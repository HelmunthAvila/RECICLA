<?php /** RECICLA+ | Gestión de usuarios (RF-16) */ ?>
<div class="d-flex justify-content-between align-items-center mb-2">
  <h1 class="h5 fw-bold mb-0"><i class="fa-solid fa-users texto-verde me-2"></i>Usuarios</h1>
  <a class="btn btn-sm btn-recicla" href="<?= e(url('a_usuario')) ?>"><i class="fa-solid fa-plus me-1"></i>Nuevo</a>
</div>

<form class="tarjeta-form" method="get" action="<?= e(BASE_URL) ?>/index.php">
  <input type="hidden" name="p" value="a_usuarios">
  <div class="row g-2">
    <div class="col-12 col-md-4">
      <label class="form-label" for="q">Buscar</label>
      <input class="form-control" id="q" name="q" value="<?= e($filtros['q'] ?? '') ?>" placeholder="Nombre, documento o correo">
    </div>
    <div class="col-6 col-md-4">
      <label class="form-label" for="rol">Rol</label>
      <select class="form-select" id="rol" name="rol" data-auto-filtro>
        <option value="">Todos</option>
        <?php foreach ($roles as $rl): ?>
          <option value="<?= e($rl['nombre']) ?>" <?= ($filtros['rol'] ?? '') === $rl['nombre'] ? 'selected' : '' ?>><?= e(ucfirst($rl['nombre'])) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-6 col-md-4">
      <label class="form-label" for="estado">Estado</label>
      <select class="form-select" id="estado" name="estado" data-auto-filtro>
        <option value="">Todos</option>
        <option value="activo" <?= ($filtros['estado'] ?? '') === 'activo' ? 'selected' : '' ?>>Activos</option>
        <option value="inactivo" <?= ($filtros['estado'] ?? '') === 'inactivo' ? 'selected' : '' ?>>Inactivos</option>
      </select>
    </div>
  </div>
  <button class="btn btn-recicla w-100 mt-3" type="submit"><i class="fa-solid fa-filter me-1"></i>Filtrar</button>
</form>

<p class="hint"><?= (int) $res['pag']['total'] ?> usuario(s).</p>

<div class="tarjeta p-2 mb-3">
  <div class="table-responsive">
    <table class="table table-sm table-recicla mb-0">
      <thead>
        <tr><th>Usuario</th><th>Rol</th><th>Contacto</th><th>Estado</th><th></th></tr>
      </thead>
      <tbody>
      <?php foreach ($res['filas'] as $u): ?>
        <tr>
          <td>
            <strong><?= e($u['nombres'] . ' ' . $u['apellidos']) ?></strong>
            <div class="hint">Doc. <?= e($u['documento']) ?><?= $u['empresa'] ? ' · ' . e($u['empresa']) : '' ?></div>
          </td>
          <td><span class="badge bg-verde-claro text-dark"><?= e(ucfirst($u['rol'])) ?></span></td>
          <td>
            <div class="hint"><?= e($u['email']) ?></div>
            <div class="hint"><?= e((string) $u['telefono']) ?> · <?= e((string) $u['ciudad']) ?></div>
          </td>
          <td><span class="badge <?= $u['estado'] === 'activo' ? 'bg-success' : 'bg-secondary' ?>"><?= e($u['estado']) ?></span></td>
          <td class="text-nowrap">
            <a class="btn btn-sm btn-outline-recicla" href="<?= e(url('a_usuario', ['id' => (int) $u['id']])) ?>" title="Editar"><i class="fa-solid fa-pen"></i></a>
            <form class="d-inline" method="post" action="<?= e(url('a_usuario_estado')) ?>"
                  data-confirmar="¿<?= $u['estado'] === 'activo' ? 'Desactivar' : 'Activar' ?> a <?= e($u['nombres']) ?>?">
              <?= csrf_campo() ?>
              <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
              <button class="btn btn-sm btn-outline-<?= $u['estado'] === 'activo' ? 'danger' : 'success' ?>" type="submit" title="<?= $u['estado'] === 'activo' ? 'Desactivar' : 'Activar' ?>">
                <i class="fa-solid <?= $u['estado'] === 'activo' ? 'fa-user-slash' : 'fa-user-check' ?>"></i>
              </button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php if (!$res['filas']): ?><p class="hint text-center mb-0 py-3">No hay usuarios con esos filtros.</p><?php endif; ?>
</div>

<?= paginador($res['pag'], 'a_usuarios', array_filter($filtros)) ?>
