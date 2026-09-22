<?php
/**
 * RECICLA+ | Modelo Material (categorías y materiales — RF-04, RF-05, RF-17)
 */
declare(strict_types=1);

final class Material
{
    // ---------------------------------------------------------- categorías
    public static function categorias(bool $soloActivas = true): array
    {
        $sql = 'SELECT c.*, (SELECT COUNT(*) FROM materiales m WHERE m.categoria_id = c.id AND m.estado = \'activo\') AS total_materiales
                FROM categorias c';
        if ($soloActivas) {
            $sql .= ' WHERE c.estado = \'activo\'';
        }
        $sql .= ' ORDER BY c.nombre';
        return db()->query($sql)->fetchAll();
    }

    public static function categoria(int $id): ?array
    {
        $st = db()->prepare('SELECT * FROM categorias WHERE id = ?');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public static function guardarCategoria(array $d, ?int $id = null): void
    {
        if ($id) {
            db()->prepare('UPDATE categorias SET nombre = ?, descripcion = ?, icono = ?, color = ? WHERE id = ?')
                ->execute([$d['nombre'], $d['descripcion'] ?: null, $d['icono'] ?: 'fa-recycle', $d['color'] ?: '#2e8800', $id]);
            return;
        }
        db()->prepare('INSERT INTO categorias (nombre, descripcion, icono, color) VALUES (?, ?, ?, ?)')
            ->execute([$d['nombre'], $d['descripcion'] ?: null, $d['icono'] ?: 'fa-recycle', $d['color'] ?: '#2e8800']);
    }

    public static function estadoCategoria(int $id, string $estado): void
    {
        db()->prepare('UPDATE categorias SET estado = ? WHERE id = ?')
            ->execute([$estado === 'activo' ? 'activo' : 'inactivo', $id]);
    }

    public static function categoriaConMateriales(int $id): int
    {
        $st = db()->prepare('SELECT COUNT(*) FROM materiales WHERE categoria_id = ?');
        $st->execute([$id]);
        return (int) $st->fetchColumn();
    }

    // ---------------------------------------------------------- materiales
    public static function listar(array $f = [], int $pagina = 1, int $porPagina = 12): array
    {
        $w = ['1 = 1'];
        $p = [];
        if (!empty($f['categoria_id'])) { $w[] = 'm.categoria_id = ?';  $p[] = (int) $f['categoria_id']; }
        if (!empty($f['estado']))       { $w[] = 'm.estado = ?';        $p[] = $f['estado']; }
        if (!empty($f['q'])) {
            $w[] = '(m.nombre LIKE ? OR m.descripcion LIKE ?)';
            $p[] = '%' . $f['q'] . '%';
            $p[] = '%' . $f['q'] . '%';
        }
        $where = implode(' AND ', $w);

        $st = db()->prepare("SELECT COUNT(*) FROM materiales m WHERE $where");
        $st->execute($p);
        $pg = paginar((int) $st->fetchColumn(), $porPagina, $pagina);

        $st = db()->prepare("SELECT m.*, c.nombre AS categoria, c.icono AS categoria_icono, c.color AS categoria_color
                             FROM materiales m
                             JOIN categorias c ON c.id = m.categoria_id
                             WHERE $where
                             ORDER BY c.nombre, m.nombre
                             LIMIT {$pg['limite']} OFFSET {$pg['offset']}");
        $st->execute($p);
        return ['filas' => $st->fetchAll(), 'pag' => $pg];
    }

    /** Materiales agrupados por categoría (pantalla "Ver materiales", RNF-02). */
    public static function agrupados(array $f = []): array
    {
        $grupos = [];
        foreach (self::categorias(true) as $c) {
            $filtro = $f;
            $filtro['categoria_id'] = (int) $c['id'];
            $filtro['estado'] = 'activo';
            $res = self::listar($filtro, 1, 60);
            if ($res['filas']) {
                $c['materiales'] = $res['filas'];
                $grupos[] = $c;
            }
        }
        return $grupos;
    }

    public static function obtener(int $id): ?array
    {
        $st = db()->prepare('SELECT m.*, c.nombre AS categoria, c.icono AS categoria_icono, c.color AS categoria_color,
                                    c.descripcion AS categoria_descripcion
                             FROM materiales m JOIN categorias c ON c.id = m.categoria_id
                             WHERE m.id = ? LIMIT 1');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public static function nombreExiste(string $nombre, int $excepto = 0): bool
    {
        $st = db()->prepare('SELECT COUNT(*) FROM materiales WHERE nombre = ? AND id <> ?');
        $st->execute([$nombre, $excepto]);
        return (int) $st->fetchColumn() > 0;
    }

    public static function guardar(array $d, ?int $id = null): int
    {
        if ($id) {
            db()->prepare('UPDATE materiales SET categoria_id = :cat, nombre = :nom, descripcion = :des,
                    recomendaciones = :rec, condiciones_entrega = :con, unidad_medida = :uni, icono = :ico,
                    puntos_por_unidad = :pun, imagen = COALESCE(:img, imagen)
                    WHERE id = :id')
                ->execute([
                    ':cat' => (int) $d['categoria_id'], ':nom' => $d['nombre'],
                    ':des' => $d['descripcion'] ?: null, ':rec' => $d['recomendaciones'] ?: null,
                    ':con' => $d['condiciones_entrega'] ?: null,
                    ':uni' => $d['unidad_medida'] ?: 'kg', ':ico' => $d['icono'] ?: 'fa-box',
                    ':pun' => (int) ($d['puntos_por_unidad'] ?? 10),
                    ':img' => $d['imagen'] ?? null, ':id' => $id,
                ]);
            return $id;
        }
        db()->prepare('INSERT INTO materiales (categoria_id, nombre, descripcion, recomendaciones, condiciones_entrega,
                unidad_medida, puntos_por_unidad, icono, imagen)
                VALUES (:cat, :nom, :des, :rec, :con, :uni, :pun, :ico, :img)')
            ->execute([
                ':cat' => (int) $d['categoria_id'], ':nom' => $d['nombre'],
                ':des' => $d['descripcion'] ?: null, ':rec' => $d['recomendaciones'] ?: null,
                ':con' => $d['condiciones_entrega'] ?: null,
                ':uni' => $d['unidad_medida'] ?: 'kg', ':ico' => $d['icono'] ?: 'fa-box',
                ':pun' => (int) ($d['puntos_por_unidad'] ?? 10),
                ':img' => $d['imagen'] ?? null,
            ]);
        return (int) db()->lastInsertId();
    }

    public static function cambiarEstado(int $id, string $estado): void
    {
        db()->prepare('UPDATE materiales SET estado = ? WHERE id = ?')
            ->execute([$estado === 'activo' ? 'activo' : 'inactivo', $id]);
    }

    public static function activos(): array
    {
        return db()->query('SELECT id, nombre, unidad_medida, categoria_id FROM materiales WHERE estado = \'activo\' ORDER BY nombre')
            ->fetchAll();
    }

    public static function contar(bool $soloActivos = false): int
    {
        $sql = 'SELECT COUNT(*) FROM materiales' . ($soloActivos ? ' WHERE estado = \'activo\'' : '');
        return (int) db()->query($sql)->fetchColumn();
    }

    public static function totalCategorias(): int
    {
        return (int) db()->query('SELECT COUNT(*) FROM categorias WHERE estado = \'activo\'')->fetchColumn();
    }
}
