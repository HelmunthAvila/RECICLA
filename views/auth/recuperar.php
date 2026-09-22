<?php /** RECICLA+ | Solicitar recuperación de contraseña (RF-03) */ ?>
<div class="row justify-content-center">
  <div class="col-12 col-md-8 col-lg-5">
    <div class="tarjeta p-4">
      <h1 class="h5 fw-bold mb-1"><i class="fa-solid fa-key texto-verde me-2"></i>Recuperar contraseña</h1>
      <p class="hint">Escribe el correo con el que te registraste y te enviaremos un enlace para crear una nueva contraseña.</p>
      <form method="post" action="<?= e(url('recuperar_post')) ?>" novalidate>
        <?= csrf_campo() ?>
        <div class="mb-3">
          <label class="form-label" for="email">Correo electrónico</label>
          <input class="form-control form-control-lg" type="email" id="email" name="email" inputmode="email" required>
        </div>
        <button class="btn btn-recicla btn-accion w-100" type="submit"><i class="fa-solid fa-paper-plane me-2"></i>Enviar enlace</button>
      </form>
      <p class="text-center small mt-3 mb-0"><a href="<?= e(url('login')) ?>">Volver al inicio de sesión</a></p>
    </div>
  </div>
</div>
