<?php /** RECICLA+ | Registro de ciudadano (RF-01) */ ?>
<?php $old = $_SESSION['old'] ?? []; ?>
<div class="row justify-content-center">
  <div class="col-12 col-lg-7">
    <div class="tarjeta p-4">
      <h1 class="h5 fw-bold mb-1"><i class="fa-solid fa-user-plus texto-verde me-2"></i>Crear mi cuenta</h1>
      <p class="hint">Solo te tomará un minuto. Con estos datos la empresa operadora llegará a tu domicilio.</p>

      <form method="post" action="<?= e(url('registro_post')) ?>" novalidate>
        <?= csrf_campo() ?>
        <div class="row g-3">
          <div class="col-12 col-md-6">
            <label class="form-label" for="nombres">Nombres *</label>
            <input class="form-control form-control-lg" id="nombres" name="nombres" required maxlength="80" value="<?= e($old['nombres'] ?? '') ?>">
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="apellidos">Apellidos *</label>
            <input class="form-control form-control-lg" id="apellidos" name="apellidos" required maxlength="80" value="<?= e($old['apellidos'] ?? '') ?>">
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="documento">Documento *</label>
            <input class="form-control form-control-lg" id="documento" name="documento" inputmode="numeric" required maxlength="15" value="<?= e($old['documento'] ?? '') ?>">
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="telefono">Teléfono / celular *</label>
            <input class="form-control form-control-lg" id="telefono" name="telefono" inputmode="tel" required maxlength="20" value="<?= e($old['telefono'] ?? '') ?>">
          </div>
          <div class="col-12">
            <label class="form-label" for="email">Correo electrónico *</label>
            <input class="form-control form-control-lg" type="email" id="email" name="email" inputmode="email" required value="<?= e($old['email'] ?? '') ?>">
            <div class="hint">El sistema valida que el correo no esté registrado previamente.</div>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="password">Contraseña *</label>
            <input class="form-control form-control-lg" type="password" id="password" name="password" required minlength="8" autocomplete="new-password">
            <div class="hint">Mínimo 8 caracteres, con letras y números.</div>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="confirmar">Confirmar contraseña *</label>
            <input class="form-control form-control-lg" type="password" id="confirmar" name="confirmar" required minlength="8" autocomplete="new-password">
          </div>
          <div class="col-12">
            <label class="form-label" for="direccion">Dirección de recolección *</label>
            <input class="form-control form-control-lg" id="direccion" name="direccion" required maxlength="160" placeholder="Carrera 33 # 45-12, apto 201" value="<?= e($old['direccion'] ?? '') ?>">
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="barrio">Barrio *</label>
            <input class="form-control form-control-lg" id="barrio" name="barrio" required maxlength="60" list="lista-barrios" value="<?= e($old['barrio'] ?? '') ?>">
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="ciudad">Ciudad o municipio *</label>
            <input class="form-control form-control-lg" id="ciudad" name="ciudad" required maxlength="60" list="lista-ciudades" value="<?= e($old['ciudad'] ?? 'Bucaramanga') ?>">
            <datalist id="lista-ciudades">
              <?php foreach ($ciudades as $c): ?><option value="<?= e($c) ?>"><?php endforeach; ?>
            </datalist>
            <datalist id="lista-barrios"></datalist>
          </div>
        </div>

        <button class="btn btn-recicla btn-accion w-100 mt-4" type="submit"><i class="fa-solid fa-check me-2"></i>Crear cuenta y continuar</button>
      </form>

      <p class="text-center small mt-3 mb-0">¿Ya tienes cuenta? <a href="<?= e(url('login')) ?>">Inicia sesión</a></p>
    </div>
    <p class="hint text-center mt-2"><i class="fa-solid fa-shield-halved me-1"></i>Tu contraseña se guarda cifrada y tus datos solo se comparten con la empresa que realice la recolección.</p>
  </div>
</div>
