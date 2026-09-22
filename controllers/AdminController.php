<?php
/**
 * RECICLA+ | Módulo del administrador
 * Usuarios, empresas, categorías, materiales, solicitudes,
 * recolecciones, reportes y configuración (RF-16 … RF-21).
 */
declare(strict_types=1);

final class AdminController
{
    // ===================================================== RF-20 dashboard
    public function panel(): void
    {
        vista('admin/panel', [
            'titulo'      => 'Panel administrativo',
            'r'           => Reporte::resumen(),
            'serie'       => Reporte::serieMensual(6),
            'ultimas'     => Solicitud::listar([], 1, 5)['filas'],
            'programadas' => Solicitud::proximasProgramadas(5),
            'movimientos' => Reporte::ultimosMovimientos(6),
            'puntos'      => Puntos::resumenAdmin(),
            'ranking'     => Puntos::ranking(5),
            'canjesPend'  => Puntos::listarCanjes(['estado' => 'solicitado'], 1, 5)['filas'],
            'seriePuntos' => Puntos::serieMensual(6),
        ]);
    }

    // ===================================================== programa de puntos y premios
    public function premios(): void
    {
        vista('admin/premios', [
            'titulo'   => 'Premios',
            'premios'  => Puntos::premios(false),
            'resumen'  => Puntos::resumenAdmin(),
            'serie'    => Puntos::serieMensual(6),
        ]);
    }

    public function premioForm(): void
    {
        $id = get_int('id');
        vista('admin/premio_form', [
            'titulo' => $id > 0 ? 'Editar premio' : 'Nuevo premio',
            'p'      => $id > 0 ? Puntos::premio($id) : null,
        ]);
    }

    public function guardarPremio(): void
    {
        $id = (int) post('id');
        $d = [
            'nombre' => post('nombre'), 'descripcion' => post('descripcion'),
            'puntos_requeridos' => post('puntos_requeridos'), 'stock' => post('stock'),
            'icono' => post('icono'),
        ];
        $v = new Validador();
        $v->requerido($d['nombre'], 'nombre', 3, 120)
          ->numero($d['puntos_requeridos'], 'puntos_requeridos', 1, 1000000)
          ->numero($d['stock'], 'stock', 0, 100000);
        if (Puntos::nombrePremioExiste($d['nombre'], $id)) {
            $v->error('nombre', 'Ya existe un premio con ese nombre.');
        }
        if (!$v->ok()) {
            flash('danger', $v->primero());
            redirigir('a_premio', ['id' => $id]);
        }
        $foto = guardarFoto('imagen', 'premios');
        if ($foto['error'] !== '') {
            flash('warning', 'Imagen: ' . $foto['error']);
        }
        if ($foto['ok']) {
            $d['imagen'] = $foto['archivo'];
        }
        Puntos::guardarPremio($d, $id ?: null);
        flash('success', $id ? 'Premio actualizado.' : 'Premio creado correctamente.');
        redirigir('a_premios');
    }

    public function estadoPremio(): void
    {
        csrf_validar();
        $id = (int) post('id');
        $p = Puntos::premio($id);
        if (!$p) {
            flash('danger', 'Premio no encontrado.');
            redirigir('a_premios');
        }
        Puntos::estadoPremio($id, $p['estado'] === 'activo' ? 'inactivo' : 'activo');
        flash('success', 'Premio ' . $p['nombre'] . ' ' . ($p['estado'] === 'activo' ? 'desactivado' : 'activado') . '.');
        redirigir('a_premios');
    }

    public function canjes(): void
    {
        $f = [
            'estado'    => trim((string) ($_GET['estado'] ?? '')),
            'premio_id' => get_int('premio_id'),
            'q'         => trim((string) ($_GET['q'] ?? '')),
        ];
        vista('admin/canjes', [
            'titulo'  => 'Canjes de premios',
            'res'     => Puntos::listarCanjes($f, max(1, get_int('pag', 1)), 15),
            'filtros' => $f,
            'premios' => Puntos::premios(false),
            'resumen' => Puntos::resumenAdmin(),
        ]);
    }

