<?php
/**
 * RECICLA+ | Reportes y estadísticas (RF-20, RF-21)
 * Todos los reportes se construyen con consultas parametrizadas y agregaciones en MySQL.
 */
declare(strict_types=1);

final class Reporte
{
    private static function rango(array $f): array
    {
        $desde = !empty($f['desde']) ? $f['desde'] : date('Y-m-01');
        $hasta = !empty($f['hasta']) ? $f['hasta'] : date('Y-m-d');
        return [$desde, $hasta];
    }

    /** Indicadores del dashboard (RF-20). */
    public static function resumen(): array
    {
        $pdo = db();
        $estados = Solicitud::contarPorEstado();
        return [
            'usuarios'              => Usuario::contar(),
            'ciudadanos'            => Usuario::contar('ciudadano'),
            'empresas'              => Empresa::contar(),
            'empresas_activas'      => Empresa::contar(true),
            'materiales'            => Material::contar(true),
            'categorias'            => Material::totalCategorias(),
            'solicitudes'           => Solicitud::total(),
            'registradas'           => $estados['REGISTRADA'] + $estados['EN_REVISION'],
            'aceptadas'             => $estados['ACEPTADA'],
            'programadas'           => $estados['PROGRAMADA'] + $estados['EN_RUTA'],
            'recolectadas'          => $estados['RECOLECTADA'],
            'canceladas'            => $estados['CANCELADA'],
            'recolecciones'         => Recoleccion::total(),
            'peso_total'            => Recoleccion::pesoTotalKg(),
            'estados'               => $estados,
            'kg_mes'                => self::pesoPeriodo(date('Y-m-01'), date('Y-m-d')),
        ];
    }

    public static function pesoPeriodo(string $desde, string $hasta): float
    {
        $st = db()->prepare('SELECT COALESCE(SUM(peso_total),0) FROM recolecciones WHERE fecha BETWEEN ? AND ?');
        $st->execute([$desde, $hasta]);
        return (float) $st->fetchColumn();
    }

