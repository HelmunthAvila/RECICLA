<?php /** RECICLA+ | Gestión de empresas operadoras (RF-18) */ ?>
<div class="d-flex justify-content-between align-items-center mb-2">
  <h1 class="h5 fw-bold mb-0"><i class="fa-solid fa-building texto-verde me-2"></i>Empresas operadoras</h1>
  <a class="btn btn-sm btn-recicla" href="<?= e(url('a_empresa')) ?>"><i class="fa-solid fa-plus me-1"></i>Nueva</a>
</div>

<form class="tarjeta-form" method="get" action="<?= e(BASE_URL) ?>/index.php">
  <input type="hidden" name="p" value="a_empresas">
  <div class="row g-2">
    <div class="col-12 col-md-5">
      <label class="form-label" for="q">Buscar</label>
      <input class="form-control" id="q" name="q" value="<?= e($filtros['q'] ?? '') ?>" placeholder="Nombre, NIT o responsable">
    </div>
    <div class="col-6 col-md-4">
      <label class="form-label" for="ciudad">Ciudad</label>
      <select class="form-select" id="ciudad" name="ciudad" data-auto-filtro>
        <option value="">Todas</option>
        <?php foreach ($ciudades as $c): ?>
          <option value="<?= e($c) ?>" <?= ($filtros['ciudad'] ?? '') === $c ? 'selected' : '' ?>><?= e($c) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-6 col-md-3">
      <label class="form-label" for="estado">Estado</label>
      <select class="form-select" id="estado" name="estado" data-auto-filtro>
        <option value="">Todas</option>
        <option value="activo" <?= ($filtros['estado'] ?? '') === 'activo' ? 'selected' : '' ?>>Activas</option>
        <option value="inactivo" <?= ($filtros['estado'] ?? '') === 'inactivo' ? 'selected' : '' ?>>Inactivas</option>
      </select>
    </div>
  </div>
  <button class="btn btn-recicla w-100 mt-3" type="submit"><i class="fa-solid fa-filter me-1"></i>Filtrar</button>
</form>

<p class="hint"><?= (int) $res['pag']['total'] ?> empresa(s).</p>

<?php foreach ($res['filas'] as $e2): ?>
  <div class="tarjeta p-3 mb-2">
    <div class="d-flex justify-content-between align-items-start">
      <div>
        <div class="fw-bold"><?= e($e2['nombre']) ?></div>
        <div class="hint">NIT <?= e($e2['nit']) ?> · <?= e($e2['ciudad']) ?></div>
        <div class="hint"><i class="fa-solid fa-user me-1"></i><?= e($e2['responsable']) ?> · <i class="fa-solid fa-phone me-1"></i><?= e((string) $e2['telefono']) ?></div>
        <div class="hint"><i class="fa-solid fa-envelope me-1"></i><?= e((string) $e2['email']) ?></div>
      </div>
      <span class="badge <?= $e2['estado'] === 'activo' ? 'bg-success' : 'bg-secondary' ?>"><?= e($e2['estado']) ?></span>
    </div>
    <div class="d-flex gap-2 small mt-2">
      <span class="badge bg-verde-claro text-dark"><?= (int) $e2['total_solicitudes'] ?> solicitudes</span>
      <span class="badge bg-verde-claro text-dark"><?= (int) $e2['total_vehiculos'] ?> vehículos</span>
    </div>
    <div class="d-flex gap-2 mt-3">
      <a class="btn btn-sm btn-outline-recicla flex-fill" href="<?= e(url('a_empresa', ['id' => (int) $e2['id']])) ?>"><i class="fa-solid fa-pen me-1"></i>Editar</a>
      <form class="flex-fill" method="post" action="<?= e(url('a_empresa_estado')) ?>"
            data-confirmar="¿<?= $e2['estado'] === 'activo' ? 'Desactivar' : 'Activar' ?> la empresa <?= e($e2['nombre']) ?>?">
        <?= csrf_campo() ?>
        <input type="hidden" name="id" value="<?= (int) $e2['id'] ?>">
        <button class="btn btn-sm btn-outline-<?= $e2['estado'] === 'activo' ? 'danger' : 'success' ?> w-100" type="submit">
          <i class="fa-solid <?= $e2['estado'] === 'activo' ? 'fa-ban' : 'fa-check' ?> me-1"></i><?= $e2['estado'] === 'activo' ? 'Desactivar' : 'Activar' ?>
        </button>
      </form>
    </div>
  </div>
<?php endforeach; ?>

<?php if (!$res['filas']): ?>
  <div class="tarjeta p-4 text-center"><i class="fa-solid fa-building fa-2x text-muted mb-2"></i><p class="mb-0">No hay empresas con esos filtros.</p></div>
<?php endif; ?>

<?= paginador($res['pag'], 'a_empresas', array_filter($filtros)) ?>
