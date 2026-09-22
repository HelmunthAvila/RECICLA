<?php /** RECICLA+ | Panel del ciudadano */ ?>
<div class="hero-recicla mb-3">
  <h1 class="h5 fw-bold mb-1">Hola, <?= e($u['nombres']) ?> <i class="fa-solid fa-hand-sparkles"></i></h1>
  <p class="mb-3 opacity-75 small"><?= e($u['direccion'] ?: 'Completa tu dirección en el perfil') ?> · <?= e($u['barrio'] ?: '') ?> <?= e($u['ciudad'] ? ', ' . $u['ciudad'] : '') ?></p>
  <a class="btn btn-amarillo btn-accion w-100" href="<?= e(url('publicar')) ?>"><i class="fa-solid fa-circle-plus me-2"></i>Quiero reciclar</a>
</div>

<div class="row g-2 mb-3">
  <div class="col-6 col-lg-3">
    <div class="stat-chip"><div class="icono mb-1"><i class="fa-solid fa-clipboard-list"></i></div>
      <div class="valor"><?= (int) $resumen['total'] ?></div><div class="etiqueta">Solicitudes</div></div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-chip"><div class="icono mb-1"><i class="fa-solid fa-hourglass-half"></i></div>
      <div class="valor"><?= (int) $resumen['pendientes'] ?></div><div class="etiqueta">En revisión</div></div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-chip"><div class="icono mb-1"><i class="fa-solid fa-truck-fast"></i></div>
      <div class="valor"><?= (int) $resumen['programadas'] ?></div><div class="etiqueta">Programadas</div></div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-chip"><div class="icono mb-1"><i class="fa-solid fa-circle-check"></i></div>
      <div class="valor"><?= (int) $resumen['recolectadas'] ?></div><div class="etiqueta">Recolectadas</div></div>
  </div>
</div>

<div class="tarjeta p-3 mb-3 d-flex align-items-center gap-3">
  <div class="icono-circulo icono-suave"><i class="fa-solid fa-leaf"></i></div>
  <div>
    <div class="etiqueta-mini">Tu impacto ambiental</div>
    <div class="fw-bold"><?= e(formatearCantidad((float) $impacto['kg'])) ?> kg de material reciclado</div>
    <div class="hint"><?= (int) $impacto['visitas'] ?> recolección(es) realizada(s)</div>
  </div>
</div>

<a class="tarjeta p-3 mb-3 d-flex align-items-center gap-3 text-dark" href="<?= e(url('mis_puntos')) ?>">
  <div class="icono-circulo" style="background:var(--amarillo);color:#1c5202"><i class="fa-solid fa-star"></i></div>
  <div class="flex-grow-1">
    <div class="etiqueta-mini">Programa de incentivos · <?= e($nivel['nombre']) ?></div>
    <div class="fw-bold"><?= number_format($puntos, 0, ',', '.') ?> puntos disponibles</div>
    <div class="hint">
      <?php if (!empty($siguientePremio)): ?>
        Te faltan <?= number_format((int) $siguientePremio['puntos_requeridos'] - $puntos, 0, ',', '.') ?> puntos para
        "<?= e($siguientePremio['nombre']) ?>"
      <?php else: ?>
        ¡Puedes canjear cualquier premio del catálogo!
      <?php endif; ?>
    </div>
  </div>
  <i class="fa-solid fa-chevron-right text-muted"></i>
</a>

<?php if ($avisos): ?>
  <div class="tarjeta p-3 mb-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <span class="seccion-titulo mb-0"><i class="fa-solid fa-bell texto-verde me-2"></i>Avisos recientes</span>
      <a class="small" href="<?= e(url('notificaciones')) ?>">Ver todos</a>
    </div>
    <?php foreach ($avisos as $a): ?>
      <div class="border-bottom py-2 small">
        <div class="fw-semibold"><?= e($a['titulo']) ?></div>
        <div class="hint"><?= e($a['mensaje']) ?></div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-2">
  <h2 class="seccion-titulo mb-0">Mis últimas solicitudes</h2>
  <a class="small" href="<?= e(url('mis_solicitudes')) ?>">Ver todas</a>
</div>
<?php $rutaDetalle = 'mi_solicitud'; ?>
<?php foreach ($ultimas as $s): ?>
  <?php require BASE_PATH . '/views/partials/solicitud_card.php'; ?>
<?php endforeach; ?>
<?php if (!$ultimas): ?>
  <div class="tarjeta p-4 text-center">
    <i class="fa-solid fa-box-open fa-2x text-muted mb-2"></i>
    <p class="mb-2">Aún no tienes solicitudes registradas.</p>
    <a class="btn btn-recicla btn-accion" href="<?= e(url('publicar')) ?>"><i class="fa-solid fa-circle-plus me-2"></i>Registrar mi primer material</a>
  </div>
<?php endif; ?>

<h2 class="seccion-titulo mt-4 mb-2">Materiales que puedes entregar</h2>
<div class="grid-materiales">
  <?php foreach ($sugeridos as $m): ?>
    <a class="tarjeta p-2 text-dark d-block" href="<?= e(url('material', ['id' => (int) $m['id']])) ?>">
      <div class="placeholder-foto" style="background:<?= e($m['categoria_color']) ?>22;color:<?= e($m['categoria_color']) ?>">
        <i class="fa-solid <?= e($m['icono']) ?>"></i>
      </div>
      <div class="fw-bold small mt-2"><?= e($m['nombre']) ?></div>
      <div class="hint"><?= e($m['categoria']) ?></div>
    </a>
  <?php endforeach; ?>
</div>
