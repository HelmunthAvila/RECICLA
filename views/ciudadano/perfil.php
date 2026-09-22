<?php /** RECICLA+ | Perfil del ciudadano */ ?>
<h1 class="h5 fw-bold mb-1"><i class="fa-solid fa-user texto-verde me-2"></i>Mi perfil</h1>
<p class="hint">Mantén actualizados tus datos: la empresa operadora los usará para llegar a tu domicilio.</p>

<div class="tarjeta p-3 mb-3">
  <div class="d-flex align-items-center gap-3">
    <div class="icono-circulo icono-suave" style="width:3.6rem;height:3.6rem;font-size:1.5rem"><i class="fa-solid fa-user"></i></div>
    <div>
      <div class="fw-bold"><?= e($u['nombres'] . ' ' . $u['apellidos']) ?></div>
      <div class="hint">Documento <?= e($u['documento']) ?> · <?= e($u['email']) ?></div>
      <div class="hint">Cuenta <?= e($u['estado']) ?> · Último acceso: <?= e($u['ultimo_acceso'] ? date('d/m/Y H:i', strtotime((string) $u['ultimo_acceso'])) : 'primera vez') ?></div>
    </div>
  </div>
</div>

<form method="post" action="<?= e(url('perfil_post')) ?>" novalidate>
  <?= csrf_campo() ?>
  <div class="tarjeta-form">
    <span class="seccion-titulo d-block mb-2">Datos personales</span>
    <div class="row g-2">
      <div class="col-12 col-md-6">
        <label class="form-label" for="nombres">Nombres *</label>
        <input class="form-control form-control-lg" id="nombres" name="nombres" required maxlength="80" value="<?= e($u['nombres']) ?>">
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label" for="apellidos">Apellidos *</label>
        <input class="form-control form-control-lg" id="apellidos" name="apellidos" required maxlength="80" value="<?= e($u['apellidos']) ?>">
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label">Documento</label>
        <input class="form-control form-control-lg" value="<?= e($u['documento']) ?>" disabled>
        <div class="hint">El documento solo puede cambiarlo el administrador.</div>
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label" for="telefono">Teléfono *</label>
        <input class="form-control form-control-lg" id="telefono" name="telefono" inputmode="tel" required maxlength="20" value="<?= e((string) $u['telefono']) ?>">
      </div>
    </div>
  </div>

  <div class="tarjeta-form">
    <span class="seccion-titulo d-block mb-2">Dirección de recolección</span>
    <div class="mb-2">
      <label class="form-label" for="direccion">Dirección *</label>
      <input class="form-control form-control-lg" id="direccion" name="direccion" required maxlength="160" value="<?= e((string) $u['direccion']) ?>">
    </div>
    <div class="row g-2">
      <div class="col-6">
        <label class="form-label" for="barrio">Barrio *</label>
        <input class="form-control form-control-lg" id="barrio" name="barrio" required maxlength="60" value="<?= e((string) $u['barrio']) ?>">
      </div>
      <div class="col-6">
        <label class="form-label" for="ciudad">Ciudad *</label>
        <input class="form-control form-control-lg" id="ciudad" name="ciudad" required maxlength="60" value="<?= e((string) $u['ciudad']) ?>">
      </div>
    </div>
  </div>

  <div class="tarjeta-form">
    <span class="seccion-titulo d-block mb-2">Cambiar contraseña <span class="hint">(opcional)</span></span>
    <div class="row g-2">
      <div class="col-12 col-md-6">
        <label class="form-label" for="password">Nueva contraseña</label>
        <input class="form-control form-control-lg" type="password" id="password" name="password" minlength="8" autocomplete="new-password">
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label" for="confirmar">Confirmar contraseña</label>
        <input class="form-control form-control-lg" type="password" id="confirmar" name="confirmar" minlength="8" autocomplete="new-password">
      </div>
    </div>
  </div>

  <div class="d-grid gap-2">
    <button class="btn btn-recicla btn-accion" type="submit"><i class="fa-solid fa-floppy-disk me-2"></i>Guardar cambios</button>
    <a class="btn btn-outline-recicla btn-accion" href="<?= e(url('historial')) ?>"><i class="fa-solid fa-clock-rotate-left me-2"></i>Ver mi historial</a>
  </div>
</form>
