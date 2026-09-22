<?php /** RECICLA+ | 404 */ ?>
<div class="tarjeta p-4 text-center">
  <div class="display-5 texto-verde mb-2"><i class="fa-solid fa-recycle"></i></div>
  <h1 class="h4 fw-bold">No encontramos esa página</h1>
  <p class="text-muted mb-1">La dirección solicitada no existe o fue movida.</p>
  <?php if (!empty($rutaPedida)): ?>
    <p class="small text-muted">Ruta: <code><?= e((string) $rutaPedida) ?></code></p>
  <?php endif; ?>
  <div class="d-grid gap-2 col-12 col-md-6 mx-auto mt-3">
    <a class="btn btn-recicla btn-accion" href="<?= e(url(estaLogueado() ? (rolActual() === 'ciudadano' ? 'panel' : (rolActual() === 'empresa' ? 'e_panel' : 'a_panel')) : 'inicio')) ?>">
      <i class="fa-solid fa-house me-1"></i> Volver al inicio
    </a>
    <a class="btn btn-outline-recicla btn-accion" href="<?= e(url('materiales')) ?>">
      <i class="fa-solid fa-list-check me-1"></i> Ver materiales reciclables
    </a>
  </div>
</div>
