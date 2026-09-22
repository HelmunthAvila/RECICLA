<?php /** RECICLA+ | Definir nueva contraseña (RF-03) */ ?>
<div class="row justify-content-center">
  <div class="col-12 col-md-8 col-lg-5">
    <div class="tarjeta p-4">
      <h1 class="h5 fw-bold mb-1"><i class="fa-solid fa-lock texto-verde me-2"></i>Nueva contraseña</h1>
      <p class="hint">El enlace es válido durante 60 minutos desde su solicitud.</p>
      <form method="post" action="<?= e(url('restablecer_post')) ?>" novalidate>
        <?= csrf_campo() ?>
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <div class="mb-3">
          <label class="form-label" for="password">Nueva contraseña</label>
          <input class="form-control form-control-lg" type="password" id="password" name="password" required minlength="8">
          <div class="hint">Mínimo 8 caracteres, con letras y números.</div>
        </div>
        <div class="mb-3">
          <label class="form-label" for="confirmar">Confirmar contraseña</label>
          <input class="form-control form-control-lg" type="password" id="confirmar" name="confirmar" required minlength="8">
        </div>
        <button class="btn btn-recicla btn-accion w-100" type="submit"><i class="fa-solid fa-check me-2"></i>Guardar contraseña</button>
      </form>
    </div>
  </div>
</div>
