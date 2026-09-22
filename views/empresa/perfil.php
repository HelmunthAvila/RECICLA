<?php /** RECICLA+ | Perfil de la empresa operadora */ ?>
<h1 class="h5 fw-bold mb-1"><i class="fa-solid fa-building texto-verde me-2"></i>Mi empresa</h1>
<p class="hint">Datos de contacto y flota de vehículos.</p>

<div class="tarjeta p-3 mb-3">
  <div class="row g-2 small">
    <div class="col-12 col-md-6"><span class="etiqueta-mini">Razón social</span><div class="fw-bold"><?= e((string) $emp['nombre']) ?></div></div>
    <div class="col-6 col-md-3"><span class="etiqueta-mini">NIT</span><div><?= e((string) $emp['nit']) ?></div></div>
    <div class="col-6 col-md-3"><span class="etiqueta-mini">Ciudad</span><div><?= e((string) $emp['ciudad']) ?></div></div>
    <div class="col-12"><span class="etiqueta-mini">Dirección</span><div><?= e((string) $emp['direccion']) ?></div></div>
  </div>
</div>

<form method="post" action="<?= e(url('e_perfil_post')) ?>" novalidate>
  <?= csrf_campo() ?>
  <div class="tarjeta-form">
    <span class="seccion-titulo d-block mb-2">Datos de contacto</span>
    <div class="row g-2">
      <div class="col-12">
        <label class="form-label" for="responsable">Responsable *</label>
        <input class="form-control form-control-lg" id="responsable" name="responsable" required maxlength="120" value="<?= e((string) $emp['responsable']) ?>">
      </div>
      <div class="col-6">
        <label class="form-label" for="telefono">Teléfono *</label>
        <input class="form-control form-control-lg" id="telefono" name="telefono" inputmode="tel" maxlength="20" value="<?= e((string) $emp['telefono']) ?>">
      </div>
      <div class="col-6">
        <label class="form-label" for="email">Correo</label>
        <input class="form-control form-control-lg" type="email" id="email" name="email" value="<?= e((string) $emp['email']) ?>">
      </div>
      <div class="col-12">
        <label class="form-label" for="direccion">Dirección</label>
        <input class="form-control form-control-lg" id="direccion" name="direccion" maxlength="160" value="<?= e((string) $emp['direccion']) ?>">
      </div>
    </div>
    <button class="btn btn-recicla btn-accion w-100 mt-3" type="submit"><i class="fa-solid fa-floppy-disk me-2"></i>Guardar cambios</button>
  </div>
</form>

<div class="tarjeta p-3 mb-3">
  <span class="seccion-titulo d-block mb-2">Vehículos registrados</span>
  <?php if ($vehiculos): ?>
    <div class="table-responsive">
      <table class="table table-sm table-recicla mb-0">
        <thead><tr><th>Placa</th><th>Tipo</th><th>Capacidad</th><th>Estado</th></tr></thead>
        <tbody>
        <?php foreach ($vehiculos as $v): ?>
          <tr>
            <td><strong><?= e($v['placa']) ?></strong></td>
            <td><?= e($v['tipo']) ?></td>
            <td><?= e((string) $v['capacidad']) ?></td>
            <td><span class="badge <?= $v['estado'] === 'activo' ? 'bg-success' : 'bg-secondary' ?>"><?= e($v['estado']) ?></span></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <p class="hint mb-0">Los vehículos de la empresa los registra el administrador del sistema.</p>
  <?php endif; ?>
</div>

<div class="tarjeta p-3">
  <span class="seccion-titulo d-block mb-2">Usuarios de la empresa</span>
  <?php if ($usuarios): ?>
    <ul class="mb-0 small ps-3">
      <?php foreach ($usuarios as $u): ?>
        <li><?= e($u['nombres'] . ' ' . $u['apellidos']) ?> · <?= e($u['email']) ?> · <?= e($u['estado']) ?></li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p class="hint mb-0">Sin usuarios asociados.</p>
  <?php endif; ?>
</div>