    public function estadoCanje(): void
    {
        csrf_validar();
        $id = (int) post('id');
        $estado = post('estado');
        if (!in_array($estado, ['solicitado', 'entregado', 'cancelado'], true)) {
            flash('danger', 'Estado de canje no válido.');
            redirigir('a_canjes');
        }
        $r = Puntos::estadoCanje($id, $estado, (int) (usuarioActual()['id'] ?? 0));
        flash($r['ok'] ? 'success' : 'warning', $r['mensaje']);
        redirigir('a_canjes');
    }

    /** Ciudadanos con puntos acumulados y ajuste manual de puntos. */
    public function puntos(): void
    {
        $q = trim((string) ($_GET['q'] ?? ''));
        $p = [];
        $w = ["r.nombre = 'ciudadano'"];
        if ($q !== '') {
            $w[] = '(u.nombres LIKE ? OR u.apellidos LIKE ? OR u.documento LIKE ? OR u.email LIKE ?)';
            $t = '%' . $q . '%';
            array_push($p, $t, $t, $t, $t);
        }
        $where = implode(' AND ', $w);

        $st = db()->prepare("SELECT COUNT(*) FROM usuarios u JOIN roles r ON r.id = u.rol_id WHERE $where");
        $st->execute($p);
        $pg = paginar((int) $st->fetchColumn(), 15, max(1, get_int('pag', 1)));

        $st = db()->prepare("SELECT u.id, CONCAT(u.nombres,' ',u.apellidos) AS ciudadano, u.documento, u.email,
                    u.telefono, u.barrio, u.ciudad,
                    COALESCE((SELECT SUM(pm.puntos) FROM puntos_movimientos pm WHERE pm.usuario_id = u.id), 0) AS saldo,
                    COALESCE((SELECT SUM(pm.puntos) FROM puntos_movimientos pm WHERE pm.usuario_id = u.id AND pm.tipo = 'ganados'), 0) AS ganados,
                    (SELECT COUNT(*) FROM canjes c WHERE c.usuario_id = u.id) AS canjes
                FROM usuarios u JOIN roles r ON r.id = u.rol_id
                WHERE $where
                ORDER BY ganados DESC, u.nombres
                LIMIT {$pg['limite']} OFFSET {$pg['offset']}");
        $st->execute($p);

        vista('admin/puntos', [
            'titulo'  => 'Puntos de los ciudadanos',
            'filas'   => $st->fetchAll(),
            'pag'     => $pg,
            'q'       => $q,
            'resumen' => Puntos::resumenAdmin(),
            'ranking' => Puntos::topCiudadanos(10),
        ]);
    }

    public function ajustarPuntos(): void
    {
        csrf_validar();
        $usuarioId = (int) post('usuario_id');
        $puntos = (int) str_replace(['.', ','], ['', ''], post('puntos'));
        $motivo = post('motivo');
        $u = Usuario::buscarPorId($usuarioId);
        $v = new Validador();
        $v->requerido($motivo, 'motivo', 5, 200);
        if (!$u || $u['rol'] !== 'ciudadano') {
            $v->error('usuario_id', 'Seleccione un ciudadano válido.');
        }
        if ($puntos === 0) {
            $v->error('puntos', 'Indique una cantidad de puntos diferente de cero (puede ser negativa).');
        }
        if (!$v->ok()) {
            flash('danger', $v->primero());
            redirigir('a_puntos');
        }
        try {
            Puntos::ajuste($usuarioId, $puntos, $motivo, (int) (usuarioActual()['id'] ?? 0));
        } catch (RuntimeException $e) {
            flash('danger', $e->getMessage());
            redirigir('a_puntos');
        }
        flash('success', 'Se registraron ' . $puntos . ' puntos para ' . $u['nombres'] . ' ' . $u['apellidos'] . '.');
        redirigir('a_puntos');
    }

    // ===================================================== RF-16 usuarios
    public function usuarios(): void
    {
        $f = [
            'rol'    => trim((string) ($_GET['rol'] ?? '')),
            'estado' => trim((string) ($_GET['estado'] ?? '')),
            'q'      => trim((string) ($_GET['q'] ?? '')),
        ];
        $res = Usuario::listar($f, max(1, get_int('pag', 1)), 15);
        vista('admin/usuarios', [
            'titulo'   => 'Usuarios',
            'res'      => $res,
            'filtros'  => $f,
            'roles'    => Usuario::roles(),
            'empresas' => Empresa::activas(),
        ]);
    }

    public function usuario(): void
    {
        $id = get_int('id');
        vista('admin/usuario_form', [
            'titulo'   => $id > 0 ? 'Editar usuario' : 'Nuevo usuario',
            'u'        => $id > 0 ? Usuario::buscarPorId($id) : null,
            'roles'    => Usuario::roles(),
            'empresas' => Empresa::activas(),
        ]);
    }

    public function guardarUsuario(): void
    {
        $id = (int) post('id');
        $d = [
            'nombres' => post('nombres'), 'apellidos' => post('apellidos'),
            'documento' => post('documento'), 'telefono' => post('telefono'),
            'email' => post('email'), 'password' => post('password'),
            'direccion' => post('direccion'), 'barrio' => post('barrio'),
            'ciudad' => post('ciudad'), 'rol_id' => post('rol_id'),
            'empresa_id' => post('empresa_id'),
        ];
        $v = new Validador();
        $v->requerido($d['nombres'], 'nombres', 3, 80)
          ->requerido($d['apellidos'], 'apellidos', 3, 80)
          ->documento($d['documento'])
          ->telefono($d['telefono'], 'telefono', true)
          ->requerido($d['email'], 'email')->email($d['email']);
        if (Usuario::emailRegistrado($d['email'], $id)) {
            $v->error('email', 'El correo ya está registrado por otro usuario.');
        }
        if (Usuario::documentoRegistrado($d['documento'], $id)) {
            $v->error('documento', 'El documento ya está registrado por otro usuario.');
        }
        if (!$id) {
            $v->password($d['password']);
        } elseif ($d['password'] !== '') {
            $v->password($d['password']);
        }
        if ((int) $d['rol_id'] === 2 && (int) $d['empresa_id'] <= 0) {
            $v->error('empresa_id', 'Un usuario con rol empresa debe estar asociado a una empresa operadora.');
        }
        if (!$v->ok()) {
            flash('danger', $v->primero());
            redirigir('a_usuario', ['id' => $id]);
        }

        if ($id) {
            Usuario::actualizar($id, $d + ['password' => '']);
            if (post('password') !== '') {
                Usuario::cambiarPassword($id, post('password'));
            }
            flash('success', 'Usuario actualizado.');
        } else {
            $nuevoId = Usuario::crearPorAdmin($d);
            notificar($nuevoId, 'Cuenta creada', 'Su cuenta fue creada en RECICLA+. Puede iniciar sesión.', 'login');
            flash('success', 'Usuario creado. Contraseña inicial: ' . ($d['password'] !== '' ? $d['password'] : 'Recicla123*'));
        }
        redirigir('a_usuarios');
    }

    public function estadoUsuario(): void
    {
        csrf_validar();
        $id = (int) post('id');
        $u = Usuario::buscarPorId($id);
        if (!$u) {
            flash('danger', 'Usuario no encontrado.');
            redirigir('a_usuarios');
        }
        if ($id === (int) (usuarioActual()['id'] ?? 0)) {
            flash('warning', 'No puede desactivar su propia cuenta.');
            redirigir('a_usuarios');
        }
        $nuevo = $u['estado'] === 'activo' ? 'inactivo' : 'activo';
        Usuario::cambiarEstado($id, $nuevo);
        flash('success', 'Usuario ' . $u['nombres'] . ' ' . $u['apellidos'] . ' ahora está ' . $nuevo . '.');
        redirigir('a_usuarios');
    }

    // ===================================================== RF-18 empresas
    public function empresas(): void
    {
        $f = [
            'estado' => trim((string) ($_GET['estado'] ?? '')),
            'ciudad' => trim((string) ($_GET['ciudad'] ?? '')),
            'q'      => trim((string) ($_GET['q'] ?? '')),
        ];
        vista('admin/empresas', [
            'titulo'   => 'Empresas operadoras',
            'res'      => Empresa::listar($f, max(1, get_int('pag', 1)), 15),
            'filtros'  => $f,
            'ciudades' => Empresa::ciudades(),
        ]);
    }

    public function empresa(): void
    {
        $id = get_int('id');
        vista('admin/empresa_form', [
            'titulo'    => $id > 0 ? 'Editar empresa' : 'Nueva empresa',
            'e'         => $id > 0 ? Empresa::obtener($id) : null,
            'vehiculos' => $id > 0 ? Empresa::vehiculos($id, false) : [],
            'usuarios'  => $id > 0 ? Usuario::usuariosDeEmpresa($id) : [],
            'ciudades'  => Solicitud::ciudadesSugeridas(),
        ]);
    }

    public function guardarEmpresa(): void
    {
        $id = (int) post('id');
        $d = [
            'nombre' => post('nombre'), 'nit' => post('nit'), 'responsable' => post('responsable'),
            'telefono' => post('telefono'), 'email' => post('email'),
            'direccion' => post('direccion'), 'ciudad' => post('ciudad'),
        ];
        $v = new Validador();
        $v->requerido($d['nombre'], 'nombre', 4, 120)
          ->requerido($d['nit'], 'nit', 5, 20)
          ->requerido($d['responsable'], 'responsable', 4, 120)
          ->telefono($d['telefono'], 'telefono', true)
          ->email($d['email'], 'email')
          ->requerido($d['ciudad'], 'ciudad', 3, 60);
        if (Empresa::nitExiste($d['nit'], $id)) {
            $v->error('nit', 'Ya existe una empresa registrada con ese NIT.');
        }
        if (!$v->ok()) {
            flash('danger', $v->primero());
            redirigir('a_empresa', ['id' => $id]);
        }
        Empresa::guardar($d, $id ?: null);
        flash('success', $id ? 'Empresa actualizada.' : 'Empresa registrada correctamente.');
        redirigir('a_empresas');
    }

    public function estadoEmpresa(): void
    {
        csrf_validar();
        $id = (int) post('id');
        $e = Empresa::obtener($id);
        if (!$e) {
            flash('danger', 'Empresa no encontrada.');
            redirigir('a_empresas');
        }
        $nuevo = $e['estado'] === 'activo' ? 'inactivo' : 'activo';
        Empresa::cambiarEstado($id, $nuevo);
        flash('success', 'Empresa ' . $e['nombre'] . ' ahora está ' . $nuevo . '.');
        redirigir('a_empresas');
    }

    public function guardarVehiculo(): void
    {
        $empresaId = (int) post('empresa_id');
        $v = new Validador();
        $v->requerido(post('placa'), 'placa', 5, 10)
          ->requerido(post('tipo'), 'tipo', 3, 40);
        if (!$v->ok() || $empresaId <= 0) {
            flash('danger', $v->ok() ? 'Empresa no válida.' : $v->primero());
            redirigir('a_empresa', ['id' => $empresaId]);
        }
        Empresa::guardarVehiculo([
            'empresa_id' => $empresaId, 'placa' => strtoupper(post('placa')),
            'tipo' => post('tipo'), 'capacidad' => post('capacidad'),
        ], get_int('vehiculo') ?: null);
        flash('success', 'Vehículo guardado.');
        redirigir('a_empresa', ['id' => $empresaId]);
    }

    // ===================================================== RF-17 materiales y categorías
    public function categorias(): void
    {
        vista('admin/categorias', [
            'titulo'     => 'Categorías',
            'categorias' => Material::categorias(false),
        ]);
    }

    public function guardarCategoria(): void
    {
        $id = (int) post('id');
        $v = new Validador();
        $v->requerido(post('nombre'), 'nombre', 3, 60);
        if (!$v->ok()) {
            flash('danger', $v->primero());
            redirigir('a_categorias');
        }
        try {
            Material::guardarCategoria([
                'nombre' => post('nombre'), 'descripcion' => post('descripcion'),
                'icono' => post('icono'), 'color' => post('color'),
            ], $id ?: null);
            flash('success', $id ? 'Categoría actualizada.' : 'Categoría creada.');
        } catch (Throwable $e) {
            flash('danger', 'No fue posible guardar la categoría: el nombre ya existe.');
        }
        redirigir('a_categorias');
    }

    public function estadoCategoria(): void
    {
        csrf_validar();
        $id = (int) post('id');
        $c = Material::categoria($id);
        if (!$c) {
            flash('danger', 'Categoría no encontrada.');
            redirigir('a_categorias');
        }
        if ($c['estado'] === 'activo' && Material::categoriaConMateriales($id) > 0) {
            flash('warning', 'No se puede desactivar: la categoría tiene materiales asociados. Desactive o mueva los materiales primero.');
            redirigir('a_categorias');
        }
        Material::estadoCategoria($id, $c['estado'] === 'activo' ? 'inactivo' : 'activo');
        flash('success', 'Categoría actualizada.');
        redirigir('a_categorias');
    }

    public function materiales(): void
    {
        $f = [
            'categoria_id' => get_int('categoria'),
            'estado'       => trim((string) ($_GET['estado'] ?? '')),
            'q'            => trim((string) ($_GET['q'] ?? '')),
        ];
        vista('admin/materiales', [
            'titulo'     => 'Materiales',
            'res'        => Material::listar($f, max(1, get_int('pag', 1)), 12),
            'filtros'    => $f,
            'categorias' => Material::categorias(false),
        ]);
    }

    public function material(): void
    {
        $id = get_int('id');
        vista('admin/material_form', [
            'titulo'     => $id > 0 ? 'Editar material' : 'Nuevo material',
            'm'          => $id > 0 ? Material::obtener($id) : null,
            'categorias' => Material::categorias(false),
            'unidades'   => unidadesMedida(),
        ]);
    }

    public function guardarMaterial(): void
    {
        $id = (int) post('id');
        $d = [
            'categoria_id' => (int) post('categoria_id'),
            'nombre' => post('nombre'), 'descripcion' => post('descripcion'),
            'recomendaciones' => post('recomendaciones'),
            'condiciones_entrega' => post('condiciones_entrega'),
            'unidad_medida' => post('unidad_medida'), 'icono' => post('icono'),
            'puntos_por_unidad' => post('puntos_por_unidad'),
        ];
        $v = new Validador();
        $v->requerido($d['nombre'], 'nombre', 3, 90)
          ->requerido($d['descripcion'], 'descripcion', 5, 400)
          ->enLista($d['unidad_medida'], unidadesMedida(), 'unidad_medida');
        if ($d['categoria_id'] <= 0 || !Material::categoria($d['categoria_id'])) {
            $v->error('categoria_id', 'Seleccione una categoría válida.');
        }
        if (Material::nombreExiste($d['nombre'], $id)) {
            $v->error('nombre', 'Ya existe un material con ese nombre.');
        }
        if (!$v->ok()) {
            flash('danger', $v->primero());
            redirigir('a_material', ['id' => $id]);
        }

        $foto = guardarFoto('imagen', 'materiales');
        if ($foto['error'] !== '') {
            flash('warning', 'Imagen: ' . $foto['error']);
        }
        if ($foto['ok']) {
            $d['imagen'] = $foto['archivo'];
        }
        Material::guardar($d, $id ?: null);
        flash('success', $id ? 'Material actualizado.' : 'Material creado correctamente.');
        redirigir('a_materiales');
    }

    public function estadoMaterial(): void
    {
        csrf_validar();
        $id = (int) post('id');
        $m = Material::obtener($id);
        if (!$m) {
            flash('danger', 'Material no encontrado.');
            redirigir('a_materiales');
        }
        Material::cambiarEstado($id, $m['estado'] === 'activo' ? 'inactivo' : 'activo');
        flash('success', 'Material ' . $m['nombre'] . ' ' . ($m['estado'] === 'activo' ? 'desactivado' : 'activado') . '.');
        redirigir('a_materiales');
    }

    // ===================================================== RF-19 solicitudes
    public function solicitudes(): void
    {
        $f = [
            'estado'      => trim((string) ($_GET['estado'] ?? '')),
            'ciudad'      => trim((string) ($_GET['ciudad'] ?? '')),
            'barrio'      => trim((string) ($_GET['barrio'] ?? '')),
            'empresa_id'  => get_int('empresa_id'),
            'material_id' => get_int('material_id'),
            'desde'       => trim((string) ($_GET['desde'] ?? '')),
            'hasta'       => trim((string) ($_GET['hasta'] ?? '')),
            'q'           => trim((string) ($_GET['q'] ?? '')),
        ];
        vista('admin/solicitudes', [
            'titulo'     => 'Solicitudes',
            'res'        => Solicitud::listar($f, max(1, get_int('pag', 1)), 12),
            'filtros'    => $f,
            'empresas'   => Empresa::activas(),
            'materiales' => Material::activos(),
            'ciudades'   => Solicitud::ciudades(),
        ]);
    }

    public function solicitud(): void
    {
        $id = get_int('id');
        $s = Solicitud::obtener($id);
        if (!$s) {
            flash('warning', 'La solicitud no existe.');
            redirigir('a_solicitudes');
        }
        $rec = Recoleccion::porSolicitud($id);
        vista('admin/solicitud', [
            'titulo'    => 'Solicitud ' . $s['numero'],
            's'         => $s,
            'detalle'   => Solicitud::materialesDe($id),
            'historial' => Solicitud::historial($id),
            'rec'       => $rec,
            'recMateriales' => $rec ? Recoleccion::materiales((int) $rec['id']) : [],
        ]);
    }

    public function recolecciones(): void
    {
        $f = [
            'empresa_id'  => get_int('empresa_id'),
            'ciudad'      => trim((string) ($_GET['ciudad'] ?? '')),
            'barrio'      => trim((string) ($_GET['barrio'] ?? '')),
            'material_id' => get_int('material_id'),
            'desde'       => trim((string) ($_GET['desde'] ?? '')),
            'hasta'       => trim((string) ($_GET['hasta'] ?? '')),
            'q'           => trim((string) ($_GET['q'] ?? '')),
        ];
        vista('admin/recolecciones', [
            'titulo'     => 'Recolecciones',
            'res'        => Recoleccion::listar($f, max(1, get_int('pag', 1)), 12),
            'filtros'    => $f,
            'empresas'   => Empresa::activas(),
            'materiales' => Material::activos(),
            'ciudades'   => Solicitud::ciudades(),
        ]);
    }

    // ===================================================== RF-21 reportes
    public function reportes(): void
    {
        $tipo = (string) ($_GET['tipo'] ?? 'resumen');
        $f = [
            'desde'        => trim((string) ($_GET['desde'] ?? date('Y-m-01'))),
            'hasta'        => trim((string) ($_GET['hasta'] ?? date('Y-m-d'))),
            'ciudad'       => trim((string) ($_GET['ciudad'] ?? '')),
            'barrio'       => trim((string) ($_GET['barrio'] ?? '')),
            'empresa_id'   => get_int('empresa_id'),
            'material_id'  => get_int('material_id'),
            'categoria_id' => get_int('categoria'),
        ];
        $datos = match ($tipo) {
            'fecha'    => Reporte::porFecha($f),
            'material' => Reporte::porMaterial($f),
            'zona'     => Reporte::porZona($f),
            'empresa'  => Reporte::porEmpresa($f),
            'estado'   => Reporte::porEstado($f),
            'puntos'   => Puntos::porMaterial($f),
            default    => ['filas' => [], 'desde' => $f['desde'], 'hasta' => $f['hasta'], 'filtros' => $f],
        };
        vista('admin/reportes', [
            'titulo'      => 'Reportes y estadísticas',
            'tipo'        => $tipo,
            'datos'       => $datos,
            'filtros'     => $f,
            'resumen'     => Reporte::resumen(),
            'serie'       => Reporte::serieMensual(6),
            'empresas'    => Empresa::activas(),
            'materiales'  => Material::activos(),
            'categorias'  => Material::categorias(false),
            'ciudades'    => Solicitud::ciudades(),
            'puntosResumen' => Puntos::resumenAdmin(),
            'seriePuntos'   => Puntos::serieMensual(6),
            'topCiudadanos' => Puntos::topCiudadanos(10),
        ]);
    }

    /** Exporta a CSV el reporte solicitado (útil para Excel). */
    public function reporteCsv(): void
    {
        $tipo = (string) ($_GET['tipo'] ?? 'material');
        $f = [
            'desde'        => trim((string) ($_GET['desde'] ?? date('Y-m-01'))),
            'hasta'        => trim((string) ($_GET['hasta'] ?? date('Y-m-d'))),
            'ciudad'       => trim((string) ($_GET['ciudad'] ?? '')),
            'barrio'       => trim((string) ($_GET['barrio'] ?? '')),
            'empresa_id'   => get_int('empresa_id'),
            'material_id'  => get_int('material_id'),
            'categoria_id' => get_int('categoria'),
        ];
        $datos = match ($tipo) {
            'fecha'   => Reporte::porFecha($f)['filas'],
            'zona'    => Reporte::porZona($f)['filas'],
            'empresa' => Reporte::porEmpresa($f)['filas'],
            'puntos'  => Puntos::porMaterial($f)['filas'],
            default   => Reporte::porMaterial($f)['filas'],
        };
        $nombre = 'recicla_' . $tipo . '_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $nombre . '"');
        $salida = fopen('php://output', 'w');
        fprintf($salida, chr(0xEF) . chr(0xBB) . chr(0xBF));   // BOM para Excel
        if ($datos) {
            fputcsv($salida, array_keys($datos[0]), ';');
            foreach ($datos as $fila) {
                fputcsv($salida, array_values($fila), ';');
            }
        } else {
            fputcsv($salida, ['Sin datos para el rango seleccionado'], ';');
        }
        fclose($salida);
        exit;
    }

    // ===================================================== configuración
    public function configuracion(): void
    {
        vista('admin/configuracion', [
            'titulo' => 'Configuración',
            'items'  => db()->query('SELECT clave, valor FROM configuracion ORDER BY clave')->fetchAll(),
        ]);
    }

    public function guardarConfiguracion(): void
    {
        $permitidos = ['nombre_sistema', 'email_contacto', 'telefono_contacto', 'horario_atencion', 'max_kg_solicitud'];
        $v = new Validador();
        $v->requerido(post('nombre_sistema'), 'nombre_sistema', 3, 60)
          ->email(post('email_contacto'), 'email_contacto')
          ->numero(post('max_kg_solicitud'), 'max_kg_solicitud', 1, 100000);
        if (!$v->ok()) {
            flash('danger', $v->primero());
            redirigir('a_config');
        }
        foreach ($permitidos as $clave) {
            if (isset($_POST[$clave])) {
                config_set($clave, trim((string) $_POST[$clave]));
            }
        }
        flash('success', 'Configuración actualizada.');
        redirigir('a_config');
    }
}
