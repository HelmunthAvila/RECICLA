<?php
/**
 * RECICLA+ | Módulo del ciudadano
 * Panel, publicación de materiales, solicitud de recolección,
 * consulta de estado, historial y perfil (RF-06, RF-08, RF-09, RF-10).
 */
declare(strict_types=1);

final class CiudadanoController
{
    // ===================================================== panel
    public function panel(): void
    {
        $u = usuarioActual();
        $pdo = db();

        $st = $pdo->prepare("SELECT COUNT(*) AS total,
                COALESCE(SUM(estado IN ('REGISTRADA','EN_REVISION')),0) AS pendientes,
                COALESCE(SUM(estado = 'ACEPTADA'),0) AS aceptadas,
                COALESCE(SUM(estado IN ('PROGRAMADA','EN_RUTA')),0) AS programadas,
                COALESCE(SUM(estado = 'RECOLECTADA'),0) AS recolectadas,
                COALESCE(SUM(estado = 'CANCELADA'),0) AS canceladas
            FROM solicitudes WHERE ciudadano_id = ?");
        $st->execute([$u['id']]);
        $resumen = $st->fetch();

        $st = $pdo->prepare('SELECT COALESCE(SUM(r.peso_total),0) AS kg, COUNT(r.id) AS visitas
                             FROM recolecciones r JOIN solicitudes s ON s.id = r.solicitud_id
                             WHERE s.ciudadano_id = ?');
        $st->execute([$u['id']]);
        $impacto = $st->fetch();

        $ultimas = Solicitud::listar(['ciudadano_id' => (int) $u['id']], 1, 4)['filas'];

        $st = $pdo->prepare('SELECT * FROM notificaciones WHERE usuario_id = ? ORDER BY id DESC LIMIT 3');
        $st->execute([$u['id']]);
        $avisos = $st->fetchAll();

        // Programa de incentivos
        $puntosDisponibles = Puntos::saldo((int) $u['id']);
        $puntosGanados     = Puntos::totalGanado((int) $u['id']);
        $nivel             = Puntos::nivel($puntosGanados);
        $resumenPremios    = Puntos::resumenAdmin();
        $siguientePremio   = null;
        foreach (Puntos::premios(true, true) as $p) {
            if ((int) $p['puntos_requeridos'] > $puntosDisponibles) {
                $siguientePremio = $p;
                break;
            }
        }

        vista('ciudadano/panel', [
            'titulo'    => 'Mi panel',
            'u'         => $u,
            'resumen'   => $resumen,
            'impacto'   => $impacto,
            'ultimas'   => $ultimas,
            'avisos'    => $avisos,
            'sugeridos' => Material::listar(['estado' => 'activo'], 1, 4)['filas'],
            'puntos'    => $puntosDisponibles,
            'puntosGanados' => $puntosGanados,
            'nivel'     => $nivel,
            'posicion'  => Puntos::posicion((int) $u['id']),
            'canjes'    => Puntos::canjesDeCiudadano((int) $u['id'], 3),
            'siguientePremio' => $siguientePremio,
        ]);
    }

    // ===================================================== puntos y premios (incentivos)
    public function misPuntos(): void
    {
        $u = usuarioActual();
        $saldo = Puntos::saldo((int) $u['id']);
        $ganados = Puntos::totalGanado((int) $u['id']);
        vista('ciudadano/puntos', [
            'titulo'        => 'Mis puntos',
            'saldo'         => $saldo,
            'ganados'       => $ganados,
            'canjeados'     => Puntos::totalCanjeado((int) $u['id']),
            'nivel'         => Puntos::nivel($ganados),
            'historial'     => Puntos::historial((int) $u['id'], max(1, get_int('pag', 1)), 12),
            'posicion'      => Puntos::posicion((int) $u['id']),
            'ranking'       => Puntos::ranking(5),
            'canjes'        => Puntos::canjesDeCiudadano((int) $u['id'], 10),
            'premios'       => Puntos::premios(true, true),
            'minimos'       => (int) config_get('puntos_minimos_canje', '0'),
            'bonus'         => (int) config_get('puntos_bonus_recoleccion', '20'),
        ]);
    }

    public function premios(): void
    {
        $u = usuarioActual();
        $saldo = Puntos::saldo((int) $u['id']);
        vista('ciudadano/premios', [
            'titulo'  => 'Cambiar puntos por premios',
            'saldo'   => $saldo,
            'premios' => Puntos::premios(true, false),
            'canjes'  => Puntos::canjesDeCiudadano((int) $u['id'], 5),
            'puedeCanjear' => $saldo >= (int) config_get('puntos_minimos_canje', '0'),
            'minimos' => (int) config_get('puntos_minimos_canje', '0'),
        ]);
    }

    /** Canje de un premio con los puntos acumulados. */
    public function canjear(): void
    {
        csrf_validar();
        $u = usuarioActual();
        $premioId = (int) post('premio_id');
        $r = Puntos::canjear((int) $u['id'], $premioId);
        flash($r['ok'] ? 'success' : 'warning', $r['mensaje']);
        redirigir($r['ok'] ? 'mis_puntos' : 'premios');
    }

    // ===================================================== publicar material (RF-06, RF-07)
    public function publicar(): void
    {
        $u = usuarioActual();
        vista('ciudadano/publicar', [
            'titulo'      => 'Quiero reciclar',
            'u'           => $u,
            'materiales'  => Material::activos(),
            'categorias'  => Material::categorias(true),
            'unidades'    => unidadesMedida(),
            'horarios'    => horariosDisponibles(),
            'ciudades'    => Solicitud::ciudadesSugeridas(),
            'maxFilas'    => FOTOS_POR_MIN,
        ]);
    }

    public function guardarPublicacion(): void
    {
        $u = usuarioActual();
        $ids      = (array) ($_POST['material_id'] ?? []);
        $cantidad = (array) ($_POST['cantidad'] ?? []);
        $unidad   = (array) ($_POST['unidad'] ?? []);
        $desc     = (array) ($_POST['descripcion'] ?? []);
        $obsm     = (array) ($_POST['observaciones_material'] ?? []);
        $fotos    = $this->archivosDeCampo('foto');

        $v = new Validador();
        $v->requerido(post('direccion'), 'direccion', 5, 160)
          ->requerido(post('barrio'), 'barrio', 3, 60)
          ->requerido(post('ciudad'), 'ciudad', 3, 60)
          ->fecha(post('fecha_disponible'), 'fecha_disponible', true)
          ->enLista(post('horario_disponible'), horariosDisponibles(), 'horario_disponible');

        $materiales = [];
        foreach ($ids as $i => $materialId) {
            $materialId = (int) $materialId;
            if ($materialId <= 0) {
                continue;
            }
            $m = Material::obtener($materialId);
            if (!$m || $m['estado'] !== 'activo') {
                $v->error('materiales', 'Uno de los materiales seleccionados no está disponible.');
                continue;
            }
            $cant = str_replace(',', '.', (string) ($cantidad[$i] ?? ''));
            if (!is_numeric($cant) || (float) $cant <= 0) {
                $v->error('materiales', 'Indique una cantidad mayor que cero para ' . $m['nombre'] . '.');
                continue;
            }
            $uni = (string) ($unidad[$i] ?? $m['unidad_medida']);
            if (!array_key_exists($uni, unidadesMedida())) {
                $uni = $m['unidad_medida'];
            }
            $materiales[$i] = [
                'material_id'  => $materialId,
                'cantidad'     => (float) $cant,
                'unidad'       => $uni,
                'descripcion'  => trim((string) ($desc[$i] ?? '')),
                'observaciones' => trim((string) ($obsm[$i] ?? '')),
                'foto'         => null,
            ];
        }
        if ($materiales === []) {
            $v->error('materiales', 'Debe indicar al menos un material con su cantidad.');
        }
        if (count($materiales) > FOTOS_POR_MIN) {
            $v->error('materiales', 'Puede registrar máximo ' . FOTOS_POR_MIN . ' materiales por solicitud.');
        }

        if (!$v->ok()) {
            flash('danger', $v->primero());
            $_SESSION['old'] = $_POST;
            redirigir('publicar');
        }

        // Cargue de fotografías (RF-07)
        foreach ($fotos as $i => $archivo) {
            if (!isset($materiales[$i])) {
                continue;
            }
            $r = guardarFotoArchivo($archivo, 'solicitudes');
            if ($r['error'] !== '') {
                flash('warning', 'Fotografía del material ' . $materiales[$i]['material_id'] . ': ' . $r['error']);
            } elseif ($r['ok']) {
                $materiales[$i]['foto'] = $r['archivo'];
            }
        }

        $datos = [
            'direccion'          => post('direccion'),
            'barrio'             => post('barrio'),
            'ciudad'             => post('ciudad'),
            'fecha_disponible'   => post('fecha_disponible'),
            'horario_disponible' => post('horario_disponible'),
            'observaciones'      => post('observaciones'),
        ];

        $res = Solicitud::crear($datos, array_values($materiales), (int) $u['id']);

        // Aviso a los administradores (RF-20) y a las empresas activas
        foreach (db()->query("SELECT u.id FROM usuarios u JOIN roles r ON r.id = u.rol_id
                              WHERE r.nombre = 'administrador' AND u.estado = 'activo'") as $adm) {
            notificar((int) $adm['id'], 'Nueva solicitud registrada',
                "El ciudadano {$u['nombres']} {$u['apellidos']} registró la solicitud {$res['numero']}.",
                'a_solicitud', ['id' => $res['id']]);
        }
        foreach (db()->query("SELECT id FROM usuarios WHERE rol_id = 2 AND estado = 'activo' AND empresa_id IS NOT NULL") as $op) {
            notificar((int) $op['id'], 'Nueva solicitud disponible',
                "Solicitud {$res['numero']} en {$datos['ciudad']} - {$datos['barrio']} disponible para recolección.",
                'e_solicitudes');
        }

        unset($_SESSION['old']);
        flash('success', '¡Solicitud registrada! Su número es ' . $res['numero'] . '.');
        redirigir('mi_solicitud', ['id' => $res['id']]);
    }

    // ===================================================== consultar solicitudes (RF-09)
    public function solicitudes(): void
    {
        $u = usuarioActual();
        $f = ['ciudadano_id' => (int) $u['id'], 'estado' => (string) ($_GET['estado'] ?? '')];
        $res = Solicitud::listar($f, max(1, get_int('pag', 1)), 10);
        vista('ciudadano/solicitudes', [
            'titulo'  => 'Mis solicitudes',
            'res'     => $res,
            'filtros' => $f,
        ]);
    }

    public function solicitud(): void
    {
        $u = usuarioActual();
        $id = get_int('id');
        $s = Solicitud::obtener($id);
        if (!$s || (int) $s['ciudadano_id'] !== (int) $u['id']) {
            http_response_code(403);
            vista('errores/403', [
                'titulo'  => 'Acceso no autorizado',
                'detalle' => 'La solicitud consultada no pertenece a su cuenta.',
            ]);
            return;
        }
        $rec = Recoleccion::porSolicitud($id);
        vista('ciudadano/solicitud', [
            'titulo'   => 'Solicitud ' . $s['numero'],
            's'        => $s,
            'detalle'  => Solicitud::materialesDe($id),
            'historial' => Solicitud::historial($id),
            'rec'      => $rec,
            'recMateriales' => $rec ? Recoleccion::materiales((int) $rec['id']) : [],
            'puedeCancelar' => in_array('CANCELADA', transicionesValidas($s['estado'], 'ciudadano'), true),
            'puntosEstimados' => Puntos::estimadosDeSolicitud($id),
            'puntosOtorgados' => $rec ? Puntos::otorgadosDeSolicitud($id) : 0,
        ]);
    }

    public function cancelar(): void
    {
        csrf_validar();
        $u = usuarioActual();
        $id = (int) post('id');
        $s = Solicitud::obtener($id);
        if (!$s || (int) $s['ciudadano_id'] !== (int) $u['id']) {
            flash('danger', 'No fue posible cancelar: la solicitud no pertenece a su cuenta.');
            redirigir('mis_solicitudes');
        }
        if (!in_array('CANCELADA', transicionesValidas($s['estado'], 'ciudadano'), true)) {
            flash('warning', 'La solicitud ' . $s['numero'] . ' ya no puede cancelarse porque está en estado "'
                . etiquetaEstado($s['estado']) . '".');
            redirigir('mi_solicitud', ['id' => $id]);
        }
        Solicitud::cambiarEstado($id, 'CANCELADA', (int) $u['id'], 'Cancelada por el ciudadano.');
        flash('success', 'La solicitud ' . $s['numero'] . ' fue cancelada.');
        redirigir('mis_solicitudes');
    }

    /** Historial de recolecciones efectivamente realizadas. */
    public function historial(): void
    {
        $u = usuarioActual();
        $f = ['ciudadano_id' => (int) $u['id'], 'estado' => 'RECOLECTADA'];
        $res = Solicitud::listar($f, max(1, get_int('pag', 1)), 10);

        $st = db()->prepare('SELECT COALESCE(SUM(r.peso_total),0) AS kg, COUNT(*) AS visitas
                             FROM recolecciones r JOIN solicitudes s ON s.id = r.solicitud_id
                             WHERE s.ciudadano_id = ?');
        $st->execute([$u['id']]);
        $totales = $st->fetch();

        $st = db()->prepare('SELECT r.solicitud_id, r.peso_total
                             FROM recolecciones r JOIN solicitudes s ON s.id = r.solicitud_id
                             WHERE s.ciudadano_id = ?');
        $st->execute([$u['id']]);
        $pesos = [];
        foreach ($st->fetchAll() as $fila) {
            $pesos[(int) $fila['solicitud_id']] = $fila['peso_total'];
        }

        vista('ciudadano/historial', ['titulo' => 'Mi historial', 'res' => $res, 'totales' => $totales, 'pesos' => $pesos]);
    }

    public function perfil(): void
    {
        $u = usuarioActual();
        vista('ciudadano/perfil', ['titulo' => 'Mi perfil', 'u' => $u]);
    }

    public function guardarPerfil(): void
    {
        $u = usuarioActual();
        $v = new Validador();
        $v->requerido(post('nombres'), 'nombres', 3, 80)
          ->requerido(post('apellidos'), 'apellidos', 3, 80)
          ->telefono(post('telefono'), 'telefono', true)
          ->requerido(post('direccion'), 'direccion', 5, 160)
          ->requerido(post('barrio'), 'barrio', 3, 60)
          ->requerido(post('ciudad'), 'ciudad', 3, 60);
        if (!$v->ok()) {
            flash('danger', $v->primero());
            redirigir('perfil');
        }
        Usuario::actualizarPerfil((int) $u['id'], [
            'nombres' => post('nombres'), 'apellidos' => post('apellidos'),
            'telefono' => post('telefono'), 'direccion' => post('direccion'),
            'barrio' => post('barrio'), 'ciudad' => post('ciudad'),
        ]);
        if (post('password') !== '') {
            $vp = new Validador();
            $vp->password(post('password'));
            if (post('password') !== post('confirmar')) {
                $vp->error('confirmar', 'Las contraseñas no coinciden.');
            }
            if ($vp->ok()) {
                Usuario::cambiarPassword((int) $u['id'], post('password'));
                flash('success', 'Perfil y contraseña actualizados.');
            } else {
                flash('warning', 'Datos actualizados, pero la contraseña no se cambió: ' . $vp->primero());
            }
        } else {
            flash('success', 'Perfil actualizado correctamente.');
        }
        redirigir('perfil');
    }

    public function notificaciones(): void
    {
        $u = usuarioActual();
        db()->prepare('UPDATE notificaciones SET leida = 1 WHERE usuario_id = ?')->execute([$u['id']]);
        $st = db()->prepare('SELECT * FROM notificaciones WHERE usuario_id = ? ORDER BY id DESC LIMIT 40');
        $st->execute([$u['id']]);
        vista('ciudadano/notificaciones', ['titulo' => 'Avisos', 'avisos' => $st->fetchAll()]);
    }

    // ---------------------------------------------------------- utilidades
    /** Normaliza $_FILES de un campo múltiple (foto[]) a un arreglo por índice. */
    private function archivosDeCampo(string $campo): array
    {
        $salida = [];
        if (empty($_FILES[$campo]['name'])) {
            return $salida;
        }
        // Si el campo llegó como archivo único (name="foto"), se normaliza al índice 0
        $nombres = is_array($_FILES[$campo]['name']) ? $_FILES[$campo]['name'] : [0 => $_FILES[$campo]['name']];
        foreach ($nombres as $i => $nombre) {
            $salida[$i] = [
                'name'     => $nombre,
                'type'     => $_FILES[$campo]['type'][$i] ?? '',
                'tmp_name' => $_FILES[$campo]['tmp_name'][$i] ?? '',
                'error'    => $_FILES[$campo]['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                'size'     => $_FILES[$campo]['size'][$i] ?? 0,
            ];
        }
        return $salida;
    }
}
