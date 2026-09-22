<?php
/**
 * RECICLA+ | Módulo de la empresa operadora
 * Consulta y aceptación de solicitudes, programación, rutas y registro
 * de la recolección realizada (RF-11 … RF-15).
 */
declare(strict_types=1);

final class EmpresaController
{
    private function empresaId(): int
    {
        $u = usuarioActual();
        $id = (int) ($u['empresa_id'] ?? 0);
        if ($id <= 0) {
            flash('danger', 'Su usuario no está asociado a una empresa operadora. Contacte al administrador.');
            redirigir('logout');
        }
        return $id;
    }

    // ===================================================== panel
    public function panel(): void
    {
        $eid = $this->empresaId();
        $emp = Empresa::obtener($eid);

        $st = db()->prepare("SELECT
                COALESCE(SUM(s.estado IN ('REGISTRADA','EN_REVISION') AND s.empresa_id IS NULL),0) AS pendientes,
                COALESCE(SUM(s.estado = 'ACEPTADA'),0)  AS aceptadas,
                COALESCE(SUM(s.estado = 'PROGRAMADA'),0) AS programadas,
                COALESCE(SUM(s.estado = 'EN_RUTA'),0)    AS en_ruta,
                COALESCE(SUM(s.estado = 'RECOLECTADA'),0) AS recolectadas
            FROM solicitudes s WHERE s.empresa_id = ? OR (s.empresa_id IS NULL AND s.estado IN ('REGISTRADA','EN_REVISION'))");
        $st->execute([$eid]);
        $resumen = $st->fetch();

        $st = db()->prepare('SELECT COALESCE(SUM(peso_total),0) AS kg, COUNT(*) AS visitas FROM recolecciones WHERE empresa_id = ?');
        $st->execute([$eid]);
        $produccion = $st->fetch();

        $st = db()->prepare("SELECT s.id, s.numero, s.fecha_programada, s.hora_programada, s.barrio, s.ciudad, s.operador,
                    s.estado, CONCAT(c.nombres,' ',c.apellidos) AS ciudadano_nombre, c.telefono AS ciudadano_telefono
                FROM solicitudes s JOIN usuarios c ON c.id = s.ciudadano_id
                WHERE s.empresa_id = ? AND s.estado IN ('ACEPTADA','PROGRAMADA','EN_RUTA')
                ORDER BY COALESCE(s.fecha_programada, s.fecha_disponible) ASC LIMIT 6");
        $st->execute([$eid]);
        $agenda = $st->fetchAll();

        $st = db()->prepare("SELECT s.id, s.numero, s.barrio, s.ciudad, s.fecha_disponible, s.horario_disponible,
                    (SELECT COUNT(*) FROM solicitud_materiales x WHERE x.solicitud_id = s.id) AS materiales
                FROM solicitudes s
                WHERE s.empresa_id IS NULL AND s.estado IN ('REGISTRADA','EN_REVISION')
                ORDER BY s.fecha_disponible ASC LIMIT 5");
        $st->execute();
        $disponibles = $st->fetchAll();

        vista('empresa/panel', [
            'titulo'      => 'Panel de la empresa',
            'emp'         => $emp,
            'resumen'     => $resumen,
            'produccion'  => $produccion,
            'agenda'      => $agenda,
            'disponibles' => $disponibles,
            'vehiculos'   => Empresa::vehiculos($eid, false),
            'zonas'       => Recoleccion::agruparPorZona($eid),
        ]);
    }

    // ===================================================== RF-11 consultar solicitudes
    public function solicitudes(): void
    {
        $eid = $this->empresaId();
        $tipo = (string) ($_GET['tipo'] ?? 'disponibles');

        $f = [
            'ciudad'      => trim((string) ($_GET['ciudad'] ?? '')),
            'barrio'      => trim((string) ($_GET['barrio'] ?? '')),
            'material_id' => get_int('material_id'),
            'q'           => trim((string) ($_GET['q'] ?? '')),
            'desde'       => trim((string) ($_GET['desde'] ?? '')),
            'hasta'       => trim((string) ($_GET['hasta'] ?? '')),
        ];
        switch ($tipo) {
            case 'aceptadas':
                $f['empresa_id'] = $eid;
                $f['estado'] = 'ACEPTADA';
                break;
            case 'programadas':
                $f['empresa_id'] = $eid;
                $f['estados'] = ['PROGRAMADA', 'EN_RUTA'];
                break;
            case 'recolectadas':
                $f['empresa_id'] = $eid;
                $f['estado'] = 'RECOLECTADA';
                break;
            default:
                $tipo = 'disponibles';
                $f['pendientes'] = true;
                break;
        }

        $res = Solicitud::listar($f, max(1, get_int('pag', 1)), 10);
        vista('empresa/solicitudes', [
            'titulo'     => 'Solicitudes',
            'tipo'       => $tipo,
            'res'        => $res,
            'filtros'    => $f,
            'materiales' => Material::activos(),
            'ciudades'   => Solicitud::ciudades(),
            'barrios'    => Solicitud::barrios($f['ciudad'] !== '' ? $f['ciudad'] : null),
        ]);
    }

    // ===================================================== detalle
    public function solicitud(): void
    {
        $eid = $this->empresaId();
        $id = get_int('id');
        $s = Solicitud::obtener($id);
        $esDisponible = $s && $s['empresa_id'] === null && in_array($s['estado'], ['REGISTRADA', 'EN_REVISION'], true);
        if (!$s || (!$esDisponible && (int) $s['empresa_id'] !== $eid)) {
            http_response_code(403);
            vista('errores/403', [
                'titulo'  => 'Acceso no autorizado',
                'detalle' => 'No tiene acceso a esa solicitud: está asignada a otra empresa operadora.',
            ]);
            return;
        }
        $rec = Recoleccion::porSolicitud($id);
        vista('empresa/solicitud', [
            'titulo'    => 'Solicitud ' . $s['numero'],
            's'         => $s,
            'detalle'   => Solicitud::materialesDe($id),
            'historial' => Solicitud::historial($id),
            'rec'       => $rec,
            'recMateriales' => $rec ? Recoleccion::materiales((int) $rec['id']) : [],
            'esDisponible'  => $esDisponible,
            'vehiculos' => Empresa::vehiculos($eid),
            'rutas'     => Recoleccion::rutas($eid),
            'puedeProgramar' => $s['estado'] === 'ACEPTADA' && (int) $s['empresa_id'] === $eid,
            'puedeEnRuta'    => in_array($s['estado'], ['PROGRAMADA'], true) && (int) $s['empresa_id'] === $eid,
            'puedeRecolectar' => in_array($s['estado'], ['PROGRAMADA', 'EN_RUTA'], true) && (int) $s['empresa_id'] === $eid,
            'puntosEstimados' => Puntos::estimadosDeSolicitud($id),
            'puntosOtorgados' => $rec ? Puntos::otorgadosDeSolicitud($id) : 0,
        ]);
    }

    // ===================================================== RF-12 aceptar
    public function aceptar(): void
    {
        csrf_validar();
        $eid = $this->empresaId();
        $u = usuarioActual();
        $id = (int) post('id');
        try {
            Solicitud::aceptar($id, $eid, (int) $u['id'], post('observacion'));
            flash('success', 'Solicitud aceptada. Ahora puede programar la recolección.');
        } catch (Throwable $e) {
            flash('danger', $e->getMessage());
        }
        redirigir('e_solicitud', ['id' => $id]);
    }

    // ===================================================== RF-13 programar
    public function programar(): void
    {
        $eid = $this->empresaId();
        $s = Solicitud::obtener(get_int('id'));
        if (!$s || (int) $s['empresa_id'] !== $eid || $s['estado'] !== 'ACEPTADA') {
            flash('warning', 'Solo se pueden programar solicitudes aceptadas por su empresa.');
            redirigir('e_solicitudes', ['tipo' => 'aceptadas']);
        }
        vista('empresa/programar', [
            'titulo'    => 'Programar recolección',
            's'         => $s,
            'detalle'   => Solicitud::materialesDe((int) $s['id']),
            'vehiculos' => Empresa::vehiculos($eid),
            'rutas'     => Recoleccion::rutas($eid),
        ]);
    }

    public function guardarProgramacion(): void
    {
        $eid = $this->empresaId();
        $u = usuarioActual();
        $id = (int) post('id');
        $s = Solicitud::obtener($id);
        if (!$s || (int) $s['empresa_id'] !== $eid || $s['estado'] !== 'ACEPTADA') {
            flash('danger', 'La solicitud ya no se encuentra en estado aceptado.');
            redirigir('e_solicitudes');
        }
        $hora = substr(post('hora_programada'), 0, 5);
        $v = new Validador();
        $v->fecha(post('fecha_programada'), 'fecha_programada', false)
          ->requerido(post('operador'), 'operador', 3, 120);
        if (!preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $hora)) {
            $v->error('hora_programada', 'Hora inválida (formato HH:MM).');
        }
        if (post('fecha_programada') < date('Y-m-d')) {
            $v->error('fecha_programada', 'La fecha programada no puede ser anterior a hoy.');
        }
        if (!$v->ok()) {
            flash('danger', $v->primero());
            redirigir('e_programar', ['id' => $id]);
        }
        Solicitud::programar($id, [
            'fecha_programada' => post('fecha_programada'),
            'hora_programada'  => $hora . ':00',
            'operador'         => post('operador'),
            'vehiculo_id'      => post('vehiculo_id'),
            'observaciones_empresa' => post('observaciones_empresa'),
        ], (int) $u['id']);

        if ((int) post('ruta_id') > 0) {
            Recoleccion::asignarARuta($id, (int) post('ruta_id'), (int) $u['id']);
        }
        flash('success', 'Recolección programada y ciudadano notificado.');
        redirigir('e_solicitud', ['id' => $id]);
    }

    public function marcarEnRuta(): void
    {
        csrf_validar();
        $eid = $this->empresaId();
        $u = usuarioActual();
        $id = (int) post('id');
        $s = Solicitud::obtener($id);
        if (!$s || (int) $s['empresa_id'] !== $eid || $s['estado'] !== 'PROGRAMADA') {
            flash('danger', 'Solo se pueden marcar en ruta las solicitudes programadas.');
            redirigir('e_solicitudes', ['tipo' => 'programadas']);
        }
        Solicitud::cambiarEstado($id, 'EN_RUTA', (int) $u['id'], 'Operador en ruta hacia el domicilio.');
        flash('success', 'Solicitud ' . $s['numero'] . ' marcada EN RUTA.');
        redirigir('e_solicitud', ['id' => $id]);
    }

    // ===================================================== RF-14 registrar recolección
    public function recoleccion(): void
    {
        $eid = $this->empresaId();
        $id = get_int('id');
        $s = Solicitud::obtener($id);
        if (!$s || (int) $s['empresa_id'] !== $eid || !in_array($s['estado'], ['PROGRAMADA', 'EN_RUTA'], true)) {
            flash('warning', 'Solo se puede registrar la recolección de solicitudes programadas o en ruta.');
            redirigir('e_solicitudes', ['tipo' => 'programadas']);
        }
        if (Recoleccion::porSolicitud($id)) {
            flash('info', 'La recolección de la solicitud ' . $s['numero'] . ' ya fue registrada.');
            redirigir('e_solicitud', ['id' => $id]);
        }
        vista('empresa/recoleccion', [
            'titulo'    => 'Registrar recolección',
            's'         => $s,
            'detalle'   => Solicitud::materialesDe($id),
            'vehiculos' => Empresa::vehiculos($eid),
            'rutas'     => Recoleccion::rutas($eid),
        ]);
    }

    public function guardarRecoleccion(): void
    {
        $eid = $this->empresaId();
        $u = usuarioActual();
        $id = (int) post('id');
        $s = Solicitud::obtener($id);
        if (!$s || (int) $s['empresa_id'] !== $eid || !in_array($s['estado'], ['PROGRAMADA', 'EN_RUTA'], true)) {
            flash('danger', 'La solicitud no está en un estado que permita registrar la recolección.');
            redirigir('e_solicitudes');
        }
        $hora = substr(post('hora'), 0, 5);
        $v = new Validador();
        $v->fecha(post('fecha'), 'fecha')
          ->requerido(post('operador'), 'operador', 3, 120);
        if (!preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $hora)) {
            $v->error('hora', 'Hora inválida (formato HH:MM).');
        }
        if (post('peso_total') !== '') {
            $v->numero(post('peso_total'), 'peso_total', 0, 100000);
        }

        // Cantidades realmente recolectadas por material
        $cantidades = [];
        $detalle = Solicitud::materialesDe($id);
        foreach ($detalle as $d) {
            $valor = str_replace(',', '.', (string) ($_POST['cant_' . $d['material_id']] ?? '0'));
            if ((float) $valor > 0) {
                $cantidades[(int) $d['material_id']] = ['cantidad' => (float) $valor, 'unidad' => $d['unidad']];
            }
        }
        if ($cantidades === [] && post('peso_total') === '') {
            $v->error('cantidad', 'Registre al menos una cantidad recolectada o el peso total.');
        }
        if (!$v->ok()) {
            flash('danger', $v->primero());
            redirigir('e_recoleccion', ['id' => $id]);
        }

        $foto = guardarFoto('evidencia', 'recolecciones');
        if ($foto['error'] !== '') {
            flash('warning', 'Evidencia: ' . $foto['error']);
        }

        $recId = Recoleccion::registrar($id, $eid, (int) $u['id'], [
            'vehiculo_id'  => post('vehiculo_id'),
            'ruta_id'      => post('ruta_id'),
            'fecha'        => post('fecha'),
            'hora'         => $hora . ':00',
            'operador'     => post('operador'),
            'observaciones' => post('observaciones'),
            'peso_total'   => post('peso_total'),
            'evidencia'    => $foto['archivo'],
        ], $cantidades);

        flash('success', '¡Recolección registrada! La solicitud ' . $s['numero'] . ' quedó en estado RECOLECTADA.');
        redirigir('e_historial');
    }

    // ===================================================== RF-15 rutas
    public function rutas(): void
    {
        $eid = $this->empresaId();
        $fecha = trim((string) ($_GET['fecha'] ?? ''));
        $rutas = Recoleccion::rutas($eid, $fecha !== '' ? $fecha : null);

        $asignables = db()->prepare("SELECT s.id, s.numero, s.barrio, s.ciudad, s.fecha_programada, s.ruta_id
                FROM solicitudes s
                WHERE s.empresa_id = ? AND s.estado IN ('ACEPTADA','PROGRAMADA','EN_RUTA')
                ORDER BY s.fecha_programada ASC, s.barrio");
        $asignables->execute([$eid]);

        vista('empresa/rutas', [
            'titulo'     => 'Rutas y recorridos',
            'rutas'      => $rutas,
            'zonas'      => Recoleccion::agruparPorZona($eid),
            'asignables' => $asignables->fetchAll(),
            'fecha'      => $fecha,
        ]);
    }

    public function crearRuta(): void
    {
        $eid = $this->empresaId();
        $v = new Validador();
        $v->requerido(post('nombre'), 'nombre', 4, 90)
          ->requerido(post('ciudad'), 'ciudad', 3, 60)
          ->fecha(post('fecha'), 'fecha');
        if (!$v->ok()) {
            flash('danger', $v->primero());
            redirigir('e_rutas');
        }
        Recoleccion::crearRuta([
            'nombre' => post('nombre'), 'zona' => post('zona'),
            'ciudad' => post('ciudad'), 'fecha' => post('fecha'),
        ], $eid);
        flash('success', 'Ruta creada. Ahora puede asignarle solicitudes.');
        redirigir('e_rutas');
    }

    public function asignarRuta(): void
    {
        csrf_validar();
        $eid = $this->empresaId();
        $u = usuarioActual();
        $id = (int) post('solicitud_id');
        $rutaId = (int) post('ruta_id');
        $s = Solicitud::obtener($id);
        if (!$s || (int) $s['empresa_id'] !== $eid) {
            flash('danger', 'No puede asignar rutas a una solicitud de otra empresa.');
            redirigir('e_rutas');
        }
        Recoleccion::asignarARuta($id, $rutaId > 0 ? $rutaId : null, (int) $u['id']);
        flash('success', $rutaId > 0 ? 'Solicitud asignada a la ruta.' : 'Solicitud retirada de la ruta.');
        redirigir('e_rutas');
    }

    // ===================================================== historial
    public function historial(): void
    {
        $eid = $this->empresaId();
        $f = [
            'empresa_id' => $eid,
            'ciudad'     => trim((string) ($_GET['ciudad'] ?? '')),
            'barrio'     => trim((string) ($_GET['barrio'] ?? '')),
            'material_id' => get_int('material_id'),
            'desde'      => trim((string) ($_GET['desde'] ?? '')),
            'hasta'      => trim((string) ($_GET['hasta'] ?? '')),
            'q'          => trim((string) ($_GET['q'] ?? '')),
        ];
        $res = Recoleccion::listar($f, max(1, get_int('pag', 1)), 10);
        $st = db()->prepare('SELECT COALESCE(SUM(peso_total),0) AS kg, COUNT(*) AS visitas FROM recolecciones WHERE empresa_id = ?');
        $st->execute([$eid]);
        vista('empresa/historial', [
            'titulo'     => 'Recolecciones realizadas',
            'res'        => $res,
            'totales'    => $st->fetch(),
            'filtros'    => $f,
            'materiales' => Material::activos(),
            'ciudades'   => Solicitud::ciudades(),
        ]);
    }

    // ===================================================== perfil de la empresa
    public function perfil(): void
    {
        $eid = $this->empresaId();
        vista('empresa/perfil', [
            'titulo'    => 'Perfil de la empresa',
            'emp'       => Empresa::obtener($eid),
            'usuarios'  => Usuario::usuariosDeEmpresa($eid),
            'vehiculos' => Empresa::vehiculos($eid, false),
        ]);
    }

    public function guardarPerfil(): void
    {
        $eid = $this->empresaId();
        $u = usuarioActual();
        $v = new Validador();
        $v->requerido(post('responsable'), 'responsable', 4, 120)
          ->telefono(post('telefono'), 'telefono')
          ->email(post('email'), 'email');
        if (!$v->ok()) {
            flash('danger', $v->primero());
            redirigir('e_perfil');
        }
        db()->prepare('UPDATE empresas SET responsable = ?, telefono = ?, email = ?, direccion = ? WHERE id = ?')
            ->execute([post('responsable'), post('telefono') ?: null, post('email') ?: null,
                       post('direccion') ?: null, $eid]);
        Usuario::actualizarPerfil((int) $u['id'], [
            'nombres' => $u['nombres'], 'apellidos' => $u['apellidos'],
            'telefono' => post('telefono'), 'direccion' => post('direccion'),
            'barrio' => $u['barrio'], 'ciudad' => $u['ciudad'],
        ]);
        flash('success', 'Datos de la empresa actualizados.');
        redirigir('e_perfil');
    }
}
