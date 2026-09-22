<?php /** RECICLA+ | Consulta administrativa de recolecciones (RF-19, RF-21) */ ?>
<h1 class="h5 fw-bold mb-1"><i class="fa-solid fa-truck-ramp-box texto-verde me-2"></i>Recolecciones</h1>
<p class="hint">Visitas efectivamente realizadas y cantidades recolectadas.</p>

<form class="tarjeta-form" method="get" action="<?= e(BASE_URL) ?>/index.php">
  <input type="hidden" name="p" value="a_recolecciones">
  <div class="row g-2">
    <div class="col-12 col-md-3">
      <label class="form-label" for="q">Buscar</label>
      <input class="form-control" id="q" name="q" value="<?= e($filtros['q'] ?? '') ?>" placeholder="Número, barrio o empresa">
    </div>
    <div class="col-6 col-md-3">
      <label class="form-label" for="empresa_id">Empresa</label>
      <select class="form-select" id="empresa_id" name="empresa_id" data-auto-filtro>
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
    <div class="col-6 col-md-3 d-flex align-items-end">
      <button class="btn btn-recicla w-100" type="submit"><i class="fa-solid fa-filter me-1"></i>Filtrar</button>
    </div>
  </div>
</form>

<p class="hint"><?= (int) $res['pag']['total'] ?> recolección(es).</p>

<div class="tarjeta p-2 mb-3">
  <div class="table-responsive">
    <table class="table table-sm table-recicla mb-0">
      <thead>
        <tr><th>Fecha</th><th>Solicitud</th><th>Ciudadano / zona</th><th>Empresa</th><th class="text-center">Peso</th><th></th></tr>
      </thead>
      <tbody>
      <?php foreach ($res['filas'] as $r): ?>
        <tr>
          <td><?= e(date('d/m/Y', strtotime((string) $r['fecha']))) ?><div class="hint"><?= e(substr((string) $r['hora'], 0, 5)) ?></div></td>
          <td><strong><?= e($r['numero']) ?></strong></td>
          <td><?= e($r['ciudadano_nombre']) ?>
            <div class="hint"><?= e($r['barrio']) ?>, <?= e($r['ciudad']) ?></div></td>
          <td><?= e($r['empresa_nombre']) ?></td>
          <td class="text-center"><?= $r['peso_total'] !== null ? e(formatearCantidad((float) $r['peso_total'])) . ' kg' : '—' ?></td>
          <td><a class="btn btn-sm btn-outline-recicla" href="<?= e(url('a_solicitud', ['id' => (int) $r['solicitud_id']])) ?>"><i class="fa-solid fa-eye"></i></a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php if (!$res['filas']): ?><p class="hint text-center mb-0 py-3">No hay recolecciones con esos filtros.</p><?php endif; ?>
</div>

<?= paginador($res['pag'], 'a_recolecciones', array_filter([
    'q' => $filtros['q'] ?? '', 'empresa_id' => $filtros['empresa_id'] ?? 0, 'material_id' => $filtros['material_id'] ?? 0,
    'ciudad' => $filtros['ciudad'] ?? '', 'barrio' => $filtros['barrio'] ?? '',
    'desde' => $filtros['desde'] ?? '', 'hasta' => $filtros['hasta'] ?? '',
])) ?>
