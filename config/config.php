<?php
/**
 * RECICLA+ | Núcleo de configuración
 * ------------------------------------------------------------------
 * Sesión segura, conexión PDO (singleton), helpers de escape,
 * CSRF, control de roles, cargue de imágenes y utilidades.
 * PHP 8+ / MySQL 8 / Apache (WAMP)
 */
declare(strict_types=1);

// ---------------------------------------------------------------- credenciales
// config.local.php NO se versiona: guarda los datos de la base de datos del hosting.
if (is_file(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
}
if (!defined('DB_HOST')) { define('DB_HOST', '127.0.0.1'); }
if (!defined('DB_PORT')) { define('DB_PORT', '3306'); }
if (!defined('DB_NAME')) { define('DB_NAME', 'recicla'); }
if (!defined('DB_USER')) { define('DB_USER', 'root'); }
if (!defined('DB_PASS')) { define('DB_PASS', ''); }
if (!defined('APP_DEBUG')) { define('APP_DEBUG', true); }   // en producción: false

// ---------------------------------------------------------------- errores
error_reporting(APP_DEBUG ? E_ALL : 0);
ini_set('display_errors', APP_DEBUG ? '1' : '0');
date_default_timezone_set('America/Bogota');
mb_internal_encoding('UTF-8');

// ---------------------------------------------------------------- rutas
define('BASE_PATH', dirname(__DIR__));
define('UPLOAD_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'uploads');
define('BASE_URL', rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/'));

// Límites del sistema
const MAX_FOTO_BYTES   = 2097152;   // 2 MB por fotografía (igual a upload_max_filesize de WAMP)
const FOTOS_POR_MIN    = 6;
const LOGIN_MAX_INTENTOS = 5;
const LOGIN_BLOQUEO_SEG  = 300;

// ---------------------------------------------------------------- sesión
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => (($_SERVER['HTTPS'] ?? '') === 'on'),
    ]);
    session_name('RECICLASESS');
    session_start();
}

// regenerar el id cada 15 minutos para evitar fijación de sesión
if (!isset($_SESSION['__creada'])) {
    $_SESSION['__creada'] = time();
} elseif (time() - (int) $_SESSION['__creada'] > 900) {
    session_regenerate_id(true);
    $_SESSION['__creada'] = time();
}

// ---------------------------------------------------------------- PDO
function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        die('<h2 style="font-family:sans-serif;color:#b00020">No fue posible conectar con MySQL.</h2>'
            . '<p style="font-family:sans-serif">Verifique que el servicio MySQL de WAMP esté iniciado y que la base <b>'
            . DB_NAME . '</b> exista (importe <code>database/recicla.sql</code>).</p>'
            . '<pre style="font-family:monospace">' . htmlspecialchars($e->getMessage()) . '</pre>');
    }
    return $pdo;
}

