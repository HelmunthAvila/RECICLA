<?php /** RECICLA+ | Consulta y filtrado de solicitudes (RF-11) */ ?>
<h1 class="h5 fw-bold mb-1"><i class="fa-solid fa-inbox texto-verde me-2"></i>Solicitudes</h1>
<p class="hint">Filtra por fecha, ciudad, barrio, material o estado.</p>

<div class="btn-group w-100 mb-2" role="group">
  <a class="btn btn-sm <?= $tipo === 'disponibles' ? 'btn-recicla' : 'btn-outline-recicla' ?>" href="<?= e(url('e_solicitudes', ['tipo' => 'disponibles'])) ?>">Disponibles</a>
  <a class="btn btn-sm <?= $tipo === 'aceptadas' ? 'btn-recicla' : 'btn-outline-recicla' ?>" href="<?= e(url('e_solicitudes', ['tipo' => 'aceptadas'])) ?>">Aceptadas</a>
  <a class="btn btn-sm <?= $tipo === 'programadas' ? 'btn-recicla' : 'btn-outline-recicla' ?>" href="<?= e(url('e_solicitudes', ['tipo' => 'programadas'])) ?>">Programadas</a>
  <a class="btn btn-sm <?= $tipo === 'recolectadas' ? 'btn-recicla' : 'btn-outline-recicla' ?>" href="<?= e(url('e_solicitudes', ['tipo' => 'recolectadas'])) ?>">Recolectadas</a>
</div>

<form class="tarjeta-form" method="get" action="<?= e(BASE_URL) ?>/index.php">
  <input type="hidden" name="p" value="e_solicitudes">
  <input type="hidden" name="tipo" value="<?= e($tipo) ?>">
  <div class="row g-2">
    <div class="col-12 col-md-4">
      <label class="form-label" for="q">Buscar</label>
      <input class="form-control" id="q" name="q" value="<?= e($filtros['q'] ?? '') ?>" placeholder="Número, barrio o dirección">
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
    <div class="col-6 col-md-4">
      <label class="form-label" for="barrio">Barrio</label>
      <input class="form-control" id="barrio" name="barrio" list="lista-barrios" value="<?= e($filtros['barrio'] ?? '') ?>">
      <datalist id="lista-barrios">
        <?php foreach ($barrios as $b): ?><option value="<?= e($b) ?>"><?php endforeach; ?>
      </datalist>
    </div>
    <div class="col-12 col-md-4">
      <label class="form-label" for="material_id">Material</label>
      <select class="form-select" id="material_id" name="material_id">
        <option value="0">Todos</option>
        <?php foreach ($materiales as $m): ?>
          <option value="<?= (int) $m['id'] ?>" <?= (int) ($filtros['material_id'] ?? 0) === (int) $m['id'] ? 'selected' : '' ?>><?= e($m['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-6 col-md-4">
      <label class="form-label" for="desde">Desde</label>
      <input class="form-control" type="date" id="desde" name="desde" value="<?= e($filtros['desde'] ?? '') ?>">
    </div>
    <div class="col-6 col-md-4">
      <label class="form-label" for="hasta">Hasta</label>
      <input class="form-control" type="date" id="hasta" name="hasta" value="<?= e($filtros['hasta'] ?? '') ?>">
    </div>
  </div>
  <div class="d-flex gap-2 mt-3">
    <button class="btn btn-recicla flex-fill" type="submit"><i class="fa-solid fa-filter me-1"></i> Aplicar filtros</button>
    <a class="btn btn-outline-recicla" href="<?= e(url('e_solicitudes', ['tipo' => $tipo])) ?>"><i class="fa-solid fa-eraser"></i></a>
  </div>
</form>

<p class="hint"><?= (int) $res['pag']['total'] ?> solicitud(es) encontrada(s).</p>

<?php $rutaDetalle = 'e_solicitud'; $mostrarCiudadano = true; ?>
<?php foreach ($res['filas'] as $s): ?>
  <?php require BASE_PATH . '/views/partials/solicitud_card.php'; ?>
<?php endforeach; ?>

<?php if (!$res['filas']): ?>
  <div class="tarjeta p-4 text-center">
    <i class="fa-solid fa-inbox fa-2x text-muted mb-2"></i>
    <p class="mb-0">No hay solicitudes con los filtros seleccionados.</p>
  </div>
<?php endif; ?>

<?= paginador($res['pag'], 'e_solicitudes', array_filter([
    'tipo' => $tipo, 'q' => $filtros['q'] ?? '', 'ciudad' => $filtros['ciudad'] ?? '',
    'barrio' => $filtros['barrio'] ?? '', 'material_id' => $filtros['material_id'] ?? 0,
    'desde' => $filtros['desde'] ?? '', 'hasta' => $filtros['hasta'] ?? '',
])) ?>
