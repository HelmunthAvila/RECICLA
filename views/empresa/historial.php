<?php /** RECICLA+ | Recolecciones realizadas por la empresa */ ?>
<h1 class="h5 fw-bold mb-1"><i class="fa-solid fa-truck-ramp-box texto-verde me-2"></i>Recolecciones realizadas</h1>
<p class="hint">Historial de visitas efectivamente registradas.</p>

<div class="row g-2 mb-3">
  <div class="col-6">
    <div class="stat-chip"><div class="icono mb-1"><i class="fa-solid fa-weight-hanging"></i></div>
      <div class="valor"><?= e(formatearCantidad((float) $totales['kg'])) ?> kg</div><div class="etiqueta">Recolectado</div></div>
  </div>
  <div class="col-6">
    <div class="stat-chip"><div class="icono mb-1"><i class="fa-solid fa-truck"></i></div>
      <div class="valor"><?= (int) $totales['visitas'] ?></div><div class="etiqueta">Visitas</div></div>
  </div>
</div>

<form class="tarjeta-form" method="get" action="<?= e(BASE_URL) ?>/index.php">
  <input type="hidden" name="p" value="e_historial">
  <div class="row g-2">
    <div class="col-12 col-md-4">
      <label class="form-label" for="q">Buscar</label>
      <input class="form-control" id="q" name="q" value="<?= e($filtros['q'] ?? '') ?>" placeholder="Número, barrio o empresa">
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
    <div class="col-12 col-md-4 d-flex align-items-end">
      <button class="btn btn-recicla w-100" type="submit"><i class="fa-solid fa-filter me-1"></i>Filtrar</button>
    </div>
  </div>
</form>

<p class="hint"><?= (int) $res['pag']['total'] ?> recolección(es).</p>

<?php foreach ($res['filas'] as $r): ?>
  <div class="tarjeta p-3 mb-2">
    <div class="d-flex justify-content-between align-items-start">
      <div>
        <div class="fw-bold"><?= e($r['numero']) ?></div>
        <div class="hint"><i class="fa-solid fa-user me-1"></i><?= e($r['ciudadano_nombre']) ?></div>
        <div class="hint"><i class="fa-solid fa-location-dot me-1"></i><?= e($r['barrio']) ?>, <?= e($r['ciudad']) ?></div>
      </div>
      <div class="text-end">
        <div class="badge bg-success-subtle text-success-emphasis"><?= e(date('d/m/Y', strtotime((string) $r['fecha']))) ?></div>
        <div class="hint"><?= e(substr((string) $r['hora'], 0, 5)) ?></div>
      </div>
    </div>
    <div class="row g-1 small mt-2">
      <div class="col-6"><span class="etiqueta-mini">Peso registrado</span>
        <div><?= $r['peso_total'] !== null ? e(formatearCantidad((float) $r['peso_total'])) . ' kg' : 'No registrado' ?></div></div>
      <div class="col-6"><span class="etiqueta-mini">Materiales</span><div><?= (int) $r['total_materiales'] ?> tipo(s)</div></div>
      <?php if (!empty($r['operador'])): ?>
        <div class="col-12"><span class="etiqueta-mini">Operador que realizó la visita</span>
          <div><i class="fa-solid fa-id-badge me-1"></i><?= e($r['operador']) ?></div></div>
      <?php endif; ?>
    </div>
    <?php if (!empty($r['evidencia'])): ?>
      <a href="<?= e(urlFoto($r['evidencia'])) ?>" target="_blank" rel="noopener" class="d-inline-block mt-2">
        <img class="miniatura" style="width:6rem" src="<?= e(urlFoto($r['evidencia'])) ?>" alt="Evidencia de la recolección">
      </a>
    <?php endif; ?>
    <a class="btn btn-sm btn-outline-recicla w-100 mt-2" href="<?= e(url('e_solicitud', ['id' => (int) $r['solicitud_id']])) ?>">Ver solicitud</a>
  </div>
<?php endforeach; ?>

<?php if (!$res['filas']): ?>
  <div class="tarjeta p-4 text-center">
    <i class="fa-solid fa-truck fa-2x text-muted mb-2"></i>
    <p class="mb-0">Todavía no hay recolecciones registradas con esos filtros.</p>
  </div>
<?php endif; ?>

<?= paginador($res['pag'], 'e_historial', array_filter([
    'q' => $filtros['q'] ?? '', 'ciudad' => $filtros['ciudad'] ?? '',
    'material_id' => $filtros['material_id'] ?? 0, 'desde' => $filtros['desde'] ?? '', 'hasta' => $filtros['hasta'] ?? '',
])) ?>
