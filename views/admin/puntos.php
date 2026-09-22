<?php /** RECICLA+ | Puntos de los ciudadanos y ajuste manual */ ?>
<h1 class="h5 fw-bold mb-1"><i class="fa-solid fa-star texto-verde me-2"></i>Puntos de los ciudadanos</h1>
<p class="hint">El programa otorga puntos automáticamente al confirmar una recolección. Aquí puede hacer ajustes manuales.</p>

<div class="row g-2 mb-3">
  <?php
  $kpis = [
      ['fa-star', $resumen['otorgados'], 'Otorgados'],
      ['fa-gift', $resumen['canjeados'], 'Canjeados'],
      ['fa-hand-holding-dollar', $resumen['saldo_total'], 'Saldo en circulación'],
      ['fa-users', $resumen['participantes'], 'Participantes'],
  ];
  foreach ($kpis as [$ic, $valor, $etq]): ?>
    <div class="col-6 col-lg-3">
      <div class="stat-chip"><div class="icono mb-1"><i class="fa-solid <?= $ic ?>"></i></div>
        <div class="valor"><?= number_format((int) $valor, 0, ',', '.') ?></div><div class="etiqueta"><?= e($etq) ?></div></div>
    </div>
  <?php endforeach; ?>
</div>

<form class="tarjeta-form" method="get" action="<?= e(BASE_URL) ?>/index.php">
  <input type="hidden" name="p" value="a_puntos">
  <div class="input-group">
    <input class="form-control" name="q" value="<?= e($q) ?>" placeholder="Buscar ciudadano por nombre, documento o correo">
    <button class="btn btn-recicla" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
  </div>
</form>

<div class="row g-3">
  <div class="col-12 col-lg-7">
    <?php foreach ($filas as $f): ?>
      <div class="tarjeta p-3 mb-2">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="fw-bold"><?= e($f['ciudadano']) ?></div>
            <div class="hint">Doc. <?= e($f['documento']) ?> · <?= e((string) $f['barrio']) ?>, <?= e((string) $f['ciudad']) ?></div>
            <div class="hint"><?= e($f['email']) ?> · <?= e((string) $f['telefono']) ?></div>
          </div>
          <div class="text-end">
            <div class="h4 mb-0 texto-verde fw-bold"><?= number_format((int) $f['saldo'], 0, ',', '.') ?></div>
            <div class="hint">saldo · <?= number_format((int) $f['ganados'], 0, ',', '.') ?> ganados · <?= (int) $f['canjes'] ?> canjes</div>
          </div>
        </div>

        <details class="mt-2">
          <summary class="small text-verde" style="cursor:pointer">Ajustar puntos de este ciudadano</summary>
          <form method="post" action="<?= e(url('a_puntos_ajuste')) ?>" class="row g-2 mt-2">
            <?= csrf_campo() ?>
            <input type="hidden" name="usuario_id" value="<?= (int) $f['id'] ?>">
            <div class="col-5">
              <input class="form-control" type="number" name="puntos" step="1" placeholder="Puntos (puede ser negativo)" required>
            </div>
            <div class="col-7">
              <input class="form-control" name="motivo" maxlength="200" placeholder="Motivo del ajuste" required>
            </div>
            <div class="col-12">
              <button class="btn btn-sm btn-outline-recicla" type="submit"><i class="fa-solid fa-scale-balanced me-1"></i>Registrar ajuste</button>
            </div>
          </form>
        </details>
      </div>
    <?php endforeach; ?>
    <?php if (!$filas): ?>
      <div class="tarjeta p-4 text-center"><i class="fa-solid fa-users fa-2x text-muted mb-2"></i><p class="mb-0">No hay ciudadanos con ese criterio.</p></div>
    <?php endif; ?>
    <?= paginador($pag, 'a_puntos', array_filter(['q' => $q])) ?>
  </div>

  <div class="col-12 col-lg-5">
    <div class="tarjeta p-3">
      <span class="seccion-titulo d-block mb-2"><i class="fa-solid fa-ranking-star texto-verde me-2"></i>Top ciudadanos por puntos</span>
      <?php if ($ranking): ?>
        <table class="table table-sm table-recicla mb-0">
          <thead><tr><th>#</th><th>Ciudadano</th><th class="text-center">Puntos</th><th class="text-center">Recolecciones</th></tr></thead>
          <tbody>
          <?php foreach ($ranking as $i => $r2): ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td><?= e($r2['ciudadano']) ?><div class="hint"><?= e((string) $r2['barrio']) ?>, <?= e((string) $r2['ciudad']) ?></div></td>
              <td class="text-center"><strong class="texto-verde"><?= number_format((int) $r2['puntos'], 0, ',', '.') ?></strong></td>
              <td class="text-center"><?= (int) $r2['recolecciones'] ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p class="hint mb-0">Aún no hay puntos otorgados.</p>
      <?php endif; ?>
    </div>
  </div>
</div>
