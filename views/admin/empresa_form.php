<?php /** RECICLA+ | Crear / editar empresa y sus vehículos (RF-18) */ ?>
<a class="btn btn-sm btn-outline-recicla mb-2" href="<?= e(url('a_empresas')) ?>"><i class="fa-solid fa-arrow-left me-1"></i> Empresas</a>
<h1 class="h5 fw-bold mb-1"><i class="fa-solid fa-building texto-verde me-2"></i><?= e($titulo) ?></h1>

<form method="post" action="<?= e(url('a_empresa_post')) ?>" novalidate>
  <?= csrf_campo() ?>
  <input type="hidden" name="id" value="<?= (int) ($e['id'] ?? 0) ?>">
  <div class="tarjeta-form">
    <div class="row g-2">
      <div class="col-12 col-md-8">
        <label class="form-label" for="nombre">Nombre de la empresa *</label>
        <input class="form-control" id="nombre" name="nombre" required maxlength="120" value="<?= e((string) ($e['nombre'] ?? '')) ?>">
      </div>
      <div class="col-12 col-md-4">
        <label class="form-label" for="nit">NIT *</label>
        <input class="form-control" id="nit" name="nit" required maxlength="20" value="<?= e((string) ($e['nit'] ?? '')) ?>">
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label" for="responsable">Responsable *</label>
        <input class="form-control" id="responsable" name="responsable" required maxlength="120" value="<?= e((string) ($e['responsable'] ?? '')) ?>">
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label" for="telefono">Teléfono *</label>
        <input class="form-control" id="telefono" name="telefono" inputmode="tel" required maxlength="20" value="<?= e((string) ($e['telefono'] ?? '')) ?>">
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label" for="ciudad">Ciudad *</label>
        <input class="form-control" id="ciudad" name="ciudad" required maxlength="60" list="lista-ciudades" value="<?= e((string) ($e['ciudad'] ?? '')) ?>">
        <datalist id="lista-ciudades">
          <?php foreach ($ciudades as $c): ?><option value="<?= e($c) ?>"><?php endforeach; ?>
        </datalist>
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label" for="email">Correo</label>
        <input class="form-control" type="email" id="email" name="email" value="<?= e((string) ($e['email'] ?? '')) ?>">
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label" for="direccion">Dirección</label>
        <input class="form-control" id="direccion" name="direccion" maxlength="160" value="<?= e((string) ($e['direccion'] ?? '')) ?>">
      </div>
    </div>
  </div>
  <div class="d-grid gap-2 mb-4">
    <button class="btn btn-recicla btn-accion" type="submit"><i class="fa-solid fa-floppy-disk me-2"></i>Guardar empresa</button>
    <a class="btn btn-outline-recicla btn-accion" href="<?= e(url('a_empresas')) ?>">Cancelar</a>
  </div>
</form>

<?php if ($e): ?>
  <div class="row g-3">
    <div class="col-12 col-lg-6">
      <div class="tarjeta p-3">
        <span class="seccion-titulo d-block mb-2"><i class="fa-solid fa-truck me-1"></i>Vehículos</span>
        <?php if ($vehiculos): ?>
          <table class="table table-sm table-recicla">
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
        <?php else: ?>
          <p class="hint">Esta empresa aún no tiene vehículos registrados.</p>
        <?php endif; ?>

        <form method="post" action="<?= e(url('a_vehiculo_post')) ?>" class="row g-2 mt-2">
          <?= csrf_campo() ?>
          <input type="hidden" name="empresa_id" value="<?= (int) $e['id'] ?>">
          <div class="col-4"><input class="form-control" name="placa" placeholder="Placa" required maxlength="10"></div>
          <div class="col-4"><input class="form-control" name="tipo" placeholder="Tipo (camión)" required maxlength="40"></div>
          <div class="col-4"><input class="form-control" name="capacidad" placeholder="Capacidad" maxlength="40"></div>
          <div class="col-12"><button class="btn btn-outline-recicla w-100" type="submit"><i class="fa-solid fa-plus me-1"></i>Agregar vehículo</button></div>
        </form>
      </div>
    </div>

    <div class="col-12 col-lg-6">
      <div class="tarjeta p-3">
        <span class="seccion-titulo d-block mb-2"><i class="fa-solid fa-users me-1"></i>Usuarios de la empresa</span>
        <?php if ($usuarios): ?>
          <ul class="small mb-0 ps-3">
            <?php foreach ($usuarios as $u): ?>
              <li><?= e($u['nombres'] . ' ' . $u['apellidos']) ?> · <?= e($u['email']) ?> · <?= e($u['estado']) ?></li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="hint mb-0">Sin usuarios asociados. Cree uno desde <a href="<?= e(url('a_usuario')) ?>">Usuarios</a> con rol Empresa.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php endif; ?>