// ---------------------------------------------------------------- escapado
function e(?string $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** URL interna: url('materiales') -> /RECICLA/index.php?p=materiales */
function url(string $ruta = '', array $params = []): string
{
    if ($ruta === '') {
        return BASE_URL . '/index.php';
    }
    $params = array_merge(['p' => $ruta], $params);
    return BASE_URL . '/index.php?' . http_build_query($params);
}

function activo(string $ruta): string
{
    return ($_GET['p'] ?? '') === $ruta ? 'active' : '';
}

function redirigir(string $ruta, array $params = []): void
{
    header('Location: ' . url($ruta, $params));
    exit;
}

function post(string $campo, string $default = ''): string
{
    return trim((string) ($_POST[$campo] ?? $default));
}

function get_int(string $campo, int $default = 0): int
{
    return (int) ($_GET[$campo] ?? $default);
}

// ---------------------------------------------------------------- CSRF
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_campo(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_validar(): void
{
    $enviado = (string) ($_POST['csrf'] ?? $_GET['csrf'] ?? '');
    if ($enviado === '' || !hash_equals(csrf_token(), $enviado)) {
        http_response_code(419);
        die('<p style="font-family:sans-serif">Sesión expirada o token inválido. '
            . '<a href="' . e(url('inicio')) . '">Volver al inicio</a></p>');
    }
}

// ---------------------------------------------------------------- mensajes flash
function flash(string $tipo, string $mensaje): void
{
    $_SESSION['flash'][] = ['tipo' => $tipo, 'msg' => $mensaje];
}

function flash_render(): string
{
    $html = '';
    foreach ($_SESSION['flash'] ?? [] as $f) {
        $icono = ['success' => 'fa-circle-check', 'danger' => 'fa-circle-exclamation',
                  'warning' => 'fa-triangle-exclamation', 'info' => 'fa-circle-info'][$f['tipo']] ?? 'fa-circle-info';
        $html .= '<div class="alert alert-' . e($f['tipo']) . ' alert-dismissible fade show shadow-sm" role="alert">'
            . '<i class="fa-solid ' . $icono . ' me-1"></i>' . e($f['msg'])
            . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button></div>';
    }
    unset($_SESSION['flash']);
    return $html;
}

// ---------------------------------------------------------------- sesión / roles
function usuarioActual(): ?array
{
    if (empty($_SESSION['usuario_id'])) {
        return null;
    }
    static $u = null;
    if ($u === null || (int) $u['id'] !== (int) $_SESSION['usuario_id']) {
        $st = db()->prepare('SELECT u.*, r.nombre AS rol, e.nombre AS empresa
                             FROM usuarios u
                             JOIN roles r ON r.id = u.rol_id
                             LEFT JOIN empresas e ON e.id = u.empresa_id
                             WHERE u.id = ? LIMIT 1');
        $st->execute([(int) $_SESSION['usuario_id']]);
        $u = $st->fetch() ?: null;
        if ($u && $u['estado'] !== 'activo') {
            cerrarSesion();
            $u = null;
        }
    }
    return $u;
}

function estaLogueado(): bool
{
    return usuarioActual() !== null;
}

function rolActual(): string
{
    return usuarioActual()['rol'] ?? '';
}

function exigirLogin(): array
{
    $u = usuarioActual();
    if ($u === null) {
        flash('warning', 'Debe iniciar sesión para continuar.');
        redirigir('login');
    }
    return $u;
}

function exigirRol(string ...$roles): array
{
    $u = exigirLogin();
    if (!in_array($u['rol'], $roles, true)) {
        http_response_code(403);
        die('<p style="font-family:sans-serif">No tiene permisos para acceder a este módulo.</p>');
    }
    return $u;
}

function cerrarSesion(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        setcookie(session_name(), '', time() - 42000, '/');
    }
    session_destroy();
    session_start();
}

function iniciarSesionUsuario(array $u): void
{
    session_regenerate_id(true);
    $_SESSION['usuario_id'] = (int) $u['id'];
    $_SESSION['__creada']   = time();
    db()->prepare('UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?')->execute([(int) $u['id']]);
}

// ---------------------------------------------------------------- catálogos de apoyo
function estadosSolicitud(): array
{
    return [
        'REGISTRADA'   => ['label' => 'Registrada',   'badge' => 'bg-secondary',          'icono' => 'fa-file-circle-plus'],
        'EN_REVISION'  => ['label' => 'En revisión',  'badge' => 'bg-info text-dark',     'icono' => 'fa-magnifying-glass'],
        'ACEPTADA'     => ['label' => 'Aceptada',     'badge' => 'bg-primary',            'icono' => 'fa-hand-holding-heart'],
        'PROGRAMADA'   => ['label' => 'Programada',   'badge' => 'bg-warning text-dark',  'icono' => 'fa-calendar-check'],
        'EN_RUTA'      => ['label' => 'En ruta',      'badge' => 'bg-purple text-white',  'icono' => 'fa-truck-fast'],
        'RECOLECTADA'  => ['label' => 'Recolectada',  'badge' => 'bg-success',            'icono' => 'fa-circle-check'],
        'CANCELADA'    => ['label' => 'Cancelada',    'badge' => 'bg-danger',             'icono' => 'fa-ban'],
    ];
}

function etiquetaEstado(string $estado): string
{
    return estadosSolicitud()[$estado]['label'] ?? str_replace('_', ' ', $estado);
}

function badgeEstado(string $estado): string
{
    $cfg = estadosSolicitud()[$estado] ?? ['badge' => 'bg-secondary', 'icono' => 'fa-circle', 'label' => $estado];
    return '<span class="badge badge-estado ' . $cfg['badge'] . '"><i class="fa-solid ' . $cfg['icono'] . ' me-1"></i>'
        . e($cfg['label']) . '</span>';
}

/** Estados a los que puede pasar una solicitud (flujo RF-10). */
function transicionesValidas(string $desde, string $rol): array
{
    $flujo = [
        'REGISTRADA'  => ['EN_REVISION', 'ACEPTADA', 'CANCELADA'],
        'EN_REVISION' => ['ACEPTADA', 'CANCELADA'],
        'ACEPTADA'    => ['PROGRAMADA', 'CANCELADA'],
        'PROGRAMADA'  => ['EN_RUTA', 'CANCELADA'],
        'EN_RUTA'     => ['RECOLECTADA'],
        'RECOLECTADA' => [],
        'CANCELADA'   => [],
    ];
    $siguiente = $flujo[$desde] ?? [];
    if ($rol === 'ciudadano') {
        return array_intersect($siguiente, ['CANCELADA']);   // el ciudadano solo cancela
    }
    return $siguiente;
}

function unidadesMedida(): array
{
    return ['kg' => 'Kilogramos (kg)', 'unidad' => 'Unidades', 'bulto' => 'Bultos',
            'caja' => 'Cajas', 'litro' => 'Litros', 'par' => 'Pares'];
}

function horariosDisponibles(): array
{
    return ['manana' => 'Mañana (7 a.m. - 12 m.)', 'tarde' => 'Tarde (12 m. - 5 p.m.)',
            'noche' => 'Noche (5 p.m. - 8 p.m.)', 'indiferente' => 'Indiferente'];
}

function etiquetaHorario(?string $h): string
{
    return horariosDisponibles()[$h] ?? 'Indiferente';
}

function formatearCantidad(float $c): string
{
    return rtrim(rtrim(number_format($c, 2, ',', '.'), '0'), ',');
}

// ---------------------------------------------------------------- configuración BD
function config_get(string $clave, string $default = ''): string
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (db()->query('SELECT clave, valor FROM configuracion') as $fila) {
            $cache[$fila['clave']] = $fila['valor'];
        }
    }
    return $cache[$clave] ?? $default;
}

