<?php /** RECICLA+ | Mis puntos (programa de incentivos) */ ?>
<h1 class="h5 fw-bold mb-1"><i class="fa-solid fa-star texto-verde me-2"></i>Mis puntos</h1>
<p class="hint">Cada recolección confirmada suma puntos. Cámbialos por premios reales.</p>

<div class="hero-recicla mb-3">
  <span class="hero-badge mb-2"><i class="fa-solid <?= e($nivel['icono']) ?>"></i> Nivel: <?= e($nivel['nombre']) ?></span>
  <div class="d-flex align-items-end gap-2">
    <div class="display-5 fw-bold mb-0"><?= number_format($saldo, 0, ',', '.') ?></div>
    <div class="mb-2 opacity-75">puntos disponibles</div>
  </div>
  <?php if ($nivel['faltan'] > 0): ?>
    <p class="small mb-2 opacity-75">Te faltan <strong><?= number_format($nivel['faltan'], 0, ',', '.') ?></strong> puntos para el siguiente nivel.</p>
  <?php else: ?>
    <p class="small mb-2 opacity-75">¡Alcanzaste el nivel máximo del programa!</p>
  <?php endif; ?>
  <div class="row g-2">
    <div class="col-6"><div class="bg-white bg-opacity-25 rounded p-2 text-center">
      <div class="fw-bold"><?= number_format($ganados, 0, ',', '.') ?></div><div class="small">Ganados</div></div></div>
    <div class="col-6"><div class="bg-white bg-opacity-25 rounded p-2 text-center">
      <div class="fw-bold"><?= number_format($canjeados, 0, ',', '.') ?></div><div class="small">Canjeados</div></div></div>
  </div>
  <div class="d-grid gap-2 d-sm-flex mt-3">
    <a class="btn btn-amarillo btn-accion flex-fill" href="<?= e(url('premios')) ?>"><i class="fa-solid fa-gift me-2"></i>Cambiar por premios</a>
    <a class="btn btn-outline-light btn-accion flex-fill" href="<?= e(url('publicar')) ?>"><i class="fa-solid fa-circle-plus me-2"></i>Reciclar más</a>
  </div>
</div>

<div class="row g-2 mb-3">
  <div class="col-6 col-lg-3">
    <div class="stat-chip"><div class="icono mb-1"><i class="fa-solid fa-ranking-star"></i></div>
      <div class="valor">#<?= (int) $posicion ?></div><div class="etiqueta">Tu posición</div></div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-chip"><div class="icono mb-1"><i class="fa-solid fa-gift"></i></div>
      <div class="valor"><?= count($canjes) ?></div><div class="etiqueta">Canjes hechos</div></div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-chip"><div class="icono mb-1"><i class="fa-solid fa-hand-holding-heart"></i></div>
      <div class="valor"><?= number_format($bonus, 0, ',', '.') ?></div><div class="etiqueta">Bono por recolección</div></div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-chip"><div class="icono mb-1"><i class="fa-solid fa-lock-open"></i></div>
      <div class="valor"><?= number_format($minimos, 0, ',', '.') ?></div><div class="etiqueta">Mínimo para canjear</div></div>
  </div>
</div>

<h2 class="seccion-titulo mb-2"><i class="fa-solid fa-lightbulb texto-verde me-2"></i>¿Cómo ganar más puntos?</h2>
<div class="tarjeta p-3 mb-3">
  <ul class="small mb-0 ps-3">
    <li>Publica tus materiales y espera la visita: los puntos se acreditan <strong>cuando la empresa confirma la recolección</strong>.</li>
    <li>Cada material otorga puntos según su tipo: aluminio, cobre o electrónicos valen más que el vidrio o la madera.</li>
    <li>Recibes un bono extra de <?= number_format($bonus, 0, ',', '.') ?> puntos por cada recolección completada.</li>
    <li>Entre más kilogramos entregues, más puntos acumulas. Puedes cambiar tu material por el premio que prefieras.</li>
  </ul>
</div>

