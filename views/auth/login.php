<?php /** RECICLA+ | Inicio de sesión (RF-02) */ ?>
<div class="row justify-content-center">
  <div class="col-12 col-md-8 col-lg-5">
    <div class="tarjeta p-4">
      <div class="text-center mb-3">
        <span class="icono-circulo icono-suave mx-auto mb-2" style="width:3.6rem;height:3.6rem;font-size:1.6rem"><i class="fa-solid fa-recycle"></i></span>
        <h1 class="h5 fw-bold mb-1">Iniciar sesión</h1>
        <p class="hint mb-0">El sistema identifica automáticamente tu rol.</p>
      </div>

      <?php if (!empty($bloqueo)): ?>
        <div class="alert alert-danger small"><i class="fa-solid fa-lock me-1"></i>Demasiados intentos fallidos. Espere 5 minutos antes de intentar de nuevo.</div>
      <?php endif; ?>

      <form method="post" action="<?= e(url('login_post')) ?>" autocomplete="on" novalidate>
        <?= csrf_campo() ?>
        <div class="mb-3">
          <label class="form-label" for="email">Correo electrónico</label>
          <input class="form-control form-control-lg" type="email" id="email" name="email" inputmode="email"
                 autocomplete="username" required placeholder="tucorreo@ejemplo.com">
        </div>
        <div class="mb-3">
          <label class="form-label" for="password">Contraseña</label>
          <div class="input-group input-group-lg">
            <input class="form-control" type="password" id="password" name="password" autocomplete="current-password" required>
            <button class="btn btn-outline-secondary" type="button" onclick="const i=document.getElementById('password');i.type=i.type==='password'?'text':'password';">
              <i class="fa-solid fa-eye"></i>
            </button>
          </div>
        </div>
        <button class="btn btn-recicla btn-accion w-100" type="submit"><i class="fa-solid fa-right-to-bracket me-2"></i>Ingresar</button>
      </form>

      <div class="d-flex justify-content-between mt-3 small">
        <a href="<?= e(url('registro')) ?>">Crear cuenta</a>
        <a href="<?= e(url('recuperar')) ?>">Olvidé mi contraseña</a>
      </div>
    </div>

    <div class="tarjeta p-3 mt-3">
      <div class="etiqueta-mini mb-2"><i class="fa-solid fa-key me-1"></i>Cuentas de prueba del sistema</div>
      <ul class="small mb-0 ps-3">
        <li>Ciudadano: <code>ciudadano@recicla.local</code> / <code>Ciudadano123*</code></li>
        <li>Empresa: <code>operador@recicla.local</code> / <code>Operador123*</code></li>
        <li>Administrador: <code>admin@recicla.local</code> / <code>Admin123*</code></li>
      </ul>
    </div>

    <div class="text-center mt-3">
      <a class="btn btn-sm btn-outline-recicla" href="<?= e(url('materiales')) ?>"><i class="fa-solid fa-list-check me-1"></i>Consultar materiales sin registrarme</a>
    </div>
  </div>
</div>
