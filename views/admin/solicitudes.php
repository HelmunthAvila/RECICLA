<?php /** RECICLA+ | Consulta administrativa de solicitudes (RF-19) */ ?>
<h1 class="h5 fw-bold mb-1"><i class="fa-solid fa-clipboard-list texto-verde me-2"></i>Solicitudes</h1>
<p class="hint">Todas las solicitudes del sistema con su estado actual.</p>

<form class="tarjeta-form" method="get" action="<?= e(BASE_URL) ?>/index.php">
  <input type="hidden" name="p" value="a_solicitudes">
  <div class="row g-2">
    <div class="col-12 col-md-3">
      <label class="form-label" for="q">Buscar</label>
      <input class="form-control" id="q" name="q" value="<?= e($filtros['q'] ?? '') ?>" placeholder="Número, barrio, dirección o documento">
    </div>
    <div class="col-6 col-md-3">
      <label class="form-label" for="estado">Estado</label>
      <select class="form-select" id="estado" name="estado" data-auto-filtro>
        <option value="">Todos</option>
        <?php foreach (estadosSolicitud() as $clave => $cfg): ?>
          <option value="<?= e($clave) ?>" <?= ($filtros['estado'] ?? '') === $clave ? 'selected' : '' ?>><?= e($cfg['label']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-6 col-md-3">
      <label class="form-label" for="empresa_id">Empresa</label>
      <select class="form-select" id="empresa_id" name="empresa_id">
        <option value="0">Todas</option>
        <?php foreach ($empresas as $em): ?>
          <option value="<?= (int) $em['id'] ?>" <?= (int) ($filtros['empresa_id'] ?? 0) === (int) $em['id'] ? 'selected' : '' ?>><?= e($em['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-6 col-md-3">
      <label class="form-label" for="material_id">Material</label>
      <select class="form-select" id="material_id" name="material_id">
        <option value="0">Todos</option>
        <?php foreach ($materiales as $m): ?>
          <option value="<?= (int) $m['id'] ?>" <?= (int) ($filtros['material_id'] ?? 0) === (int) $m['id'] ? 'selected' : '' ?>><?= e($m['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-6 col-md-3">
      <label class="form-label" for="ciudad">Ciudad</label>
      <select class="form-select" id="ciudad" name="ciudad" data-auto-filtro>
        <option value="">Todas</option>
        <?php foreach ($ciudades as $c): ?>
          <option value="<?= e($c) ?>" <?= ($filtros['ciudad'] ?? '') === $c ? 'selected' : '' ?>><?= e($c) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-6 col-md-3">
      <label class="form-label" for="barrio">Barrio</label>
      <input class="form-control" id="barrio" name="barrio" value="<?= e($filtros['barrio'] ?? '') ?>">
    </div>
    <div class="col-6 col-md-3">
      <label class="form-label" for="desde">Desde</label>
      <input class="form-control" type="date" id="desde" name="desde" value="<?= e($filtros['desde'] ?? '') ?>">
    </div>
    <div class="col-6 col-md-3">
      <label class="form-label" for="hasta">Hasta</label>
      <input class="form-control" type="date" id="hasta" name="hasta" value="<?= e($filtros['hasta'] ?? '') ?>">
    </div>
  </div>
  <div class="d-flex gap-2 mt-3">
    <button class="btn btn-recicla flex-fill" type="submit"><i class="fa-solid fa-filter me-1"></i>Aplicar filtros</button>
    <a class="btn btn-outline-recicla" href="<?= e(url('a_solicitudes')) ?>"><i class="fa-solid fa-eraser"></i></a>
  </div>
</form>

<p class="hint"><?= (int) $res['pag']['total'] ?> solicitud(es).</p>

<?php $rutaDetalle = 'a_solicitud'; $mostrarCiudadano = true; ?>
<?php foreach ($res['filas'] as $s): ?>
  <?php require BASE_PATH . '/views/partials/solicitud_card.php'; ?>
<?php endforeach; ?>

<?php if (!$res['filas']): ?>
  <div class="tarjeta p-4 text-center"><i class="fa-solid fa-inbox fa-2x text-muted mb-2"></i><p class="mb-0">No hay solicitudes con esos filtros.</p></div>
<?php endif; ?>

<?= paginador($res['pag'], 'a_solicitudes', array_filter([
    'q' => $filtros['q'] ?? '', 'estado' => $filtros['estado'] ?? '', 'empresa_id' => $filtros['empresa_id'] ?? 0,
    'material_id' => $filtros['material_id'] ?? 0, 'ciudad' => $filtros['ciudad'] ?? '',
    'barrio' => $filtros['barrio'] ?? '', 'desde' => $filtros['desde'] ?? '', 'hasta' => $filtros['hasta'] ?? '',
])) ?>
