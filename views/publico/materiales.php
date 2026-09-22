<?php /** RECICLA+ | Catálogo de materiales reciclables (RF-04, RF-05) */ ?>
<h1 class="h5 fw-bold mb-1"><i class="fa-solid fa-list-check texto-verde me-2"></i>Materiales reciclables</h1>
<p class="hint">Consulta qué elementos puedes entregar y bajo qué condiciones.</p>

<form class="tarjeta-form" method="get" action="<?= e(BASE_URL) ?>/index.php">
  <input type="hidden" name="p" value="materiales">
  <div class="input-group mb-2">
    <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass"></i></span>
    <input class="form-control" type="search" name="q" value="<?= e($filtros['q'] ?? '') ?>" placeholder="Buscar material (papel, PET, cobre...)">
    <button class="btn btn-recicla" type="submit">Buscar</button>
  </div>
  <label class="form-label" for="categoria">Categoría</label>
  <select class="form-select form-select-lg" name="categoria" id="categoria" data-auto-filtro>
    <option value="0">Todas las categorías</option>
    <?php foreach ($categorias as $c): ?>
      <option value="<?= (int) $c['id'] ?>" <?= (int) ($filtros['categoria_id'] ?? 0) === (int) $c['id'] ? 'selected' : '' ?>>
        <?= e($c['nombre']) ?> (<?= (int) $c['total_materiales'] ?>)
      </option>
    <?php endforeach; ?>
  </select>
</form>

<p class="hint"><?= (int) $res['pag']['total'] ?> material(es) encontrado(s).</p>

<div class="row g-2">
  <?php foreach ($res['filas'] as $m): ?>
    <div class="col-12 col-md-6 col-lg-4">
      <a class="tarjeta p-2 d-flex gap-2 align-items-center text-dark h-100" href="<?= e(url('material', ['id' => (int) $m['id']])) ?>">
        <?php if (!empty($m['imagen'])): ?>
          <img class="miniatura" style="width:5rem" src="<?= e(urlFoto($m['imagen'])) ?>" alt="<?= e($m['nombre']) ?>" loading="lazy">
        <?php else: ?>
          <div class="placeholder-foto" style="width:5rem;background:<?= e($m['categoria_color']) ?>22;color:<?= e($m['categoria_color']) ?>">
            <i class="fa-solid <?= e($m['icono']) ?>"></i>
          </div>
        <?php endif; ?>
        <div class="flex-grow-1">
          <div class="fw-bold small"><?= e($m['nombre']) ?></div>
          <div class="hint mb-1"><span class="badge rounded-pill" style="background:<?= e($m['categoria_color']) ?>"><?= e($m['categoria']) ?></span></div>
          <div class="hint text-truncate"><?= e((string) $m['descripcion']) ?></div>
          <div class="hint"><i class="fa-solid fa-scale-balanced me-1"></i>Se mide en <?= e(unidadesMedida()[$m['unidad_medida']] ?? $m['unidad_medida']) ?></div>
        </div>
        <i class="fa-solid fa-chevron-right text-muted"></i>
      </a>
    </div>
  <?php endforeach; ?>
  <?php if (!$res['filas']): ?>
    <div class="col-12">
      <div class="tarjeta p-4 text-center">
        <i class="fa-solid fa-magnifying-glass fa-2x text-muted mb-2"></i>
        <p class="mb-0">No se encontraron materiales con ese criterio.</p>
      </div>
    </div>
  <?php endif; ?>
</div>

<?= paginador($res['pag'], 'materiales', ['q' => $filtros['q'] ?? '', 'categoria' => $filtros['categoria_id'] ?? 0]) ?>

<?php if (!estaLogueado()): ?>
  <div class="tarjeta p-3 text-center mt-3">
    <p class="mb-2 small">¿Ya sabes qué quieres entregar? Crea tu cuenta y solicita la recolección.</p>
    <a class="btn btn-recicla btn-accion w-100" href="<?= e(url('registro')) ?>"><i class="fa-solid fa-user-plus me-2"></i>Crear cuenta gratis</a>
  </div>
<?php endif; ?>
