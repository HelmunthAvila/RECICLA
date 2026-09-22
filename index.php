<?php
/**
 * RECICLA+ | Front controller
 * Todas las peticiones entran por aquí: index.php?p=<ruta>
 * --------------------------------------------------------------
 */
declare(strict_types=1);

require __DIR__ . '/config/config.php';
require __DIR__ . '/config/validar.php';

foreach (['Usuario', 'Material', 'Empresa', 'Solicitud', 'Recoleccion', 'Reporte', 'Puntos'] as $modelo) {
    require __DIR__ . '/models/' . $modelo . '.php';
}
foreach (['AuthController', 'CatalogoController', 'CiudadanoController', 'EmpresaController', 'AdminController'] as $ctl) {
    require __DIR__ . '/controllers/' . $ctl . '.php';
}

/** Tabla de rutas: p => [Controlador, método, rol requerido (null = público)] */
$rutas = [
    // ---------------- público ----------------
    'inicio'            => ['AuthController', 'inicio', null],
    'login'             => ['AuthController', 'loginForm', null],
    'login_post'        => ['AuthController', 'login', null],
    'registro'          => ['AuthController', 'registroForm', null],
    'registro_post'     => ['AuthController', 'registro', null],
    'recuperar'         => ['AuthController', 'recuperarForm', null],
    'recuperar_post'    => ['AuthController', 'recuperarEnviar', null],
    'restablecer'       => ['AuthController', 'restablecer', null],
    'restablecer_post'  => ['AuthController', 'guardarPassword', null],
    'logout'            => ['AuthController', 'logout', null],
    'materiales'        => ['CatalogoController', 'index', null],
    'material'          => ['CatalogoController', 'detalle', null],

    // ---------------- ciudadano ----------------
    'panel'             => ['CiudadanoController', 'panel', 'ciudadano'],
    'publicar'          => ['CiudadanoController', 'publicar', 'ciudadano'],
    'publicar_post'     => ['CiudadanoController', 'guardarPublicacion', 'ciudadano'],
    'mis_solicitudes'   => ['CiudadanoController', 'solicitudes', 'ciudadano'],
    'mi_solicitud'      => ['CiudadanoController', 'solicitud', 'ciudadano'],
    'cancelar'          => ['CiudadanoController', 'cancelar', 'ciudadano'],
    'historial'         => ['CiudadanoController', 'historial', 'ciudadano'],
    'perfil'            => ['CiudadanoController', 'perfil', 'ciudadano'],
    'perfil_post'       => ['CiudadanoController', 'guardarPerfil', 'ciudadano'],
    'notificaciones'    => ['CiudadanoController', 'notificaciones', 'ciudadano'],
    'mis_puntos'        => ['CiudadanoController', 'misPuntos', 'ciudadano'],
    'premios'           => ['CiudadanoController', 'premios', 'ciudadano'],
    'canjear'           => ['CiudadanoController', 'canjear', 'ciudadano'],

    // ---------------- empresa operadora ----------------
    'e_panel'           => ['EmpresaController', 'panel', 'empresa'],
    'e_solicitudes'     => ['EmpresaController', 'solicitudes', 'empresa'],
    'e_solicitud'       => ['EmpresaController', 'solicitud', 'empresa'],
    'e_aceptar'         => ['EmpresaController', 'aceptar', 'empresa'],
    'e_programar'       => ['EmpresaController', 'programar', 'empresa'],
    'e_programar_post'  => ['EmpresaController', 'guardarProgramacion', 'empresa'],
    'e_en_ruta'         => ['EmpresaController', 'marcarEnRuta', 'empresa'],
    'e_recoleccion'     => ['EmpresaController', 'recoleccion', 'empresa'],
    'e_recoleccion_post' => ['EmpresaController', 'guardarRecoleccion', 'empresa'],
    'e_rutas'           => ['EmpresaController', 'rutas', 'empresa'],
    'e_ruta_post'       => ['EmpresaController', 'crearRuta', 'empresa'],
    'e_asignar_ruta'    => ['EmpresaController', 'asignarRuta', 'empresa'],
    'e_historial'       => ['EmpresaController', 'historial', 'empresa'],
    'e_perfil'          => ['EmpresaController', 'perfil', 'empresa'],
    'e_perfil_post'     => ['EmpresaController', 'guardarPerfil', 'empresa'],

    // ---------------- administrador ----------------
    'a_panel'           => ['AdminController', 'panel', 'administrador'],
    'a_usuarios'        => ['AdminController', 'usuarios', 'administrador'],
    'a_usuario'         => ['AdminController', 'usuario', 'administrador'],
    'a_usuario_post'    => ['AdminController', 'guardarUsuario', 'administrador'],
    'a_usuario_estado'  => ['AdminController', 'estadoUsuario', 'administrador'],
    'a_empresas'        => ['AdminController', 'empresas', 'administrador'],
    'a_empresa'         => ['AdminController', 'empresa', 'administrador'],
    'a_empresa_post'    => ['AdminController', 'guardarEmpresa', 'administrador'],
    'a_empresa_estado'  => ['AdminController', 'estadoEmpresa', 'administrador'],
    'a_vehiculo_post'   => ['AdminController', 'guardarVehiculo', 'administrador'],
    'a_categorias'      => ['AdminController', 'categorias', 'administrador'],
    'a_categoria_post'  => ['AdminController', 'guardarCategoria', 'administrador'],
    'a_categoria_estado' => ['AdminController', 'estadoCategoria', 'administrador'],
    'a_materiales'      => ['AdminController', 'materiales', 'administrador'],
    'a_material'        => ['AdminController', 'material', 'administrador'],
    'a_material_post'   => ['AdminController', 'guardarMaterial', 'administrador'],
    'a_material_estado' => ['AdminController', 'estadoMaterial', 'administrador'],
    'a_solicitudes'     => ['AdminController', 'solicitudes', 'administrador'],
    'a_solicitud'       => ['AdminController', 'solicitud', 'administrador'],
    'a_recolecciones'   => ['AdminController', 'recolecciones', 'administrador'],
    'a_reportes'        => ['AdminController', 'reportes', 'administrador'],
    'a_reporte_csv'     => ['AdminController', 'reporteCsv', 'administrador'],
    'a_config'          => ['AdminController', 'configuracion', 'administrador'],
    'a_config_post'     => ['AdminController', 'guardarConfiguracion', 'administrador'],
    'a_premios'         => ['AdminController', 'premios', 'administrador'],
    'a_premio'          => ['AdminController', 'premioForm', 'administrador'],
    'a_premio_post'     => ['AdminController', 'guardarPremio', 'administrador'],
    'a_premio_estado'   => ['AdminController', 'estadoPremio', 'administrador'],
    'a_canjes'          => ['AdminController', 'canjes', 'administrador'],
    'a_canje_estado'    => ['AdminController', 'estadoCanje', 'administrador'],
    'a_puntos'          => ['AdminController', 'puntos', 'administrador'],
    'a_puntos_ajuste'   => ['AdminController', 'ajustarPuntos', 'administrador'],
];

$p = (string) ($_GET['p'] ?? 'inicio');
if (!isset($rutas[$p])) {
    http_response_code(404);
    vista('errores/404', ['titulo' => 'Página no encontrada', 'rutaPedida' => $p]);
    exit;
}

[$controlador, $metodo, $rolRequerido] = $rutas[$p];

// Control de permisos según el rol (RNF-04)
if ($rolRequerido !== null) {
    exigirRol($rolRequerido);
}

// Escrituras por POST con token CSRF
if (str_ends_with($p, '_post')) {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        redirigir('inicio');
    }
    csrf_validar();
}

try {
    (new $controlador())->$metodo();
} catch (Throwable $e) {
    error_log('[RECICLA+] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    vista('errores/500', ['titulo' => 'Error del sistema', 'mensaje' => $e->getMessage()]);
}