<?php if ($ranking): ?>
  <h2 class="seccion-titulo mb-2"><i class="fa-solid fa-ranking-star texto-verde me-2"></i>Ciudadanos que más reciclan</h2>
  <div class="tarjeta p-3 mb-3">
    <?php foreach ($ranking as $r2): ?>
      <div class="d-flex justify-content-between align-items-center border-bottom py-2 small">
        <div class="d-flex gap-2 align-items-center">
          <span class="badge <?= $r2['puesto'] === 1 ? 'bg-warning text-dark' : 'bg-secondary' ?>">#<?= (int) $r2['puesto'] ?></span>
          <div>
            <div class="fw-semibold"><?= e($r2['nombres'] . ' ' . substr((string) $r2['apellidos'], 0, 1)) ?>.</div>
            <div class="hint"><?= e((string) $r2['barrio']) ?> · <?= e((string) $r2['ciudad']) ?></div>
          </div>
        </div>
        <strong class="texto-verde"><?= number_format((int) $r2['puntos'], 0, ',', '.') ?> pts</strong>
      </div>
    <?php endforeach; ?>
    <p class="hint mb-0 mt-2">Los nombres se muestran parcialmente para proteger tus datos.</p>
  </div>
<?php endif; ?>

<?php if ($canjes): ?>
  <h2 class="seccion-titulo mb-2"><i class="fa-solid fa-gift texto-verde me-2"></i>Mis canjes</h2>
  <?php foreach ($canjes as $c): ?>
    <div class="tarjeta p-3 mb-2">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <div class="fw-bold small"><i class="fa-solid <?= e($c['icono']) ?> texto-verde me-1"></i><?= e($c['premio']) ?></div>
          <div class="hint">Código <?= e($c['codigo']) ?> · <?= e(date('d/m/Y H:i', strtotime((string) $c['created_at']))) ?></div>
        </div>
        <span class="badge <?= $c['estado'] === 'solicitado' ? 'bg-warning text-dark' : ($c['estado'] === 'entregado' ? 'bg-success' : 'bg-secondary') ?>"><?= e(ucfirst($c['estado'])) ?></span>
      </div>
      <div class="hint mt-1">Puntos utilizados: <?= number_format((int) $c['puntos'], 0, ',', '.') ?>
        <?= $c['fecha_entrega'] ? '· Entregado el ' . e(date('d/m/Y', strtotime((string) $c['fecha_entrega']))) : '· Espera la coordinación del administrador' ?></div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<h2 class="seccion-titulo mb-2"><i class="fa-solid fa-clock-rotate-left texto-verde me-2"></i>Historial de puntos</h2>
<?php foreach ($historial['filas'] as $h): ?>
  <div class="tarjeta p-3 mb-2 d-flex justify-content-between align-items-start gap-2">
    <div>
      <div class="fw-semibold small">
        <i class="fa-solid <?= $h['tipo'] === 'ganados' ? 'fa-circle-plus texto-verde' : ($h['tipo'] === 'canjeados' ? 'fa-gift text-danger' : 'fa-scale-balanced text-warning') ?> me-1"></i>
        <?= e($h['descripcion']) ?>
      </div>
      <div class="hint"><?= e(date('d/m/Y H:i', strtotime((string) $h['created_at']))) ?>
        <?= $h['numero'] ? '· Solicitud ' . e($h['numero']) : '' ?>
        <?= !empty($h['premio']) ? '· ' . e($h['premio']) : '' ?></div>
    </div>
    <strong class="<?= (int) $h['puntos'] >= 0 ? 'texto-verde' : 'text-danger' ?> text-nowrap">
      <?= (int) $h['puntos'] >= 0 ? '+' : '' ?><?= number_format((int) $h['puntos'], 0, ',', '.') ?>
    </strong>
  </div>
<?php endforeach; ?>
<?php if (!$historial['filas']): ?>
  <div class="tarjeta p-4 text-center">
    <i class="fa-solid fa-star fa-2x text-muted mb-2"></i>
    <p class="mb-2">Aún no tienes movimientos de puntos.</p>
    <a class="btn btn-recicla btn-accion" href="<?= e(url('publicar')) ?>">Registrar mi primer material</a>
  </div>
<?php endif; ?>

<?= paginador($historial['pag'], 'mis_puntos') ?>
