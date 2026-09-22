<?php /** RECICLA+ | Historial de recolecciones del ciudadano */ ?>
<h1 class="h5 fw-bold mb-1"><i class="fa-solid fa-clock-rotate-left texto-verde me-2"></i>Mi historial</h1>
<p class="hint">Solicitudes que ya fueron recolectadas por una empresa operadora.</p>

<div class="row g-2 mb-3">
  <div class="col-6">
    <div class="stat-chip"><div class="icono mb-1"><i class="fa-solid fa-weight-hanging"></i></div>
      <div class="valor"><?= e(formatearCantidad((float) $totales['kg'])) ?> kg</div><div class="etiqueta">Reciclado</div></div>
  </div>
  <div class="col-6">
    <div class="stat-chip"><div class="icono mb-1"><i class="fa-solid fa-truck-ramp-box"></i></div>
      <div class="valor"><?= (int) $totales['visitas'] ?></div><div class="etiqueta">Recolecciones</div></div>
  </div>
</div>

<?php $rutaDetalle = 'mi_solicitud'; $mostrarCiudadano = false; ?>
<?php foreach ($res['filas'] as $s): ?>
  <?php $pesoRecolectado = $pesos[(int) $s['id']] ?? null; ?>
  <?php require BASE_PATH . '/views/partials/solicitud_card.php'; ?>
<?php endforeach; ?>

<?php if (!$res['filas']): ?>
  <div class="tarjeta p-4 text-center">
    <i class="fa-solid fa-box-open fa-2x text-muted mb-2"></i>
    <p class="mb-0">Todavía no tienes recolecciones realizadas.</p>
  </div>
<?php endif; ?>

<?= paginador($res['pag'], 'historial') ?>