function config_set(string $clave, string $valor): void
{
    db()->prepare('INSERT INTO configuracion (clave, valor) VALUES (?, ?)
                   ON DUPLICATE KEY UPDATE valor = VALUES(valor)')->execute([$clave, $valor]);
}

// ---------------------------------------------------------------- historial y notificaciones
function registrarHistorial(int $solicitudId, ?int $usuarioId, ?string $anterior, string $nuevo, string $obs = ''): void
{
    db()->prepare('INSERT INTO historial_solicitudes (solicitud_id, usuario_id, estado_anterior, estado_nuevo, observacion)
                   VALUES (?, ?, ?, ?, ?)')
        ->execute([$solicitudId, $usuarioId, $anterior, $nuevo, $obs !== '' ? $obs : null]);
}

function notificar(int $usuarioId, string $titulo, string $mensaje, string $ruta = '', array $params = []): void
{
    db()->prepare('INSERT INTO notificaciones (usuario_id, titulo, mensaje, url) VALUES (?, ?, ?, ?)')
        ->execute([$usuarioId, $titulo, $mensaje, $ruta !== '' ? url($ruta, $params) : null]);
}

function notificacionesNoLeidas(?int $usuarioId): int
{
    if (!$usuarioId) {
        return 0;
    }
    $st = db()->prepare('SELECT COUNT(*) FROM notificaciones WHERE usuario_id = ? AND leida = 0');
    $st->execute([$usuarioId]);
    return (int) $st->fetchColumn();
}

// ---------------------------------------------------------------- cargue de imágenes (RF-07)
/**
 * Valida y guarda una fotografía subida desde el celular.
 * @return array{ok:bool, archivo:?string, error:string}
 */
function guardarFoto(string $campo, string $subcarpeta): array
{
    if (!isset($_FILES[$campo]) || ($_FILES[$campo]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['ok' => false, 'archivo' => null, 'error' => ''];          // opcional
    }
    return guardarFotoArchivo($_FILES[$campo], $subcarpeta);
}

/** Variante que recibe el arreglo ya resuelto del archivo (soporta campos foto[]). */
function guardarFotoArchivo(array $f, string $subcarpeta): array
{
    // Tolerar tanto name="foto" como name="foto[]" (arreglo de archivos)
    if (isset($f['error']) && is_array($f['error'])) {
        $clave = array_key_first($f['error']);
        $f = [
            'name'     => $f['name'][$clave] ?? '',
            'type'     => $f['type'][$clave] ?? '',
            'tmp_name' => $f['tmp_name'][$clave] ?? '',
            'error'    => $f['error'][$clave] ?? UPLOAD_ERR_NO_FILE,
            'size'     => $f['size'][$clave] ?? 0,
        ];
    }
    if (($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['ok' => false, 'archivo' => null, 'error' => ''];
    }
    if ($f['error'] !== UPLOAD_ERR_OK) {
        $msg = $f['error'] === UPLOAD_ERR_INI_SIZE || $f['error'] === UPLOAD_ERR_FORM_SIZE
            ? 'La fotografía supera el tamaño permitido.' : 'No fue posible recibir la fotografía.';
        return ['ok' => false, 'archivo' => null, 'error' => $msg];
    }
    if ($f['size'] > MAX_FOTO_BYTES) {
        return ['ok' => false, 'archivo' => null, 'error' => 'La fotografía supera 3 MB. Intente con una imagen más liviana.'];
    }
    $info = @getimagesize($f['tmp_name']);
    $permitidos = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
    if ($info === false || !isset($permitidos[$info[2]])) {
        return ['ok' => false, 'archivo' => null, 'error' => 'Formato no permitido. Use JPG, PNG o WEBP.'];
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
        return ['ok' => false, 'archivo' => null, 'error' => 'El archivo no es una imagen válida.'];
    }
    $destinoDir = UPLOAD_PATH . DIRECTORY_SEPARATOR . $subcarpeta;
    if (!is_dir($destinoDir) && !mkdir($destinoDir, 0775, true) && !is_dir($destinoDir)) {
        return ['ok' => false, 'archivo' => null, 'error' => 'No fue posible crear la carpeta de evidencias.'];
    }
    $nombre = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $permitidos[$info[2]];
    if (!move_uploaded_file($f['tmp_name'], $destinoDir . DIRECTORY_SEPARATOR . $nombre)) {
        return ['ok' => false, 'archivo' => null, 'error' => 'No fue posible guardar la fotografía.'];
    }
    // Optimización (RNF-05): reducir el lado mayor a 1280 px
    optimizarImagen($destinoDir . DIRECTORY_SEPARATOR . $nombre, $info[2]);
    return ['ok' => true, 'archivo' => $subcarpeta . '/' . $nombre, 'error' => ''];
}

function optimizarImagen(string $rutaArchivo, int $tipo): void
{
    if (!function_exists('imagecreatetruecolor')) {
        return;
    }
    [$ancho, $alto] = getimagesize($rutaArchivo) ?: [0, 0];
    if ($ancho <= 1280 && $alto <= 1280) {
        return;
    }
    $escala = 1280 / max($ancho, $alto);
    $nuevoAncho = (int) round($ancho * $escala);
    $nuevoAlto  = (int) round($alto * $escala);
    $origen = match ($tipo) {
        IMAGETYPE_JPEG => imagecreatefromjpeg($rutaArchivo),
        IMAGETYPE_PNG  => imagecreatefrompng($rutaArchivo),
        IMAGETYPE_WEBP => imagecreatefromwebp($rutaArchivo),
        default        => null,
    };
    if (!$origen) {
        return;
    }
    $lienzo = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
    imagecopyresampled($lienzo, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
    match ($tipo) {
        IMAGETYPE_PNG  => imagepng($lienzo, $rutaArchivo, 7),
        IMAGETYPE_WEBP => imagewebp($lienzo, $rutaArchivo, 80),
        default        => imagejpeg($lienzo, $rutaArchivo, 78),
    };
    imagedestroy($origen);
    imagedestroy($lienzo);
}

function urlFoto(?string $rutaRelativa): ?string
{
    return $rutaRelativa ? BASE_URL . '/uploads/' . $rutaRelativa : null;
}

// ---------------------------------------------------------------- paginación (RNF-05)
function paginar(int $total, int $porPagina, int $paginaActual): array
{
    $paginas = max(1, (int) ceil($total / max(1, $porPagina)));
    $pagina  = min(max(1, $paginaActual), $paginas);
    return [
        'total'   => $total,
        'paginas' => $paginas,
        'pagina'  => $pagina,
        'offset'  => ($pagina - 1) * $porPagina,
        'limite'  => $porPagina,
    ];
}

function paginador(array $p, string $ruta, array $params = []): string
{
    if ($p['paginas'] <= 1) {
        return '';
    }
    $html = '<nav class="mt-3"><ul class="pagination pagination-sm justify-content-center flex-wrap">';
    for ($i = 1; $i <= $p['paginas']; $i++) {
        $activo = $i === $p['pagina'] ? ' active' : '';
        $html .= '<li class="page-item' . $activo . '"><a class="page-link" href="'
            . e(url($ruta, $params + ['pag' => $i])) . '">' . $i . '</a></li>';
    }
    return $html . '</ul></nav>';
}

// ---------------------------------------------------------------- render de vistas
function render(string $vista, array $datos = []): void
{
    $archivo = BASE_PATH . '/views/' . $vista . '.php';
    if (!is_file($archivo)) {
        http_response_code(500);
        die('Vista no encontrada: ' . e($vista));
    }
    extract($datos, EXTR_SKIP);
    require $archivo;
}

/** Vista con layout (header + contenido + footer). */
function vista(string $vista, array $datos = [], array $layout = []): void
{
    $datos['titulo']  = $datos['titulo'] ?? config_get('nombre_sistema', 'RECICLA+');
    $datos['navTipo'] = $layout['nav'] ?? (rolActual() === 'ciudadano' ? 'ciudadano' : (rolActual() === 'empresa' ? 'empresa' : (rolActual() === 'administrador' ? 'admin' : '')));
    $datos['usuario'] = usuarioActual();
    render('layouts/header', $datos);
    render($vista, $datos);
    render('layouts/footer', $datos);
}
