<?php
/**
 * RECICLA+ | Modelo Solicitud de recolección (RF-08 … RF-15, RF-19)
 */
declare(strict_types=1);

final class Solicitud
{
    /** Crea la solicitud + su detalle de materiales y genera REC-000001 (RF-08). */
    public static function crear(array $d, array $materiales, int $ciudadanoId): array
    {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $st = $pdo->prepare('INSERT INTO solicitudes
                (numero, ciudadano_id, direccion, barrio, ciudad, fecha_disponible, horario_disponible, observaciones, estado)
                VALUES (:numero, :ciudadano, :direccion, :barrio, :ciudad, :fecha, :horario, :obs, \'REGISTRADA\')');
            $st->execute([
                ':numero'    => 'TMP',
                ':ciudadano' => $ciudadanoId,
                ':direccion' => $d['direccion'],
                ':barrio'    => $d['barrio'],
                ':ciudad'    => $d['ciudad'],
                ':fecha'     => $d['fecha_disponible'],
                ':horario'   => $d['horario_disponible'],
                ':obs'       => $d['observaciones'] ?: null,
            ]);
            $id = (int) $pdo->lastInsertId();
            $numero = 'REC-' . str_pad((string) $id, 6, '0', STR_PAD_LEFT);
            $pdo->prepare('UPDATE solicitudes SET numero = ? WHERE id = ?')->execute([$numero, $id]);

            $detalle = $pdo->prepare('INSERT INTO solicitud_materiales
                (solicitud_id, material_id, cantidad, unidad, descripcion, observaciones, foto)
                VALUES (?, ?, ?, ?, ?, ?, ?)');
            foreach ($materiales as $m) {
                $detalle->execute([
                    $id, (int) $m['material_id'], (float) $m['cantidad'], $m['unidad'],
                    $m['descripcion'] ?: null, $m['observaciones'] ?: null, $m['foto'] ?? null,
                ]);
            }
            registrarHistorial($id, $ciudadanoId, null, 'REGISTRADA', 'Solicitud creada por el ciudadano.');
            $pdo->commit();
            return ['id' => $id, 'numero' => $numero];
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /** Consulta completa de una solicitud con relación de apoyo. */
    public static function obtener(int $id): ?array
    {
        $st = db()->prepare('SELECT s.*, 
                CONCAT(c.nombres, \' \', c.apellidos) AS ciudadano_nombre,
                c.documento AS ciudadano_documento, c.telefono AS ciudadano_telefono,
                c.email AS ciudadano_email, c.direccion AS ciudadano_direccion,
                e.nombre AS empresa_nombre, e.nit AS empresa_nit, e.telefono AS empresa_telefono,
                e.responsable AS empresa_responsable,
                v.placa, v.tipo AS vehiculo_tipo,
                r.nombre AS ruta_nombre, r.zona AS ruta_zona,
                CONCAT(ue.nombres, \' \', ue.apellidos) AS aceptada_por
            FROM solicitudes s
            JOIN usuarios c  ON c.id = s.ciudadano_id
            LEFT JOIN empresas e ON e.id = s.empresa_id
            LEFT JOIN vehiculos v ON v.id = s.vehiculo_id
            LEFT JOIN rutas r ON r.id = s.ruta_id
            LEFT JOIN usuarios ue ON ue.id = s.empresa_usuario_id
            WHERE s.id = ? LIMIT 1');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public static function materialesDe(int $solicitudId): array
    {
        $st = db()->prepare('SELECT sm.*, m.nombre AS material, c.nombre AS categoria, c.color AS categoria_color,
                                    m.icono, m.unidad_medida
                             FROM solicitud_materiales sm
                             JOIN materiales m ON m.id = sm.material_id
                             JOIN categorias c ON c.id = m.categoria_id
                             WHERE sm.solicitud_id = ? ORDER BY m.nombre');
        $st->execute([$solicitudId]);
        return $st->fetchAll();
    }

    public static function historial(int $solicitudId): array
    {
        $st = db()->prepare('SELECT h.*, CONCAT(u.nombres, \' \', u.apellidos) AS usuario, r.nombre AS rol
                             FROM historial_solicitudes h
                             LEFT JOIN usuarios u ON u.id = h.usuario_id
                             LEFT JOIN roles r ON r.id = u.rol_id
                             WHERE h.solicitud_id = ? ORDER BY h.id ASC');
        $st->execute([$solicitudId]);
        return $st->fetchAll();
    }

    /** Filtros comunes: estado, ciudad, barrio, material, fechas, empresa. */
    private static function where(array $f): array
    {
        $w = ['1 = 1'];
        $p = [];
        if (!empty($f['estado']))  { $w[] = 's.estado = ?';        $p[] = $f['estado']; }
        if (!empty($f['estados']) && is_array($f['estados'])) {
            $w[] = 's.estado IN (' . implode(',', array_fill(0, count($f['estados']), '?')) . ')';
            foreach ($f['estados'] as $est) { $p[] = $est; }
        }
        if (!empty($f['ciudad']))  { $w[] = 's.ciudad = ?';        $p[] = $f['ciudad']; }
        if (!empty($f['barrio']))  { $w[] = 's.barrio = ?';        $p[] = $f['barrio']; }
        if (!empty($f['empresa_id'])) { $w[] = 's.empresa_id = ?'; $p[] = (int) $f['empresa_id']; }
        if (!empty($f['ciudadano_id'])) { $w[] = 's.ciudadano_id = ?'; $p[] = (int) $f['ciudadano_id']; }
        if (!empty($f['desde']))   { $w[] = 's.fecha_disponible >= ?'; $p[] = $f['desde']; }
        if (!empty($f['hasta']))   { $w[] = 's.fecha_disponible <= ?'; $p[] = $f['hasta']; }
        if (!empty($f['q'])) {
            $w[] = '(s.numero LIKE ? OR s.barrio LIKE ? OR s.direccion LIKE ? OR c.documento LIKE ?)';
            $t = '%' . $f['q'] . '%';
            array_push($p, $t, $t, $t, $t);
        }
        if (!empty($f['material_id'])) {
            $w[] = 'EXISTS (SELECT 1 FROM solicitud_materiales x WHERE x.solicitud_id = s.id AND x.material_id = ?)';
            $p[] = (int) $f['material_id'];
        }
        if (!empty($f['pendientes'])) {
            $w[] = "s.estado IN ('REGISTRADA','EN_REVISION') AND s.empresa_id IS NULL";
        }
        return [implode(' AND ', $w), $p];
    }

    public static function listar(array $f = [], int $pagina = 1, int $porPagina = 12): array
    {
        [$where, $p] = self::where($f);

        $st = db()->prepare("SELECT COUNT(*) FROM solicitudes s JOIN usuarios c ON c.id = s.ciudadano_id WHERE $where");
        $st->execute($p);
        $pg = paginar((int) $st->fetchColumn(), $porPagina, $pagina);

        $sql = "SELECT s.*, CONCAT(c.nombres, ' ', c.apellidos) AS ciudadano_nombre, c.telefono AS ciudadano_telefono,
                       e.nombre AS empresa_nombre,
                       (SELECT COUNT(*) FROM solicitud_materiales x WHERE x.solicitud_id = s.id) AS total_materiales,
                       (SELECT COALESCE(SUM(CASE WHEN x.unidad = 'kg' THEN x.cantidad ELSE 0 END), 0)
                          FROM solicitud_materiales x WHERE x.solicitud_id = s.id) AS total_kg
                FROM solicitudes s
                JOIN usuarios c ON c.id = s.ciudadano_id
                LEFT JOIN empresas e ON e.id = s.empresa_id
                WHERE $where
                ORDER BY FIELD(s.estado, 'EN_RUTA','PROGRAMADA','ACEPTADA','EN_REVISION','REGISTRADA','RECOLECTADA','CANCELADA'),
                         s.fecha_disponible ASC, s.id DESC
                LIMIT {$pg['limite']} OFFSET {$pg['offset']}";
        $st = db()->prepare($sql);
        $st->execute($p);
        return ['filas' => $st->fetchAll(), 'pag' => $pg];
    }

    public static function cambiarEstado(int $id, string $nuevo, ?int $usuarioId, string $obs = ''): void
    {
        $s = self::obtener($id);
        if (!$s) {
            return;
        }
        $anterior = $s['estado'];
        $extra = match ($nuevo) {
            'ACEPTADA'    => ', fecha_aceptacion = NOW()',
            'RECOLECTADA' => '',
            default       => '',
        };
        db()->prepare("UPDATE solicitudes SET estado = ?{$extra} WHERE id = ?")->execute([$nuevo, $id]);
        registrarHistorial($id, $usuarioId, $anterior, $nuevo, $obs);

        if ($nuevo === 'CANCELADA' && $usuarioId && $usuarioId !== (int) $s['ciudadano_id']) {
            notificar((int) $s['ciudadano_id'], 'Solicitud cancelada',
                "La solicitud {$s['numero']} fue cancelada.", 'mi_solicitud', ['id' => $id]);
        }
        if ($nuevo === 'EN_RUTA') {
            notificar((int) $s['ciudadano_id'], 'Operador en ruta',
                "La empresa está en camino para la solicitud {$s['numero']}.", 'mi_solicitud', ['id' => $id]);
        }
    }

    /** RF-12: la empresa acepta la solicitud. */
    public static function aceptar(int $id, int $empresaId, int $usuarioId, string $obs = ''): void
    {
        $s = self::obtener($id);
        if (!$s || !in_array($s['estado'], ['REGISTRADA', 'EN_REVISION'], true) || $s['empresa_id'] !== null) {
            throw new RuntimeException('La solicitud ya no está disponible para aceptar.');
        }
        db()->prepare("UPDATE solicitudes SET estado = 'ACEPTADA', empresa_id = ?, empresa_usuario_id = ?,
                       fecha_aceptacion = NOW() WHERE id = ? AND empresa_id IS NULL")
            ->execute([$empresaId, $usuarioId, $id]);
        registrarHistorial($id, $usuarioId, $s['estado'], 'ACEPTADA',
            $obs !== '' ? $obs : 'Aceptada por la empresa operadora.');
        notificar((int) $s['ciudadano_id'], 'Solicitud aceptada',
            "Una empresa operadora aceptó tu solicitud {$s['numero']}.", 'mi_solicitud', ['id' => $id]);
    }

    /** RF-13: programación de la recolección. */
    public static function programar(int $id, array $d, int $usuarioId): void
    {
        db()->prepare("UPDATE solicitudes SET estado = 'PROGRAMADA', fecha_programada = :fecha,
                hora_programada = :hora, operador = :operador, vehiculo_id = :vehiculo,
                observaciones_empresa = :obs
                WHERE id = :id")
            ->execute([
                ':fecha' => $d['fecha_programada'], ':hora' => $d['hora_programada'],
                ':operador' => $d['operador'] ?: null,
                ':vehiculo' => !empty($d['vehiculo_id']) ? (int) $d['vehiculo_id'] : null,
                ':obs' => $d['observaciones_empresa'] ?: null, ':id' => $id,
            ]);
        $s = self::obtener($id);
        registrarHistorial($id, $usuarioId, 'ACEPTADA', 'PROGRAMADA',
            'Recolección programada para el ' . $d['fecha_programada'] . ' a las ' . substr((string) $d['hora_programada'], 0, 5));
        notificar((int) $s['ciudadano_id'], 'Recolección programada',
            "Tu solicitud {$s['numero']} fue programada para el {$d['fecha_programada']} a las "
            . substr((string) $d['hora_programada'], 0, 5) . '.', 'mi_solicitud', ['id' => $id]);
    }

    // ---------------------------------------------------------- estadísticas
    public static function contarPorEstado(): array
    {
        $r = array_fill_keys(array_keys(estadosSolicitud()), 0);
        foreach (db()->query('SELECT estado, COUNT(*) t FROM solicitudes GROUP BY estado') as $f) {
            $r[$f['estado']] = (int) $f['t'];
        }
        return $r;
    }

    public static function total(): int
    {
        return (int) db()->query('SELECT COUNT(*) FROM solicitudes')->fetchColumn();
    }

    public static function proximasProgramadas(int $limite = 5): array
    {
        $sql = "SELECT s.id, s.numero, s.fecha_programada, s.hora_programada, s.barrio, s.ciudad, s.operador,
                       CONCAT(c.nombres,' ',c.apellidos) AS ciudadano_nombre, e.nombre AS empresa_nombre
                FROM solicitudes s
                JOIN usuarios c ON c.id = s.ciudadano_id
                LEFT JOIN empresas e ON e.id = s.empresa_id
                WHERE s.estado IN ('PROGRAMADA','EN_RUTA')
                ORDER BY s.fecha_programada ASC, s.hora_programada ASC LIMIT " . (int) $limite;
        return db()->query($sql)->fetchAll();
    }

    public static function ciudades(): array
    {
        return db()->query('SELECT DISTINCT ciudad FROM solicitudes ORDER BY ciudad')->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function barrios(?string $ciudad = null): array
    {
        if ($ciudad) {
            $st = db()->prepare('SELECT DISTINCT barrio FROM solicitudes WHERE ciudad = ? ORDER BY barrio');
            $st->execute([$ciudad]);
            return $st->fetchAll(PDO::FETCH_COLUMN);
        }
        return db()->query('SELECT DISTINCT barrio FROM solicitudes ORDER BY barrio')->fetchAll(PDO::FETCH_COLUMN);
    }

    /** Ciudades donde ya hay solicitudes o empresas: se usan para sugerir al ciudadano. */
    public static function ciudadesSugeridas(): array
    {
        $sql = 'SELECT DISTINCT ciudad FROM (
                    SELECT ciudad FROM empresas WHERE ciudad IS NOT NULL
                    UNION SELECT ciudad FROM solicitudes WHERE ciudad IS NOT NULL) t ORDER BY ciudad';
        return db()->query($sql)->fetchAll(PDO::FETCH_COLUMN);
    }
}
