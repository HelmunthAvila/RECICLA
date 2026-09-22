<?php
/**
 * RECICLA+ | Modelo Empresa operadora (RF-18) y vehículos (RF-13)
 */
declare(strict_types=1);

final class Empresa
{
    public static function listar(array $f = [], int $pagina = 1, int $porPagina = 15): array
    {
        $w = ['1 = 1'];
        $p = [];
        if (!empty($f['estado'])) { $w[] = 'e.estado = ?'; $p[] = $f['estado']; }
        if (!empty($f['ciudad'])) { $w[] = 'e.ciudad = ?'; $p[] = $f['ciudad']; }
        if (!empty($f['q'])) {
            $w[] = '(e.nombre LIKE ? OR e.nit LIKE ? OR e.responsable LIKE ?)';
            $t = '%' . $f['q'] . '%';
            array_push($p, $t, $t, $t);
        }
        $where = implode(' AND ', $w);

        $st = db()->prepare("SELECT COUNT(*) FROM empresas e WHERE $where");
        $st->execute($p);
        $pg = paginar((int) $st->fetchColumn(), $porPagina, $pagina);

        $st = db()->prepare("SELECT e.*,
                (SELECT COUNT(*) FROM solicitudes s WHERE s.empresa_id = e.id) AS total_solicitudes,
                (SELECT COUNT(*) FROM vehiculos v WHERE v.empresa_id = e.id AND v.estado = 'activo') AS total_vehiculos
            FROM empresas e WHERE $where ORDER BY e.nombre LIMIT {$pg['limite']} OFFSET {$pg['offset']}");
        $st->execute($p);
        return ['filas' => $st->fetchAll(), 'pag' => $pg];
    }

    public static function obtener(int $id): ?array
    {
        $st = db()->prepare('SELECT * FROM empresas WHERE id = ?');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public static function guardar(array $d, ?int $id = null): int
    {
        if ($id) {
            db()->prepare('UPDATE empresas SET nombre = :nom, nit = :nit, responsable = :res, telefono = :tel,
                    email = :ema, direccion = :dir, ciudad = :ciu WHERE id = :id')
                ->execute([
                    ':nom' => $d['nombre'], ':nit' => $d['nit'], ':res' => $d['responsable'],
                    ':tel' => $d['telefono'] ?: null, ':ema' => $d['email'] ?: null,
                    ':dir' => $d['direccion'] ?: null, ':ciu' => $d['ciudad'], ':id' => $id,
                ]);
            return $id;
        }
        db()->prepare('INSERT INTO empresas (nombre, nit, responsable, telefono, email, direccion, ciudad)
                VALUES (:nom, :nit, :res, :tel, :ema, :dir, :ciu)')
            ->execute([
                ':nom' => $d['nombre'], ':nit' => $d['nit'], ':res' => $d['responsable'],
                ':tel' => $d['telefono'] ?: null, ':ema' => $d['email'] ?: null,
                ':dir' => $d['direccion'] ?: null, ':ciu' => $d['ciudad'],
            ]);
        return (int) db()->lastInsertId();
    }

    public static function nitExiste(string $nit, int $excepto = 0): bool
    {
        $st = db()->prepare('SELECT COUNT(*) FROM empresas WHERE nit = ? AND id <> ?');
        $st->execute([$nit, $excepto]);
        return (int) $st->fetchColumn() > 0;
    }

    public static function cambiarEstado(int $id, string $estado): void
    {
        db()->prepare('UPDATE empresas SET estado = ? WHERE id = ?')
            ->execute([$estado === 'activo' ? 'activo' : 'inactivo', $id]);
    }

    public static function activas(): array
    {
        return db()->query('SELECT id, nombre, ciudad FROM empresas WHERE estado = \'activo\' ORDER BY nombre')->fetchAll();
    }

    public static function contar(bool $soloActivas = false): int
    {
        $sql = 'SELECT COUNT(*) FROM empresas' . ($soloActivas ? ' WHERE estado = \'activo\'' : '');
        return (int) db()->query($sql)->fetchColumn();
    }

    public static function ciudades(): array
    {
        return db()->query('SELECT DISTINCT ciudad FROM empresas WHERE ciudad IS NOT NULL ORDER BY ciudad')->fetchAll(PDO::FETCH_COLUMN);
    }

    // ---------------------------------------------------------- vehículos
    public static function vehiculos(int $empresaId, bool $soloActivos = true): array
    {
        $sql = 'SELECT * FROM vehiculos WHERE empresa_id = ?' . ($soloActivos ? " AND estado = 'activo'" : '') . ' ORDER BY placa';
        $st = db()->prepare($sql);
        $st->execute([$empresaId]);
        return $st->fetchAll();
    }

    public static function guardarVehiculo(array $d, ?int $id = null): void
    {
        if ($id) {
            db()->prepare('UPDATE vehiculos SET placa = ?, tipo = ?, capacidad = ? WHERE id = ?')
                ->execute([$d['placa'], $d['tipo'], $d['capacidad'] ?: null, $id]);
            return;
        }
        db()->prepare('INSERT INTO vehiculos (empresa_id, placa, tipo, capacidad) VALUES (?, ?, ?, ?)')
            ->execute([(int) $d['empresa_id'], $d['placa'], $d['tipo'], $d['capacidad'] ?: null]);
    }

    public static function estadoVehiculo(int $id, string $estado): void
    {
        db()->prepare('UPDATE vehiculos SET estado = ? WHERE id = ?')
            ->execute([$estado === 'activo' ? 'activo' : 'inactivo', $id]);
    }
}
