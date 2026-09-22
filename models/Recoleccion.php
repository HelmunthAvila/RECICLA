<?php
/**
 * RECICLA+ | Modelo Recolección (RF-14) y Rutas (RF-15)
 */
declare(strict_types=1);

final class Recoleccion
{
    /**
     * Registra la recolección efectivamente realizada y pasa la solicitud a RECOLECTADA.
     * @param array $cantidades [material_id => cantidad_recolectada]
     */
    public static function registrar(int $solicitudId, int $empresaId, int $usuarioId, array $d, array $cantidades): int
    {
        $pdo = db();
        $puntosOtorgados = 0;
        $pdo->beginTransaction();
        try {
            $st = $pdo->prepare('INSERT INTO recolecciones
                (solicitud_id, empresa_id, usuario_id, vehiculo_id, ruta_id, fecha, hora, observaciones, evidencia, peso_total)
                VALUES (:sol, :emp, :usu, :veh, :rut, :fec, :hor, :obs, :evi, :peso)');
            $st->execute([
                ':sol' => $solicitudId, ':emp' => $empresaId, ':usu' => $usuarioId,
                ':veh' => !empty($d['vehiculo_id']) ? (int) $d['vehiculo_id'] : null,
                ':rut' => !empty($d['ruta_id']) ? (int) $d['ruta_id'] : null,
                ':fec' => $d['fecha'], ':hor' => $d['hora'],
                ':obs' => $d['observaciones'] ?: null, ':evi' => $d['evidencia'] ?? null,
                ':peso' => $d['peso_total'] !== '' ? (float) $d['peso_total'] : null,
            ]);
            $recId = (int) $pdo->lastInsertId();

            $det = $pdo->prepare('INSERT INTO recoleccion_materiales (recoleccion_id, material_id, cantidad, unidad)
                                  VALUES (?, ?, ?, ?)');
            $total = 0.0;
            foreach ($cantidades as $materialId => $info) {
                $cantidad = (float) ($info['cantidad'] ?? 0);
                if ($cantidad <= 0) {
                    continue;
                }
                $det->execute([$recId, (int) $materialId, $cantidad, $info['unidad'] ?? 'kg']);
                if (($info['unidad'] ?? 'kg') === 'kg') {
                    $total += $cantidad;
                }
            }
            if (empty($d['peso_total']) && $total > 0) {
                $pdo->prepare('UPDATE recolecciones SET peso_total = ? WHERE id = ?')->execute([$total, $recId]);
            }

            $pdo->prepare("UPDATE solicitudes SET estado = 'RECOLECTADA',
                    operador = COALESCE(:ope, operador) WHERE id = :id")
                ->execute([':ope' => $d['operador'] ?: null, ':id' => $solicitudId]);

            $s = Solicitud::obtener($solicitudId);
            $pdo->prepare('INSERT INTO historial_solicitudes (solicitud_id, usuario_id, estado_anterior, estado_nuevo, observacion)
                           VALUES (?, ?, ?, ?, ?)')
                ->execute([$solicitudId, $usuarioId, $s['estado'] === 'RECOLECTADA' ? 'EN_RUTA' : $s['estado'],
                           'RECOLECTADA', 'Recolección registrada por la empresa operadora.']);

            // Programa de incentivos: el ciudadano gana puntos por el material recolectado
            $puntosOtorgados = Puntos::otorgarPorRecoleccion($recId, (int) $s['ciudadano_id'], $solicitudId);
            $pdo->commit();

            notificar((int) $s['ciudadano_id'], 'Material recolectado',
                "¡Gracias! Tu solicitud {$s['numero']} fue recolectada. Ganaste {$puntosOtorgados} puntos RECICLA+.",
                'mi_solicitud', ['id' => $solicitudId]);
            if ($puntosOtorgados > 0) {
                notificar((int) $s['ciudadano_id'], '¡Ganaste puntos!',
                    "Acumulaste {$puntosOtorgados} puntos con la recolección {$s['numero']}. Ya puedes cambiarlos por premios.",
                    'premios');
            }
            return $recId;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function porSolicitud(int $solicitudId): ?array
    {
        $st = db()->prepare('SELECT r.*, e.nombre AS empresa_nombre, u.nombres, u.apellidos, v.placa, ru.nombre AS ruta_nombre
                             FROM recolecciones r
                             JOIN empresas e ON e.id = r.empresa_id
                             JOIN usuarios u ON u.id = r.usuario_id
                             LEFT JOIN vehiculos v ON v.id = r.vehiculo_id
                             LEFT JOIN rutas ru ON ru.id = r.ruta_id
                             WHERE r.solicitud_id = ? LIMIT 1');
        $st->execute([$solicitudId]);
        return $st->fetch() ?: null;
    }

    public static function materiales(int $recoleccionId): array
    {
        $st = db()->prepare('SELECT rm.*, m.nombre AS material, c.nombre AS categoria, c.color AS categoria_color
                             FROM recoleccion_materiales rm
                             JOIN materiales m ON m.id = rm.material_id
                             JOIN categorias c ON c.id = m.categoria_id
                             WHERE rm.recoleccion_id = ? ORDER BY m.nombre');
        $st->execute([$recoleccionId]);
        return $st->fetchAll();
    }

    public static function listar(array $f = [], int $pagina = 1, int $porPagina = 12): array
    {
        $w = ['1 = 1'];
        $p = [];
        if (!empty($f['empresa_id'])) { $w[] = 'r.empresa_id = ?'; $p[] = (int) $f['empresa_id']; }
        if (!empty($f['ciudad']))     { $w[] = 's.ciudad = ?';     $p[] = $f['ciudad']; }
        if (!empty($f['barrio']))     { $w[] = 's.barrio = ?';     $p[] = $f['barrio']; }
        if (!empty($f['desde']))      { $w[] = 'r.fecha >= ?';     $p[] = $f['desde']; }
        if (!empty($f['hasta']))      { $w[] = 'r.fecha <= ?';     $p[] = $f['hasta']; }
        if (!empty($f['material_id'])) {
            $w[] = 'EXISTS (SELECT 1 FROM recoleccion_materiales x WHERE x.recoleccion_id = r.id AND x.material_id = ?)';
            $p[] = (int) $f['material_id'];
        }
        if (!empty($f['q'])) {
            $w[] = '(s.numero LIKE ? OR s.barrio LIKE ? OR e.nombre LIKE ?)';
            $t = '%' . $f['q'] . '%';
            array_push($p, $t, $t, $t);
        }
        $where = implode(' AND ', $w);

        $base = "FROM recolecciones r
                 JOIN solicitudes s ON s.id = r.solicitud_id
                 JOIN usuarios c ON c.id = s.ciudadano_id
                 JOIN empresas e ON e.id = r.empresa_id
                 WHERE $where";
        $st = db()->prepare("SELECT COUNT(*) $base");
        $st->execute($p);
        $pg = paginar((int) $st->fetchColumn(), $porPagina, $pagina);

        $st = db()->prepare("SELECT r.*, s.numero, s.barrio, s.ciudad, s.direccion, s.operador,
                    CONCAT(c.nombres,' ',c.apellidos) AS ciudadano_nombre,
                    e.nombre AS empresa_nombre,
                    (SELECT COUNT(*) FROM recoleccion_materiales x WHERE x.recoleccion_id = r.id) AS total_materiales
                $base ORDER BY r.fecha DESC, r.hora DESC
                LIMIT {$pg['limite']} OFFSET {$pg['offset']}");
        $st->execute($p);
        return ['filas' => $st->fetchAll(), 'pag' => $pg];
    }

    public static function total(): int
    {
        return (int) db()->query('SELECT COUNT(*) FROM recolecciones')->fetchColumn();
    }

    public static function pesoTotalKg(): float
    {
        return (float) db()->query('SELECT COALESCE(SUM(peso_total),0) FROM recolecciones')->fetchColumn();
    }

    // ---------------------------------------------------------- rutas (RF-15)
    public static function rutas(int $empresaId, ?string $fecha = null): array
    {
        $sql = "SELECT r.*,
                   (SELECT COUNT(*) FROM solicitudes s WHERE s.ruta_id = r.id) AS total_solicitudes,
                   (SELECT COUNT(*) FROM solicitudes s WHERE s.ruta_id = r.id AND s.estado = 'RECOLECTADA') AS recolectadas
                FROM rutas r WHERE r.empresa_id = ?";
        $p = [$empresaId];
        if ($fecha) {
            $sql .= ' AND r.fecha = ?';
            $p[] = $fecha;
        }
        $sql .= ' ORDER BY r.fecha DESC, r.nombre';
        $st = db()->prepare($sql);
        $st->execute($p);
        return $st->fetchAll();
    }

    public static function crearRuta(array $d, int $empresaId): int
    {
        db()->prepare('INSERT INTO rutas (empresa_id, nombre, zona, ciudad, fecha) VALUES (?, ?, ?, ?, ?)')
            ->execute([$empresaId, $d['nombre'], $d['zona'] ?: null, $d['ciudad'], $d['fecha']]);
        return (int) db()->lastInsertId();
    }

    public static function agruparPorZona(int $empresaId): array
    {
        $sql = "SELECT s.barrio, s.ciudad, s.fecha_programada,
                       COUNT(*) AS total,
                       SUM(s.estado = 'RECOLECTADA') AS recolectadas
                FROM solicitudes s
                WHERE s.empresa_id = ? AND s.estado IN ('PROGRAMADA','EN_RUTA','RECOLECTADA')
                GROUP BY s.ciudad, s.barrio, s.fecha_programada
                ORDER BY s.fecha_programada, s.ciudad, s.barrio";
        $st = db()->prepare($sql);
        $st->execute([$empresaId]);
        return $st->fetchAll();
    }

    public static function asignarARuta(int $solicitudId, ?int $rutaId, int $usuarioId): void
    {
        db()->prepare('UPDATE solicitudes SET ruta_id = ? WHERE id = ?')->execute([$rutaId, $solicitudId]);
        registrarHistorial($solicitudId, $usuarioId, null, 'EN_RUTA',
            $rutaId ? 'Solicitud asignada a una ruta de recolección.' : 'Solicitud retirada de la ruta.');
    }
}
