<?php /** RECICLA+ | Canjes de premios solicitados por los ciudadanos */ ?>
<h1 class="h5 fw-bold mb-1"><i class="fa-solid fa-list-check texto-verde me-2"></i>Canjes de premios</h1>
<p class="hint">Al marcar un canje como entregado se registra la fecha; al cancelarlo se devuelven los puntos y el stock.</p>

<div class="row g-2 mb-3">
  <?php
  $kpis = [
      ['fa-hourglass-half', $resumen['pendientes'], 'Por entregar'],
      ['fa-circle-check', $resumen['entregados'], 'Entregados'],
      ['fa-gift', $resumen['canjeados'], 'Puntos canjeados'],
      ['fa-star', $resumen['otorgados'], 'Puntos otorgados'],
  ];
  foreach ($kpis as [$ic, $valor, $etq]): ?>
    <div class="col-6 col-lg-3">
      <div class="stat-chip"><div class="icono mb-1"><i class="fa-solid <?= $ic ?>"></i></div>
        <div class="valor"><?= number_format((int) $valor, 0, ',', '.') ?></div><div class="etiqueta"><?= e($etq) ?></div></div>
    </div>
  <?php endforeach; ?>
</div>

<form class="tarjeta-form" method="get" action="<?= e(BASE_URL) ?>/index.php">
  <input type="hidden" name="p" value="a_canjes">
  <div class="row g-2">
    <div class="col-12 col-md-5">
      <label class="form-label" for="q">Buscar</label>
      <input class="form-control" id="q" name="q" value="<?= e($filtros['q'] ?? '') ?>" placeholder="Código, documento, nombre o premio">
    </div>
    <div class="col-6 col-md-3">
      <label class="form-label" for="estado">Estado</label>
      <select class="form-select" id="estado" name="estado" data-auto-filtro>
        <option value="">Todos</option>
        <option value="solicitado" <?= ($filtros['estado'] ?? '') === 'solicitado' ? 'selected' : '' ?>>Solicitados</option>
        <option value="entregado" <?= ($filtros['estado'] ?? '') === 'entregado' ? 'selected' : '' ?>>Entregados</option>
        <option value="cancelado" <?= ($filtros['estado'] ?? '') === 'cancelado' ? 'selected' : '' ?>>Cancelados</option>
      </select>
    </div>
    <div class="col-6 col-md-4">
      <label class="form-label" for="premio_id">Premio</label>
      <select class="form-select" id="premio_id" name="premio_id">
        <option value="0">Todos</option>
        <?php foreach ($premios as $p): ?>
          <option value="<?= (int) $p['id'] ?>" <?= (int) ($filtros['premio_id'] ?? 0) === (int) $p['id'] ? 'selected' : '' ?>><?= e($p['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
  <button class="btn btn-recicla w-100 mt-3" type="submit"><i class="fa-solid fa-filter me-1"></i>Filtrar</button>
</form>

<p class="hint"><?= (int) $res['pag']['total'] ?> canje(s).</p>

<?php foreach ($res['filas'] as $c): ?>
  <div class="tarjeta p-3 mb-2">
    <div class="d-flex justify-content-between align-items-start">
      <div>
        <div class="fw-bold"><i class="fa-solid <?= e($c['icono']) ?> texto-verde me-1"></i><?= e($c['premio']) ?></div>
        <div class="hint">Código <strong><?= e($c['codigo']) ?></strong> · <?= e(date('d/m/Y H:i', strtotime((string) $c['created_at']))) ?></div>
      </div>
      <span class="badge <?= $c['estado'] === 'solicitado' ? 'bg-warning text-dark' : ($c['estado'] === 'entregado' ? 'bg-success' : 'bg-secondary') ?>"><?= e(ucfirst($c['estado'])) ?></span>
    </div>
    <div class="row g-2 small mt-2">
      <div class="col-12 col-md-6"><span class="etiqueta-mini">Ciudadano</span>
        <div><?= e($c['ciudadano']) ?> · Doc. <?= e($c['documento']) ?></div></div>
      <div class="col-6 col-md-3"><span class="etiqueta-mini">Contacto</span>
        <div><?= e((string) $c['telefono']) ?></div></div>
      <div class="col-6 col-md-3"><span class="etiqueta-mini">Puntos usados</span>
        <div class="fw-bold"><?= number_format((int) $c['puntos'], 0, ',', '.') ?></div></div>
      <div class="col-12"><span class="etiqueta-mini">Ubicación</span>
        <div><?= e((string) $c['barrio']) ?>, <?= e((string) $c['ciudad']) ?> · <?= e($c['email']) ?></div></div>
      <?php if (!empty($c['fecha_entrega'])): ?>
        <div class="col-12"><span class="etiqueta-mini">Entregado</span>
          <div><?= e(date('d/m/Y H:i', strtotime((string) $c['fecha_entrega']))) ?></div></div>
      <?php endif; ?>
    </div>
    <?php if ($c['estado'] === 'solicitado'): ?>
      <div class="d-flex gap-2 mt-3">
        <form class="flex-fill" method="post" action="<?= e(url('a_canje_estado')) ?>" data-confirmar="¿Marcar el canje <?= e($c['codigo']) ?> como ENTREGADO?">
          <?= csrf_campo() ?>
          <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
          <input type="hidden" name="estado" value="entregado">
          <button class="btn btn-sm btn-recicla w-100" type="submit"><i class="fa-solid fa-circle-check me-1"></i>Marcar entregado</button>
        </form>
        <form class="flex-fill" method="post" action="<?= e(url('a_canje_estado')) ?>" data-confirmar="¿Cancelar el canje <?= e($c['codigo']) ?>? Se devolverán los puntos y el stock.">
          <?= csrf_campo() ?>
          <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
          <input type="hidden" name="estado" value="cancelado">
          <button class="btn btn-sm btn-outline-danger w-100" type="submit"><i class="fa-solid fa-ban me-1"></i>Cancelar y devolver puntos</button>
        </form>
      </div>
    <?php endif; ?>
  </div>
<?php endforeach; ?>

<?php if (!$res['filas']): ?>
  <div class="tarjeta p-4 text-center">
    <i class="fa-solid fa-gift fa-2x text-muted mb-2"></i>
    <p class="mb-0">No hay canjes con esos filtros.</p>
  </div>
<?php endif; ?>

<?= paginador($res['pag'], 'a_canjes', array_filter([
    'q' => $filtros['q'] ?? '', 'estado' => $filtros['estado'] ?? '', 'premio_id' => $filtros['premio_id'] ?? 0,
])) ?>
