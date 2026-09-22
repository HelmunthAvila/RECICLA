<?php /** RECICLA+ | Administración del catálogo de premios */ ?>
<div class="d-flex justify-content-between align-items-center mb-2">
  <h1 class="h5 fw-bold mb-0"><i class="fa-solid fa-gift texto-verde me-2"></i>Premios</h1>
  <a class="btn btn-sm btn-recicla" href="<?= e(url('a_premio')) ?>"><i class="fa-solid fa-plus me-1"></i>Nuevo premio</a>
</div>
<p class="hint">El ciudadano cambia sus puntos por estos premios. Mantenga el stock actualizado.</p>

<div class="row g-2 mb-3">
  <?php
  $kpis = [
      ['fa-star', $resumen['otorgados'], 'Puntos otorgados'],
      ['fa-gift', $resumen['canjeados'], 'Puntos canjeados'],
      ['fa-hand-holding-dollar', $resumen['saldo_total'], 'Saldo en circulación'],
      ['fa-users', $resumen['participantes'], 'Ciudadanos con puntos'],
      ['fa-hourglass-half', $resumen['pendientes'], 'Canjes por entregar'],
      ['fa-circle-check', $resumen['entregados'], 'Canjes entregados'],
  ];
  foreach ($kpis as [$ic, $valor, $etq]): ?>
    <div class="col-6 col-lg-2">
      <div class="stat-chip"><div class="icono mb-1"><i class="fa-solid <?= $ic ?>"></i></div>
        <div class="valor"><?= number_format((int) $valor, 0, ',', '.') ?></div>
        <div class="etiqueta"><?= e($etq) ?></div></div>
    </div>
  <?php endforeach; ?>
</div>

<div class="tarjeta p-3 mb-3">
  <span class="seccion-titulo d-block mb-2"><i class="fa-solid fa-chart-column texto-verde me-2"></i>Puntos ganados y canjeados por mes</span>
  <?php $maxP = max(array_map(fn($s) => max($s['ganados'], $s['canjeados']), $serie) ?: [1]); ?>
  <?php foreach ($serie as $m): ?>
    <div class="d-flex align-items-center gap-2 mb-2">
      <span class="etiqueta-mini text-nowrap" style="width:3.2rem"><?= e($m['label']) ?></span>
      <div class="flex-grow-1">
        <div class="bg-light rounded mb-1" style="height:.7rem;overflow:hidden">
          <div style="height:100%;width:<?= $maxP > 0 ? round($m['ganados'] / $maxP * 100) : 0 ?>%;background:linear-gradient(90deg,#2e8800,#7ac943)"></div>
        </div>
        <div class="bg-light rounded" style="height:.7rem;overflow:hidden">
          <div style="height:100%;width:<?= $maxP > 0 ? round($m['canjeados'] / $maxP * 100) : 0 ?>%;background:linear-gradient(90deg,#b02a37,#f06b7b)"></div>
        </div>
      </div>
      <span class="small text-nowrap" style="width:7.5rem;text-align:right">
        <span class="texto-verde">+<?= number_format($m['ganados'], 0, ',', '.') ?></span> /
        <span class="text-danger">-<?= number_format($m['canjeados'], 0, ',', '.') ?></span>
      </span>
    </div>
  <?php endforeach; ?>
</div>

<?php foreach ($premios as $p): ?>
  <div class="tarjeta p-3 mb-2">
    <div class="d-flex justify-content-between align-items-start gap-2">
      <div class="d-flex gap-3">
        <?php if (!empty($p['imagen'])): ?>
          <img class="miniatura" style="width:4.5rem" src="<?= e(urlFoto($p['imagen'])) ?>" alt="<?= e($p['nombre']) ?>">
        <?php else: ?>
          <div class="icono-circulo" style="width:3.2rem;height:3.2rem"><i class="fa-solid <?= e($p['icono']) ?>"></i></div>
        <?php endif; ?>
        <div>
          <div class="fw-bold"><?= e($p['nombre']) ?></div>
          <div class="hint"><?= e((string) $p['descripcion']) ?></div>
          <div class="hint">
            <span class="badge bg-verde-claro text-dark"><?= number_format((int) $p['puntos_requeridos'], 0, ',', '.') ?> puntos</span>
            <span class="badge <?= (int) $p['stock'] > 0 ? 'bg-success' : 'bg-danger' ?> ms-1">Stock: <?= (int) $p['stock'] ?></span>
            <span class="ms-1"><?= (int) $p['total_canjes'] ?> canje(s)</span>
          </div>
        </div>
      </div>
      <span class="badge <?= $p['estado'] === 'activo' ? 'bg-success' : 'bg-secondary' ?>"><?= e($p['estado']) ?></span>
    </div>
    <div class="d-flex gap-2 mt-3">
      <a class="btn btn-sm btn-outline-recicla flex-fill" href="<?= e(url('a_premio', ['id' => (int) $p['id']])) ?>"><i class="fa-solid fa-pen me-1"></i>Editar</a>
      <a class="btn btn-sm btn-outline-secondary flex-fill" href="<?= e(url('a_canjes', ['premio_id' => (int) $p['id']])) ?>"><i class="fa-solid fa-list-check me-1"></i>Ver canjes</a>
      <form class="flex-fill" method="post" action="<?= e(url('a_premio_estado')) ?>"
            data-confirmar="¿<?= $p['estado'] === 'activo' ? 'Desactivar' : 'Activar' ?> el premio <?= e($p['nombre']) ?>?">
        <?= csrf_campo() ?>
        <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
        <button class="btn btn-sm btn-outline-<?= $p['estado'] === 'activo' ? 'danger' : 'success' ?> w-100" type="submit">
          <i class="fa-solid <?= $p['estado'] === 'activo' ? 'fa-ban' : 'fa-check' ?> me-1"></i><?= $p['estado'] === 'activo' ? 'Desactivar' : 'Activar' ?>
        </button>
      </form>
    </div>
  </div>
<?php endforeach; ?>

<?php if (!$premios): ?>
  <div class="tarjeta p-4 text-center">
    <i class="fa-solid fa-gift fa-2x text-muted mb-2"></i>
    <p class="mb-2">Todavía no hay premios en el catálogo.</p>
    <a class="btn btn-recicla" href="<?= e(url('a_premio')) ?>"><i class="fa-solid fa-plus me-1"></i>Crear el primer premio</a>
  </div>
<?php endif; ?>
