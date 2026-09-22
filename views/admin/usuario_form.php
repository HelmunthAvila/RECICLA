<?php /** RECICLA+ | Crear / editar usuario (RF-16) */ ?>
<a class="btn btn-sm btn-outline-recicla mb-2" href="<?= e(url('a_usuarios')) ?>"><i class="fa-solid fa-arrow-left me-1"></i> Usuarios</a>
<h1 class="h5 fw-bold mb-1"><i class="fa-solid fa-user-pen texto-verde me-2"></i><?= e($titulo) ?></h1>
<p class="hint">Los usuarios con rol <strong>empresa</strong> deben quedar asociados a una empresa operadora.</p>

<form method="post" action="<?= e(url('a_usuario_post')) ?>" novalidate>
  <?= csrf_campo() ?>
  <input type="hidden" name="id" value="<?= (int) ($u['id'] ?? 0) ?>">

  <div class="tarjeta-form">
    <div class="row g-2">
      <div class="col-12 col-md-6">
        <label class="form-label" for="nombres">Nombres *</label>
        <input class="form-control" id="nombres" name="nombres" required maxlength="80" value="<?= e((string) ($u['nombres'] ?? '')) ?>">
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label" for="apellidos">Apellidos *</label>
        <input class="form-control" id="apellidos" name="apellidos" required maxlength="80" value="<?= e((string) ($u['apellidos'] ?? '')) ?>">
      </div>
      <div class="col-6">
        <label class="form-label" for="documento">Documento *</label>
        <input class="form-control" id="documento" name="documento" inputmode="numeric" required maxlength="15" value="<?= e((string) ($u['documento'] ?? '')) ?>">
      </div>
      <div class="col-6">
        <label class="form-label" for="telefono">Teléfono *</label>
        <input class="form-control" id="telefono" name="telefono" inputmode="tel" required maxlength="20" value="<?= e((string) ($u['telefono'] ?? '')) ?>">
      </div>
      <div class="col-12">
        <label class="form-label" for="email">Correo electrónico *</label>
        <input class="form-control" type="email" id="email" name="email" required value="<?= e((string) ($u['email'] ?? '')) ?>">
      </div>
      <div class="col-12">
        <label class="form-label" for="password">Contraseña <?= $u ? '(dejar vacío para conservar)' : '*' ?></label>
        <input class="form-control" type="password" id="password" name="password" <?= $u ? '' : 'required' ?> minlength="8" autocomplete="new-password">
        <div class="hint">Mínimo 8 caracteres con letras y números. Si no la escribe al crear, se asignará la contraseña temporal <code>Recicla123*</code>.</div>
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label" for="rol_id">Rol *</label>
        <select class="form-select" id="rol_id" name="rol_id" required>
          <?php foreach ($roles as $rl): ?>
            <option value="<?= (int) $rl['id'] ?>" <?= (int) ($u['rol_id'] ?? 1) === (int) $rl['id'] ? 'selected' : '' ?>><?= e(ucfirst($rl['nombre'])) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label" for="empresa_id">Empresa operadora</label>
        <select class="form-select" id="empresa_id" name="empresa_id">
          <option value="0">No aplica</option>
          <?php foreach ($empresas as $em): ?>
            <option value="<?= (int) $em['id'] ?>" <?= (int) ($u['empresa_id'] ?? 0) === (int) $em['id'] ? 'selected' : '' ?>>
              <?= e($em['nombre']) ?> · <?= e($em['ciudad']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12">
        <label class="form-label" for="direccion">Dirección</label>
        <input class="form-control" id="direccion" name="direccion" maxlength="160" value="<?= e((string) ($u['direccion'] ?? '')) ?>">
      </div>
      <div class="col-6">
        <label class="form-label" for="barrio">Barrio</label>
        <input class="form-control" id="barrio" name="barrio" maxlength="60" value="<?= e((string) ($u['barrio'] ?? '')) ?>">
      </div>
      <div class="col-6">
        <label class="form-label" for="ciudad">Ciudad</label>
        <input class="form-control" id="ciudad" name="ciudad" maxlength="60" value="<?= e((string) ($u['ciudad'] ?? '')) ?>">
      </div>
    </div>
  </div>

  <div class="d-grid gap-2">
    <button class="btn btn-recicla btn-accion" type="submit"><i class="fa-solid fa-floppy-disk me-2"></i>Guardar usuario</button>
    <a class="btn btn-outline-recicla btn-accion" href="<?= e(url('a_usuarios')) ?>">Cancelar</a>
  </div>
</form>
