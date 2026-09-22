<?php
/**
 * RECICLA+ | Modelo Usuario (ciudadano, empresa, administrador)
 */
declare(strict_types=1);

final class Usuario
{
    /** Registro de ciudadano (RF-01). */
    public static function crearCiudadano(array $d): int
    {
        $st = db()->prepare('INSERT INTO usuarios
            (rol_id, nombres, apellidos, documento, telefono, email, password, direccion, barrio, ciudad, estado)
            VALUES (1, :nombres, :apellidos, :documento, :telefono, :email, :password, :direccion, :barrio, :ciudad, \'activo\')');
        $st->execute([
            ':nombres'   => $d['nombres'],
            ':apellidos' => $d['apellidos'],
            ':documento' => $d['documento'],
            ':telefono'  => $d['telefono'] ?: null,
            ':email'     => mb_strtolower($d['email']),
            ':password'  => password_hash($d['password'], PASSWORD_BCRYPT),
            ':direccion' => $d['direccion'] ?: null,
            ':barrio'    => $d['barrio'] ?: null,
            ':ciudad'    => $d['ciudad'] ?: null,
        ]);
        return (int) db()->lastInsertId();
    }

    public static function buscarPorEmail(string $email): ?array
    {
        $st = db()->prepare('SELECT u.*, r.nombre AS rol, e.nombre AS empresa, e.id AS empresa_ref
                             FROM usuarios u
                             JOIN roles r ON r.id = u.rol_id
                             LEFT JOIN empresas e ON e.id = u.empresa_id
                             WHERE u.email = ? LIMIT 1');
        $st->execute([mb_strtolower(trim($email))]);
        return $st->fetch() ?: null;
    }

    public static function buscarPorId(int $id): ?array
    {
        $st = db()->prepare('SELECT u.*, r.nombre AS rol, e.nombre AS empresa
                             FROM usuarios u
                             JOIN roles r ON r.id = u.rol_id
                             LEFT JOIN empresas e ON e.id = u.empresa_id
                             WHERE u.id = ? LIMIT 1');
        $st->execute([$id]);
        $u = $st->fetch() ?: null;
        if ($u) {
            unset($u['password'], $u['token_recuperacion']);
        }
        return $u;
    }

    public static function emailRegistrado(string $email, int $exceptoId = 0): bool
    {
        $st = db()->prepare('SELECT COUNT(*) FROM usuarios WHERE email = ? AND id <> ?');
        $st->execute([mb_strtolower(trim($email)), $exceptoId]);
        return (int) $st->fetchColumn() > 0;
    }

    public static function documentoRegistrado(string $documento, int $exceptoId = 0): bool
    {
        $st = db()->prepare('SELECT COUNT(*) FROM usuarios WHERE documento = ? AND id <> ?');
        $st->execute([trim($documento), $exceptoId]);
        return (int) $st->fetchColumn() > 0;
    }

    /** Listado administrativo con filtros y paginación (RF-16). */
    public static function listar(array $f = [], int $pagina = 1, int $porPagina = 15): array
    {
        $w = ['1 = 1'];
        $p = [];
        if (!empty($f['rol']))    { $w[] = 'r.nombre = ?';        $p[] = $f['rol']; }
        if (!empty($f['estado'])) { $w[] = 'u.estado = ?';        $p[] = $f['estado']; }
        if (!empty($f['q'])) {
            $w[] = '(u.nombres LIKE ? OR u.apellidos LIKE ? OR u.documento LIKE ? OR u.email LIKE ?)';
            $t = '%' . $f['q'] . '%';
            array_push($p, $t, $t, $t, $t);
        }
        $where = implode(' AND ', $w);

        $st = db()->prepare("SELECT COUNT(*) FROM usuarios u JOIN roles r ON r.id = u.rol_id WHERE $where");
        $st->execute($p);
        $pg = paginar((int) $st->fetchColumn(), $porPagina, $pagina);

        $sql = "SELECT u.id, u.nombres, u.apellidos, u.documento, u.telefono, u.email, u.ciudad, u.barrio,
                       u.estado, u.created_at, u.ultimo_acceso, r.nombre AS rol, e.nombre AS empresa
                FROM usuarios u
                JOIN roles r ON r.id = u.rol_id
                LEFT JOIN empresas e ON e.id = u.empresa_id
                WHERE $where
                ORDER BY u.id DESC LIMIT {$pg['limite']} OFFSET {$pg['offset']}";
        $st = db()->prepare($sql);
        $st->execute($p);
        return ['filas' => $st->fetchAll(), 'pag' => $pg];
    }

    public static function actualizar(int $id, array $d): void
    {
        $st = db()->prepare('UPDATE usuarios SET nombres = :nombres, apellidos = :apellidos, documento = :documento,
            telefono = :telefono, email = :email, direccion = :direccion, barrio = :barrio, ciudad = :ciudad,
            rol_id = :rol_id, empresa_id = :empresa_id WHERE id = :id');
        $st->execute([
            ':nombres' => $d['nombres'], ':apellidos' => $d['apellidos'], ':documento' => $d['documento'],
            ':telefono' => $d['telefono'] ?: null, ':email' => mb_strtolower($d['email']),
            ':direccion' => $d['direccion'] ?: null, ':barrio' => $d['barrio'] ?: null,
            ':ciudad' => $d['ciudad'] ?: null, ':rol_id' => (int) $d['rol_id'],
            ':empresa_id' => !empty($d['empresa_id']) ? (int) $d['empresa_id'] : null,
            ':id' => $id,
        ]);
    }

    public static function actualizarPerfil(int $id, array $d): void
    {
        $st = db()->prepare('UPDATE usuarios SET nombres = :nombres, apellidos = :apellidos, telefono = :telefono,
            direccion = :direccion, barrio = :barrio, ciudad = :ciudad WHERE id = :id');
        $st->execute([
            ':nombres' => $d['nombres'], ':apellidos' => $d['apellidos'],
            ':telefono' => $d['telefono'] ?: null, ':direccion' => $d['direccion'] ?: null,
            ':barrio' => $d['barrio'] ?: null, ':ciudad' => $d['ciudad'] ?: null, ':id' => $id,
        ]);
    }

    public static function cambiarEstado(int $id, string $estado): void
    {
        db()->prepare('UPDATE usuarios SET estado = ? WHERE id = ?')
            ->execute([$estado === 'activo' ? 'activo' : 'inactivo', $id]);
    }

    public static function cambiarPassword(int $id, string $nueva): void
    {
        db()->prepare('UPDATE usuarios SET password = ?, token_recuperacion = NULL, token_expira = NULL WHERE id = ?')
            ->execute([password_hash($nueva, PASSWORD_BCRYPT), $id]);
    }

    public static function crearPorAdmin(array $d): int
    {
        $st = db()->prepare('INSERT INTO usuarios
            (rol_id, empresa_id, nombres, apellidos, documento, telefono, email, password, direccion, barrio, ciudad)
            VALUES (:rol_id, :empresa_id, :nombres, :apellidos, :documento, :telefono, :email, :password, :direccion, :barrio, :ciudad)');
        $st->execute([
            ':rol_id' => (int) $d['rol_id'],
            ':empresa_id' => !empty($d['empresa_id']) ? (int) $d['empresa_id'] : null,
            ':nombres' => $d['nombres'], ':apellidos' => $d['apellidos'], ':documento' => $d['documento'],
            ':telefono' => $d['telefono'] ?: null, ':email' => mb_strtolower($d['email']),
            ':password' => password_hash($d['password'] ?: 'Recicla123*', PASSWORD_BCRYPT),
            ':direccion' => $d['direccion'] ?: null, ':barrio' => $d['barrio'] ?: null,
            ':ciudad' => $d['ciudad'] ?: null,
        ]);
        return (int) db()->lastInsertId();
    }

    // -------- recuperación de contraseña (RF-03)
    public static function guardarToken(int $id, string $token): void
    {
        db()->prepare('UPDATE usuarios SET token_recuperacion = ?, token_expira = DATE_ADD(NOW(), INTERVAL 60 MINUTE)
                       WHERE id = ?')->execute([$token, $id]);
    }

    public static function buscarPorToken(string $token): ?array
    {
        $st = db()->prepare('SELECT * FROM usuarios WHERE token_recuperacion = ? AND token_expira > NOW() LIMIT 1');
        $st->execute([$token]);
        return $st->fetch() ?: null;
    }

    public static function usuariosDeEmpresa(int $empresaId): array
    {
        $st = db()->prepare('SELECT u.id, u.nombres, u.apellidos, u.documento, u.email, u.telefono, u.estado
                             FROM usuarios u WHERE u.empresa_id = ? AND u.rol_id = 2 ORDER BY u.nombres');
        $st->execute([$empresaId]);
        return $st->fetchAll();
    }

    public static function contar(?string $rol = null): int
    {
        if ($rol) {
            $st = db()->prepare('SELECT COUNT(*) FROM usuarios u JOIN roles r ON r.id = u.rol_id WHERE r.nombre = ?');
            $st->execute([$rol]);
            return (int) $st->fetchColumn();
        }
        return (int) db()->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
    }

    public static function roles(): array
    {
        return db()->query('SELECT id, nombre, descripcion FROM roles ORDER BY id')->fetchAll();
    }
}
