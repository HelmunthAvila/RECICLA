<?php /** RECICLA+ | 403 · Acceso no autorizado */ ?>
<div class="tarjeta p-4 text-center">
  <div class="display-6 text-danger mb-2"><i class="fa-solid fa-lock"></i></div>
  <h1 class="h5 fw-bold">No tiene permisos para ver esta información</h1>
  <p class="text-muted mb-0"><?= e($detalle ?? 'La solicitud consultada no pertenece a su cuenta o su rol no permite acceder a este módulo.') ?></p>
  <div class="d-grid gap-2 col-12 col-md-6 mx-auto mt-3">
    <a class="btn btn-recicla btn-accion" href="<?= e(url(rolActual() === 'ciudadano' ? 'panel' : (rolActual() === 'empresa' ? 'e_panel' : (rolActual() === 'administrador' ? 'a_panel' : 'inicio')))) ?>">
      <i class="fa-solid fa-house me-1"></i> Volver a mi panel
    </a>
  </div>
</div>
