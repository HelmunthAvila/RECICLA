<?php /** RECICLA+ | Gestión de materiales (RF-17) */ ?>
<div class="d-flex justify-content-between align-items-center mb-2">
  <h1 class="h5 fw-bold mb-0"><i class="fa-solid fa-recycle texto-verde me-2"></i>Materiales</h1>
  <a class="btn btn-sm btn-recicla" href="<?= e(url('a_material')) ?>"><i class="fa-solid fa-plus me-1"></i>Nuevo</a>
</div>

<form class="tarjeta-form" method="get" action="<?= e(BASE_URL) ?>/index.php">
  <input type="hidden" name="p" value="a_materiales">
  <div class="row g-2">
    <div class="col-12 col-md-4">
      <label class="form-label" for="q">Buscar</label>
      <input class="form-control" id="q" name="q" value="<?= e($filtros['q'] ?? '') ?>" placeholder="Nombre o descripción">
    </div>
    <div class="col-6 col-md-4">
      <label class="form-label" for="categoria">Categoría</label>
      <select class="form-select" id="categoria" name="categoria" data-auto-filtro>
        <option value="0">Todas</option>
        <?php foreach ($categorias as $c): ?>
          <option value="<?= (int) $c['id'] ?>" <?= (int) ($filtros['categoria_id'] ?? 0) === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['nombre']) ?></option>
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

<p class="hint"><?= (int) $res['pag']['total'] ?> material(es).</p>

<div class="row g-2">
  <?php foreach ($res['filas'] as $m): ?>
    <div class="col-12 col-lg-6">
      <div class="tarjeta p-3 d-flex gap-3">
        <?php if (!empty($m['imagen'])): ?>
          <img class="miniatura" style="width:5rem" src="<?= e(urlFoto($m['imagen'])) ?>" alt="<?= e($m['nombre']) ?>">
        <?php else: ?>
          <div class="placeholder-foto" style="width:5rem;background:<?= e($m['categoria_color']) ?>22;color:<?= e($m['categoria_color']) ?>">
            <i class="fa-solid <?= e($m['icono']) ?>"></i>
          </div>
        <?php endif; ?>
        <div class="flex-grow-1">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div class="fw-bold"><?= e($m['nombre']) ?></div>
              <div class="hint"><span class="badge" style="background:<?= e($m['categoria_color']) ?>"><?= e($m['categoria']) ?></span>
                <?= e(unidadesMedida()[$m['unidad_medida']] ?? $m['unidad_medida']) ?></div>
            </div>
            <span class="badge <?= $m['estado'] === 'activo' ? 'bg-success' : 'bg-secondary' ?>"><?= e($m['estado']) ?></span>
          </div>
          <div class="hint mt-1"><?= e((string) $m['descripcion']) ?></div>
          <div class="d-flex gap-2 mt-2">
            <a class="btn btn-sm btn-outline-recicla" href="<?= e(url('a_material', ['id' => (int) $m['id']])) ?>"><i class="fa-solid fa-pen me-1"></i>Editar</a>
            <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('material', ['id' => (int) $m['id']])) ?>" target="_blank" rel="noopener"><i class="fa-solid fa-eye me-1"></i>Ver ficha</a>
            <form class="ms-auto" method="post" action="<?= e(url('a_material_estado')) ?>"
                  data-confirmar="¿<?= $m['estado'] === 'activo' ? 'Desactivar' : 'Activar' ?> el material <?= e($m['nombre']) ?>?">
              <?= csrf_campo() ?>
              <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
              <button class="btn btn-sm btn-outline-<?= $m['estado'] === 'activo' ? 'danger' : 'success' ?>" type="submit">
                <i class="fa-solid <?= $m['estado'] === 'activo' ? 'fa-ban' : 'fa-check' ?>"></i>
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php if (!$res['filas']): ?>
  <div class="tarjeta p-4 text-center"><i class="fa-solid fa-recycle fa-2x text-muted mb-2"></i><p class="mb-0">No hay materiales con esos filtros.</p></div>
<?php endif; ?>

<?= paginador($res['pag'], 'a_materiales', array_filter([
    'q' => $filtros['q'] ?? '', 'categoria' => $filtros['categoria_id'] ?? 0, 'estado' => $filtros['estado'] ?? '',
])) ?>
