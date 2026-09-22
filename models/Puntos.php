<?php
/**
 * RECICLA+ | Modelo Puntos y Premios (programa de incentivos)
 * ------------------------------------------------------------------
 * Regla del programa: cada recolección CONFIRMADA por la empresa operadora
 * otorga puntos al ciudadano (puntos por unidad de cada material + un bono
 * por recolección completada). Los puntos se cambian por premios del catálogo.
 */
declare(strict_types=1);

final class Puntos
{
    // =================================================== saldos y niveles
    /** Saldo disponible del ciudadano (puntos ganados menos canjeados). */
    public static function saldo(int $usuarioId): int
    {
        $st = db()->prepare('SELECT COALESCE(SUM(puntos), 0) FROM puntos_movimientos WHERE usuario_id = ?');
        $st->execute([$usuarioId]);
        return (int) $st->fetchColumn();
    }

    /** Total histórico de puntos ganados (define el nivel del ciudadano). */
    public static function totalGanado(int $usuarioId): int
    {
        $st = db()->prepare("SELECT COALESCE(SUM(puntos), 0) FROM puntos_movimientos
                             WHERE usuario_id = ? AND tipo = 'ganados'");
        $st->execute([$usuarioId]);
        return (int) $st->fetchColumn();
    }

    public static function totalCanjeado(int $usuarioId): int
    {
        $st = db()->prepare("SELECT COALESCE(SUM(puntos), 0) FROM puntos_movimientos
                             WHERE usuario_id = ? AND tipo = 'canjeados'");
        $st->execute([$usuarioId]);
        return abs((int) $st->fetchColumn());
    }

    /** Nivel de compromiso alcanzado según los puntos ganados. */
    public static function nivel(int $puntosGanados): array
    {
        $niveles = [
            ['min' => 2000, 'nombre' => 'Embajador del reciclaje', 'icono' => 'fa-star',        'siguiente' => null],
            ['min' => 800,  'nombre' => 'Guardián verde',          'icono' => 'fa-shield-heart','siguiente' => 2000],
            ['min' => 200,  'nombre' => 'Reciclador activo',       'icono' => 'fa-recycle',     'siguiente' => 800],
            ['min' => 0,    'nombre' => 'Semilla',                 'icono' => 'fa-seedling',    'siguiente' => 200],
        ];
        foreach ($niveles as $n) {
            if ($puntosGanados >= $n['min']) {
                $faltan = $n['siguiente'] !== null ? max(0, $n['siguiente'] - $puntosGanados) : 0;
                return $n + ['faltan' => $faltan];
            }
        }
        return $niveles[3] + ['faltan' => 200];
    }

    // =================================================== historial
    public static function historial(int $usuarioId, int $pagina = 1, int $porPagina = 12): array
    {
        $st = db()->prepare('SELECT COUNT(*) FROM puntos_movimientos WHERE usuario_id = ?');
        $st->execute([$usuarioId]);
        $pg = paginar((int) $st->fetchColumn(), $porPagina, $pagina);

        $st = db()->prepare("SELECT pm.*, s.numero, p.nombre AS premio
                             FROM puntos_movimientos pm
                             LEFT JOIN solicitudes s ON s.id = pm.solicitud_id
                             LEFT JOIN canjes c ON c.id = pm.canje_id
                             LEFT JOIN premios p ON p.id = c.premio_id
                             WHERE pm.usuario_id = ?
                             ORDER BY pm.id DESC LIMIT {$pg['limite']} OFFSET {$pg['offset']}");
        $st->execute([$usuarioId]);
        return ['filas' => $st->fetchAll(), 'pag' => $pg];
    }

    /** Ranking de ciudadanos por puntos ganados (motivación). */
    public static function ranking(int $limite = 5): array
    {
        $sql = "SELECT u.id, u.nombres, u.apellidos, u.barrio, u.ciudad,
                       SUM(pm.puntos) AS puntos, COUNT(pm.id) AS movimientos
                FROM puntos_movimientos pm
                JOIN usuarios u ON u.id = pm.usuario_id
                WHERE pm.tipo = 'ganados'
                GROUP BY u.id, u.nombres, u.apellidos, u.barrio, u.ciudad
                HAVING puntos > 0
                ORDER BY puntos DESC, movimientos ASC
                LIMIT " . (int) $limite;
        $filas = db()->query($sql)->fetchAll();
        $puesto = 0;
        foreach ($filas as &$f) {
            $f['puesto'] = ++$puesto;
        }
        return $filas;
    }

    public static function posicion(int $usuarioId): int
    {
        $sql = "SELECT COUNT(*) + 1 FROM (
                    SELECT usuario_id, SUM(puntos) AS puntos FROM puntos_movimientos
                    WHERE tipo = 'ganados' GROUP BY usuario_id
                ) t WHERE t.puntos > (SELECT COALESCE(SUM(puntos),0) FROM puntos_movimientos
                                      WHERE usuario_id = ? AND tipo = 'ganados')";
        $st = db()->prepare($sql);
        $st->execute([$usuarioId]);
        return (int) $st->fetchColumn();
    }

    // =================================================== otorgar puntos (RF-14)
    /** Otorga los puntos de una recolección confirmada. Idempotente. */
    public static function otorgarPorRecoleccion(int $recoleccionId, int $ciudadanoId, int $solicitudId): int
    {
        $pdo = db();
        $st = $pdo->prepare("SELECT COUNT(*) FROM puntos_movimientos WHERE recoleccion_id = ? AND tipo = 'ganados'");
        $st->execute([$recoleccionId]);
        if ((int) $st->fetchColumn() > 0) {
            return 0;   // ya se otorgaron: no duplicar
        }

        $lineas = $pdo->prepare('SELECT rm.cantidad, rm.unidad, m.nombre, m.puntos_por_unidad
                                 FROM recoleccion_materiales rm
                                 JOIN materiales m ON m.id = rm.material_id
                                 WHERE rm.recoleccion_id = ? ORDER BY m.nombre');
        $lineas->execute([$recoleccionId]);
        $detalle = $lineas->fetchAll();

        $insertar = $pdo->prepare('INSERT INTO puntos_movimientos
            (usuario_id, solicitud_id, recoleccion_id, tipo, puntos, descripcion)
            VALUES (?, ?, ?, \'ganados\', ?, ?)');

        $total = 0;
        foreach ($detalle as $d) {
            $puntos = (int) round((float) $d['cantidad'] * (int) $d['puntos_por_unidad']);
            if ($puntos <= 0) {
                continue;
            }
            $insertar->execute([
                $ciudadanoId, $solicitudId, $recoleccionId, $puntos,
                sprintf('%s recolectado: %s %s', $d['nombre'], formatearCantidad((float) $d['cantidad']), $d['unidad']),
            ]);
            $total += $puntos;
        }

        $bono = (int) config_get('puntos_bonus_recoleccion', '20');
        if ($bono > 0 && $detalle) {
            $insertar->execute([$ciudadanoId, $solicitudId, $recoleccionId, $bono,
                'Bono por recolección completada con éxito']);
            $total += $bono;
        }
        return $total;
    }

    /**
     * Ajuste manual de puntos por parte del administrador (puede ser negativo).
     * Un descuento nunca puede dejar el saldo en negativo: en ese caso se rechaza
     * con un mensaje claro en lugar de corromper el saldo del ciudadano.
     */
    public static function ajuste(int $usuarioId, int $puntos, string $descripcion, int $adminId): void
    {
        if ($puntos < 0) {
            $disponible = self::saldo($usuarioId);
            if ($disponible <= 0) {
                throw new RuntimeException('El ciudadano no tiene puntos disponibles para descontar (saldo actual: 0).');
            }
            if (abs($puntos) > $disponible) {
                throw new RuntimeException('El descuento de ' . number_format(abs($puntos), 0, ',', '.')
                    . ' puntos supera el saldo disponible del ciudadano ('
                    . number_format($disponible, 0, ',', '.') . ' puntos). Ajuste no aplicado.');
            }
        }
        db()->prepare("INSERT INTO puntos_movimientos (usuario_id, tipo, puntos, descripcion)
                       VALUES (?, 'ajuste', ?, ?)")
            ->execute([$usuarioId, $puntos, $descripcion . ' (ajuste del administrador #' . $adminId . ')']);
        notificar($usuarioId, 'Ajuste de puntos',
            ($puntos >= 0 ? 'Se sumaron ' : 'Se descontaron ') . abs($puntos) . ' puntos a tu cuenta. ' . $descripcion,
            'mis_puntos');
    }

    public static function otorgadosDeSolicitud(int $solicitudId): int
    {
        $st = db()->prepare("SELECT COALESCE(SUM(puntos),0) FROM puntos_movimientos
                             WHERE solicitud_id = ? AND tipo = 'ganados'");
        $st->execute([$solicitudId]);
        return (int) $st->fetchColumn();
    }

    /** Puntos estimados que otorgaría una solicitud según lo publicado (informativo). */
    public static function estimadosDeSolicitud(int $solicitudId): int
    {
        $st = db()->prepare('SELECT COALESCE(SUM(ROUND(sm.cantidad * m.puntos_por_unidad)),0)
                             FROM solicitud_materiales sm
                             JOIN materiales m ON m.id = sm.material_id
                             WHERE sm.solicitud_id = ?');
        $st->execute([$solicitudId]);
        return (int) $st->fetchColumn() + (int) config_get('puntos_bonus_recoleccion', '20');
    }

    // =================================================== catálogo de premios
    public static function premios(bool $soloActivos = true, bool $soloDisponibles = false): array
    {
        $w = [];
        if ($soloActivos) {
            $w[] = "p.estado = 'activo'";
        }
        if ($soloDisponibles) {
            $w[] = 'p.stock > 0';
        }
        $sql = 'SELECT p.*, (SELECT COUNT(*) FROM canjes c WHERE c.premio_id = p.id) AS total_canjes
                FROM premios p' . ($w ? ' WHERE ' . implode(' AND ', $w) : '') . ' ORDER BY p.puntos_requeridos ASC';
        return db()->query($sql)->fetchAll();
    }

    public static function premio(int $id): ?array
    {
        $st = db()->prepare('SELECT * FROM premios WHERE id = ?');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public static function nombrePremioExiste(string $nombre, int $excepto = 0): bool
    {
        $st = db()->prepare('SELECT COUNT(*) FROM premios WHERE nombre = ? AND id <> ?');
        $st->execute([$nombre, $excepto]);
        return (int) $st->fetchColumn() > 0;
    }

    public static function guardarPremio(array $d, ?int $id = null): int
    {
        if ($id) {
            db()->prepare('UPDATE premios SET nombre = :nom, descripcion = :des, puntos_requeridos = :pun,
                    stock = :sto, icono = :ico, imagen = COALESCE(:img, imagen) WHERE id = :id')
                ->execute([
                    ':nom' => $d['nombre'], ':des' => $d['descripcion'] ?: null,
                    ':pun' => (int) $d['puntos_requeridos'], ':sto' => (int) $d['stock'],
                    ':ico' => $d['icono'] ?: 'fa-gift', ':img' => $d['imagen'] ?? null, ':id' => $id,
                ]);
            return $id;
        }
        db()->prepare('INSERT INTO premios (nombre, descripcion, puntos_requeridos, stock, icono, imagen)
                VALUES (:nom, :des, :pun, :sto, :ico, :img)')
            ->execute([
                ':nom' => $d['nombre'], ':des' => $d['descripcion'] ?: null,
                ':pun' => (int) $d['puntos_requeridos'], ':sto' => (int) $d['stock'],
                ':ico' => $d['icono'] ?: 'fa-gift', ':img' => $d['imagen'] ?? null,
            ]);
        return (int) db()->lastInsertId();
    }

    public static function estadoPremio(int $id, string $estado): void
    {
        db()->prepare('UPDATE premios SET estado = ? WHERE id = ?')
            ->execute([$estado === 'activo' ? 'activo' : 'inactivo', $id]);
    }

    // =================================================== canje de premios
    /**
     * Canjea un premio con los puntos del ciudadano.
     * @return array{ok:bool, mensaje:string, codigo:?string}
     */
    public static function canjear(int $usuarioId, int $premioId): array
    {
        $pdo = db();
        $premio = self::premio($premioId);
        if (!$premio || $premio['estado'] !== 'activo') {
            return ['ok' => false, 'mensaje' => 'El premio seleccionado no está disponible.', 'codigo' => null];
        }
        if ((int) $premio['stock'] <= 0) {
            return ['ok' => false, 'mensaje' => 'El premio "' . $premio['nombre'] . '" está agotado.', 'codigo' => null];
        }
        $saldo = self::saldo($usuarioId);
        if ($saldo < (int) $premio['puntos_requeridos']) {
            return ['ok' => false, 'mensaje' => 'Te faltan ' . ((int) $premio['puntos_requeridos'] - $saldo)
                . ' puntos para este premio (tienes ' . $saldo . ').', 'codigo' => null];
        }
        $minimos = (int) config_get('puntos_minimos_canje', '0');
        if ($saldo < $minimos) {
            return ['ok' => false, 'mensaje' => 'El canje requiere mínimo ' . $minimos . ' puntos acumulados.', 'codigo' => null];
        }

        $pdo->beginTransaction();
        try {
            $pdo->prepare('INSERT INTO canjes (codigo, usuario_id, premio_id, puntos, estado)
                           VALUES (?, ?, ?, ?, \'solicitado\')')
                ->execute(['TMP', $usuarioId, $premioId, (int) $premio['puntos_requeridos']]);
            $canjeId = (int) $pdo->lastInsertId();
            $codigo = 'CANJE-' . str_pad((string) $canjeId, 6, '0', STR_PAD_LEFT);
            $pdo->prepare('UPDATE canjes SET codigo = ? WHERE id = ?')->execute([$codigo, $canjeId]);
            $pdo->prepare('UPDATE premios SET stock = stock - 1 WHERE id = ? AND stock > 0')->execute([$premioId]);

            $pdo->prepare("INSERT INTO puntos_movimientos (usuario_id, canje_id, tipo, puntos, descripcion)
                           VALUES (?, ?, 'canjeados', ?, ?)")
                ->execute([$usuarioId, $canjeId, -1 * (int) $premio['puntos_requeridos'],
                           'Canje ' . $codigo . ': ' . $premio['nombre']]);
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            error_log('[RECICLA+] Error al canjear: ' . $e->getMessage());
            return ['ok' => false, 'mensaje' => 'No fue posible registrar el canje. Intente nuevamente.', 'codigo' => null];
        }

        notificar($usuarioId, 'Canje registrado',
            'Solicitaste el premio "' . $premio['nombre'] . '" con el código ' . $codigo
            . '. Un administrador coordinará la entrega.', 'mis_puntos');
        foreach ($pdo->query("SELECT u.id FROM usuarios u JOIN roles r ON r.id = u.rol_id
                              WHERE r.nombre = 'administrador' AND u.estado = 'activo'") as $adm) {
            notificar((int) $adm['id'], 'Nuevo canje de premio',
                'El ciudadano #' . $usuarioId . ' canjeó "' . $premio['nombre'] . '" (' . $codigo . ').',
                'a_canjes');
        }
        return ['ok' => true, 'mensaje' => '¡Canje registrado! Tu código es ' . $codigo . '.', 'codigo' => $codigo];
    }

    public static function canjesDeCiudadano(int $usuarioId, int $limite = 10): array
    {
        $st = db()->prepare('SELECT c.*, p.nombre AS premio, p.icono
                             FROM canjes c JOIN premios p ON p.id = c.premio_id
                             WHERE c.usuario_id = ? ORDER BY c.id DESC LIMIT ' . (int) $limite);
        $st->execute([$usuarioId]);
        return $st->fetchAll();
    }

    public static function listarCanjes(array $f = [], int $pagina = 1, int $porPagina = 15): array
    {
        $w = ['1 = 1'];
        $p = [];
        if (!empty($f['estado']))   { $w[] = 'c.estado = ?';       $p[] = $f['estado']; }
        if (!empty($f['premio_id'])){ $w[] = 'c.premio_id = ?';    $p[] = (int) $f['premio_id']; }
        if (!empty($f['q'])) {
            $w[] = '(c.codigo LIKE ? OR u.documento LIKE ? OR u.nombres LIKE ? OR u.apellidos LIKE ? OR p.nombre LIKE ?)';
            $t = '%' . $f['q'] . '%';
            array_push($p, $t, $t, $t, $t, $t);
        }
        $where = implode(' AND ', $w);

        $st = db()->prepare("SELECT COUNT(*) FROM canjes c JOIN usuarios u ON u.id = c.usuario_id
                             JOIN premios p ON p.id = c.premio_id WHERE $where");
        $st->execute($p);
        $pg = paginar((int) $st->fetchColumn(), $porPagina, $pagina);

        $st = db()->prepare("SELECT c.*, p.nombre AS premio, p.icono,
                    CONCAT(u.nombres,' ',u.apellidos) AS ciudadano, u.documento, u.telefono, u.email, u.ciudad, u.barrio
                FROM canjes c
                JOIN usuarios u ON u.id = c.usuario_id
                JOIN premios p ON p.id = c.premio_id
                WHERE $where
                ORDER BY FIELD(c.estado,'solicitado','entregado','cancelado'), c.id DESC
                LIMIT {$pg['limite']} OFFSET {$pg['offset']}");
        $st->execute($p);
        return ['filas' => $st->fetchAll(), 'pag' => $pg];
    }

    /** Cambia el estado de un canje; al cancelar devuelve los puntos y el stock. */
    public static function estadoCanje(int $canjeId, string $estado, int $adminId): array
    {
        $pdo = db();
        $st = $pdo->prepare('SELECT c.*, p.nombre AS premio FROM canjes c JOIN premios p ON p.id = c.premio_id WHERE c.id = ?');
        $st->execute([$canjeId]);
        $c = $st->fetch();
        if (!$c) {
            return ['ok' => false, 'mensaje' => 'Canje no encontrado.'];
        }
        if ($c['estado'] === $estado) {
            return ['ok' => false, 'mensaje' => 'El canje ya está en estado ' . $estado . '.'];
        }
        if ($c['estado'] === 'cancelado') {
            return ['ok' => false, 'mensaje' => 'Un canje cancelado no puede modificarse.'];
        }

        $pdo->beginTransaction();
        try {
            if ($estado === 'cancelado') {
                $pdo->prepare('UPDATE canjes SET estado = \'cancelado\', observaciones = CONCAT(COALESCE(observaciones,\'\'),
                               \' Cancelado por el administrador.\') WHERE id = ?')->execute([$canjeId]);
                $pdo->prepare('UPDATE premios SET stock = stock + 1 WHERE id = ?')->execute([(int) $c['premio_id']]);
                $pdo->prepare("INSERT INTO puntos_movimientos (usuario_id, canje_id, tipo, puntos, descripcion)
                               VALUES (?, ?, 'ajuste', ?, ?)")
                    ->execute([(int) $c['usuario_id'], $canjeId, (int) $c['puntos'],
                               'Devolución de puntos por canje cancelado ' . $c['codigo']]);
            } elseif ($estado === 'entregado') {
                $pdo->prepare('UPDATE canjes SET estado = \'entregado\', fecha_entrega = NOW(), atendido_por = ? WHERE id = ?')
                    ->execute([$adminId, $canjeId]);
            } else {
                $pdo->prepare('UPDATE canjes SET estado = ? WHERE id = ?')->execute([$estado, $canjeId]);
            }
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            return ['ok' => false, 'mensaje' => 'No fue posible actualizar el canje.'];
        }

        notificar((int) $c['usuario_id'], 'Canje ' . $estado,
            'Tu canje ' . $c['codigo'] . ' (' . $c['premio'] . ') quedó en estado: ' . $estado
            . ($estado === 'cancelado' ? '. Los puntos fueron devueltos a tu cuenta.' : '.'),
            'mis_puntos');
        return ['ok' => true, 'mensaje' => 'Canje ' . $c['codigo'] . ' actualizado a ' . $estado . '.'];
    }

    // =================================================== estadísticas
    public static function resumenAdmin(): array
    {
        $pdo = db();
        $r = [];
        $r['otorgados']  = (int) $pdo->query("SELECT COALESCE(SUM(puntos),0) FROM puntos_movimientos WHERE tipo = 'ganados'")->fetchColumn();
        $r['canjeados']  = abs((int) $pdo->query("SELECT COALESCE(SUM(puntos),0) FROM puntos_movimientos WHERE tipo = 'canjeados'")->fetchColumn());
        $r['saldo_total'] = (int) $pdo->query('SELECT COALESCE(SUM(puntos),0) FROM puntos_movimientos')->fetchColumn();
        $r['movimientos'] = (int) $pdo->query('SELECT COUNT(*) FROM puntos_movimientos')->fetchColumn();
        $r['premios']     = (int) $pdo->query("SELECT COUNT(*) FROM premios WHERE estado = 'activo'")->fetchColumn();
        $r['agotados']    = (int) $pdo->query('SELECT COUNT(*) FROM premios WHERE stock <= 0')->fetchColumn();
        $r['canjes']      = (int) $pdo->query('SELECT COUNT(*) FROM canjes')->fetchColumn();
        $r['pendientes']  = (int) $pdo->query("SELECT COUNT(*) FROM canjes WHERE estado = 'solicitado'")->fetchColumn();
        $r['entregados']  = (int) $pdo->query("SELECT COUNT(*) FROM canjes WHERE estado = 'entregado'")->fetchColumn();
        $r['participantes'] = (int) $pdo->query('SELECT COUNT(DISTINCT usuario_id) FROM puntos_movimientos')->fetchColumn();
        return $r;
    }

    /** Reporte mensual de puntos otorgados y canjeados. */
    public static function serieMensual(int $meses = 6): array
    {
        $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') AS mes,
                       COALESCE(SUM(CASE WHEN tipo = 'ganados' THEN puntos ELSE 0 END), 0) AS ganados,
                       ABS(COALESCE(SUM(CASE WHEN tipo = 'canjeados' THEN puntos ELSE 0 END), 0)) AS canjeados,
                       COUNT(*) AS movimientos
                FROM puntos_movimientos
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL " . (int) $meses . " MONTH)
                GROUP BY mes";
        $datos = [];
        foreach (db()->query($sql) as $f) {
            $datos[$f['mes']] = ['ganados' => (int) $f['ganados'], 'canjeados' => (int) $f['canjeados'], 'movimientos' => (int) $f['movimientos']];
        }
        $serie = [];
        for ($i = $meses - 1; $i >= 0; $i--) {
            $clave = date('Y-m', strtotime("-$i month"));
            $serie[] = [
                'mes' => $clave, 'label' => date('M', strtotime($clave . '-01')),
                'ganados' => $datos[$clave]['ganados'] ?? 0,
                'canjeados' => $datos[$clave]['canjeados'] ?? 0,
                'movimientos' => $datos[$clave]['movimientos'] ?? 0,
            ];
        }
        return $serie;
    }

    /** Puntos otorgados y canjeados agrupados por material (reporte). */
    public static function porMaterial(array $f): array
    {
        $desde = !empty($f['desde']) ? $f['desde'] : date('Y-m-01');
        $hasta = !empty($f['hasta']) ? $f['hasta'] : date('Y-m-d');
        $st = db()->prepare("SELECT m.nombre AS material, c.nombre AS categoria, c.color AS color,
                    m.puntos_por_unidad, COUNT(DISTINCT pm.id) AS movimientos,
                    SUM(pm.puntos) AS puntos
                FROM puntos_movimientos pm
                JOIN recolecciones r ON r.id = pm.recoleccion_id
                JOIN recoleccion_materiales rm ON rm.recoleccion_id = r.id
                JOIN materiales m ON m.id = rm.material_id
                JOIN categorias c ON c.id = m.categoria_id
                WHERE pm.tipo = 'ganados' AND DATE(pm.created_at) BETWEEN ? AND ?
                GROUP BY m.id, m.nombre, c.nombre, c.color, m.puntos_por_unidad
                ORDER BY puntos DESC");
        $st->execute([$desde, $hasta]);
        return ['filas' => $st->fetchAll(), 'desde' => $desde, 'hasta' => $hasta, 'filtros' => $f];
    }

    /** Ciudades/localidades con más puntos entregados (para campañas). */
    public static function topCiudadanos(int $limite = 10): array
    {
        $sql = "SELECT CONCAT(u.nombres,' ',u.apellidos) AS ciudadano, u.documento, u.barrio, u.ciudad,
                       SUM(pm.puntos) AS puntos, COUNT(DISTINCT pm.recoleccion_id) AS recolecciones
                FROM puntos_movimientos pm JOIN usuarios u ON u.id = pm.usuario_id
                WHERE pm.tipo = 'ganados'
                GROUP BY u.id, ciudadano, u.documento, u.barrio, u.ciudad
                ORDER BY puntos DESC LIMIT " . (int) $limite;
        return db()->query($sql)->fetchAll();
    }
}