    /** Reporte por fecha (RF-21). */
    public static function porFecha(array $f): array
    {
        [$desde, $hasta] = self::rango($f);
        $w = ['r.fecha BETWEEN ? AND ?'];
        $p = [$desde, $hasta];
        if (!empty($f['empresa_id']))  { $w[] = 'r.empresa_id = ?'; $p[] = (int) $f['empresa_id']; }
        if (!empty($f['ciudad']))      { $w[] = 's.ciudad = ?';     $p[] = $f['ciudad']; }
        if (!empty($f['barrio']))      { $w[] = 's.barrio = ?';     $p[] = $f['barrio']; }
        if (!empty($f['material_id'])) {
            $w[] = 'EXISTS (SELECT 1 FROM recoleccion_materiales x WHERE x.recoleccion_id = r.id AND x.material_id = ?)';
            $p[] = (int) $f['material_id'];
        }
        $where = implode(' AND ', $w);
        $st = db()->prepare("SELECT r.fecha,
                    COUNT(*) AS recolecciones,
                    COALESCE(SUM(r.peso_total),0) AS kg,
                    COUNT(DISTINCT s.ciudadano_id) AS ciudadanos,
                    COUNT(DISTINCT r.empresa_id) AS empresas
                FROM recolecciones r
                JOIN solicitudes s ON s.id = r.solicitud_id
                WHERE $where
                GROUP BY r.fecha ORDER BY r.fecha DESC");
        $st->execute($p);
        return ['filas' => $st->fetchAll(), 'desde' => $desde, 'hasta' => $hasta, 'filtros' => $f];
    }

    /** Reporte por material y cantidad recolectada. */
    public static function porMaterial(array $f): array
    {
        [$desde, $hasta] = self::rango($f);
        $w = ['r.fecha BETWEEN ? AND ?'];
        $p = [$desde, $hasta];
        if (!empty($f['ciudad']))  { $w[] = 's.ciudad = ?'; $p[] = $f['ciudad']; }
        if (!empty($f['barrio']))  { $w[] = 's.barrio = ?'; $p[] = $f['barrio']; }
        if (!empty($f['empresa_id'])) { $w[] = 'r.empresa_id = ?'; $p[] = (int) $f['empresa_id']; }
        if (!empty($f['categoria_id'])) { $w[] = 'm.categoria_id = ?'; $p[] = (int) $f['categoria_id']; }
        $where = implode(' AND ', $w);
        $st = db()->prepare("SELECT m.nombre AS material, c.nombre AS categoria, c.color AS color,
                    rm.unidad,
                    COUNT(DISTINCT r.id) AS recolecciones,
                    SUM(rm.cantidad) AS cantidad_total,
                    SUM(CASE WHEN rm.unidad = 'kg' THEN rm.cantidad ELSE 0 END) AS kg
                FROM recoleccion_materiales rm
                JOIN recolecciones r ON r.id = rm.recoleccion_id
                JOIN materiales m ON m.id = rm.material_id
                JOIN categorias c ON c.id = m.categoria_id
                JOIN solicitudes s ON s.id = r.solicitud_id
                WHERE $where
                GROUP BY m.id, m.nombre, c.nombre, c.color, rm.unidad
                ORDER BY kg DESC, cantidad_total DESC");
        $st->execute($p);
        return ['filas' => $st->fetchAll(), 'desde' => $desde, 'hasta' => $hasta, 'filtros' => $f];
    }

    /** Reporte por ciudad / barrio. */
    public static function porZona(array $f): array
    {
        [$desde, $hasta] = self::rango($f);
        $w = ['s.created_at BETWEEN ? AND ?'];
        $p = [$desde . ' 00:00:00', $hasta . ' 23:59:59'];
        if (!empty($f['ciudad'])) { $w[] = 's.ciudad = ?'; $p[] = $f['ciudad']; }
        $where = implode(' AND ', $w);
        $st = db()->prepare("SELECT s.ciudad, s.barrio,
                    COUNT(*) AS solicitudes,
                    SUM(s.estado = 'RECOLECTADA') AS recolectadas,
                    SUM(s.estado = 'CANCELADA') AS canceladas,
                    SUM(s.estado IN ('REGISTRADA','EN_REVISION')) AS pendientes,
                    (SELECT COALESCE(SUM(r.peso_total),0) FROM recolecciones r
                        JOIN solicitudes s2 ON s2.id = r.solicitud_id
                        WHERE s2.ciudad = s.ciudad AND s2.barrio = s.barrio) AS kg
                FROM solicitudes s
                WHERE $where
                GROUP BY s.ciudad, s.barrio
                ORDER BY solicitudes DESC");
        $st->execute($p);
        return ['filas' => $st->fetchAll(), 'desde' => $desde, 'hasta' => $hasta, 'filtros' => $f];
    }

    /** Reporte por empresa operadora. */
    public static function porEmpresa(array $f): array
    {
        [$desde, $hasta] = self::rango($f);
        $st = db()->prepare("SELECT e.nombre AS empresa, e.nit, e.ciudad, e.estado,
                    (SELECT COUNT(*) FROM solicitudes s WHERE s.empresa_id = e.id) AS solicitudes,
                    (SELECT COUNT(*) FROM solicitudes s WHERE s.empresa_id = e.id AND s.estado = 'RECOLECTADA') AS recolectadas,
                    (SELECT COUNT(*) FROM solicitudes s WHERE s.empresa_id = e.id AND s.estado = 'PROGRAMADA') AS programadas,
                    (SELECT COALESCE(SUM(r.peso_total),0) FROM recolecciones r
                        WHERE r.empresa_id = e.id AND r.fecha BETWEEN ? AND ?) AS kg
                FROM empresas e ORDER BY kg DESC, solicitudes DESC");
        $st->execute([$desde, $hasta]);
        return ['filas' => $st->fetchAll(), 'desde' => $desde, 'hasta' => $hasta, 'filtros' => $f];
    }

    /** Reporte por estado. */
    public static function porEstado(array $f): array
    {
        [$desde, $hasta] = self::rango($f);
        $w = ['s.created_at BETWEEN ? AND ?'];
        $p = [$desde . ' 00:00:00', $hasta . ' 23:59:59'];
        if (!empty($f['ciudad'])) { $w[] = 's.ciudad = ?'; $p[] = $f['ciudad']; }
        if (!empty($f['empresa_id'])) { $w[] = 's.empresa_id = ?'; $p[] = (int) $f['empresa_id']; }
        $where = implode(' AND ', $w);
        $st = db()->prepare("SELECT s.estado, COUNT(*) AS total FROM solicitudes s WHERE $where GROUP BY s.estado");
        $st->execute($p);
        $conteo = array_fill_keys(array_keys(estadosSolicitud()), 0);
        foreach ($st->fetchAll() as $fila) {
            $conteo[$fila['estado']] = (int) $fila['total'];
        }
        return ['filas' => $conteo, 'desde' => $desde, 'hasta' => $hasta, 'filtros' => $f];
    }

    /** Últimos movimientos (auditoría rápida en el dashboard). */
    public static function ultimosMovimientos(int $limite = 8): array
    {
        $sql = "SELECT h.created_at, h.estado_anterior, h.estado_nuevo, h.observacion,
                       s.numero, CONCAT(u.nombres,' ',u.apellidos) AS usuario, r.nombre AS rol
                FROM historial_solicitudes h
                JOIN solicitudes s ON s.id = h.solicitud_id
                LEFT JOIN usuarios u ON u.id = h.usuario_id
                LEFT JOIN roles r ON r.id = u.rol_id
                ORDER BY h.id DESC LIMIT " . (int) $limite;
        return db()->query($sql)->fetchAll();
    }

    /** Serie mensual de recolecciones (kg) para la gráfica del dashboard. */
    public static function serieMensual(int $meses = 6): array
    {
        $sql = "SELECT DATE_FORMAT(fecha, '%Y-%m') AS mes, COALESCE(SUM(peso_total),0) AS kg, COUNT(*) AS total
                FROM recolecciones
                WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL " . (int) $meses . " MONTH)
                GROUP BY mes ORDER BY mes";
        $datos = [];
        foreach (db()->query($sql) as $f) {
            $datos[$f['mes']] = ['kg' => (float) $f['kg'], 'total' => (int) $f['total']];
        }
        $serie = [];
        for ($i = $meses - 1; $i >= 0; $i--) {
            $clave = date('Y-m', strtotime("-$i month"));
            $serie[] = [
                'mes'   => $clave,
                'label' => date('M', strtotime($clave . '-01')),
                'kg'    => $datos[$clave]['kg'] ?? 0.0,
                'total' => $datos[$clave]['total'] ?? 0,
            ];
        }
        return $serie;
    }
}
