<?php /** RECICLA+ | Rutas y organización de recolecciones (RF-15) */ ?>
<h1 class="h5 fw-bold mb-1"><i class="fa-solid fa-route texto-verde me-2"></i>Rutas y recorridos</h1>
<p class="hint">Agrupe solicitudes por barrio, zona, fecha y ciudad para organizar el recorrido de los vehículos.</p>

<div class="row g-3">
  <div class="col-12 col-lg-5">
    <div class="tarjeta-form">
      <span class="seccion-titulo d-block mb-2"><i class="fa-solid fa-plus me-1"></i>Crear ruta</span>
      <form method="post" action="<?= e(url('e_ruta_post')) ?>" novalidate>
        <?= csrf_campo() ?>
        <div class="mb-2">
          <label class="form-label" for="nombre">Nombre de la ruta *</label>
          <input class="form-control" id="nombre" name="nombre" required maxlength="90" placeholder="Ej: Ruta Norte mañana">
        </div>
        <div class="row g-2 mb-2">
          <div class="col-6">
            <label class="form-label" for="zona">Zona / barrio</label>
            <input class="form-control" id="zona" name="zona" maxlength="60" placeholder="Ej: Zona norte">
          </div>
          <div class="col-6">
            <label class="form-label" for="ciudad">Ciudad *</label>
            <input class="form-control" id="ciudad" name="ciudad" required maxlength="60" placeholder="Ej: Bucaramanga">
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label" for="fecha">Fecha del recorrido *</label>
          <input class="form-control" type="date" id="fecha" name="fecha" required value="<?= e(date('Y-m-d')) ?>">
        </div>
        <button class="btn btn-recicla w-100" type="submit"><i class="fa-solid fa-floppy-disk me-1"></i>Crear ruta</button>
      </form>
    </div>

    <div class="tarjeta-form">
      <span class="seccion-titulo d-block mb-2"><i class="fa-solid fa-link me-1"></i>Asignar solicitud a una ruta</span>
      <form method="post" action="<?= e(url('e_asignar_ruta')) ?>">
        <?= csrf_campo() ?>
        <div class="mb-2">
          <label class="form-label" for="solicitud_id">Solicitud</label>
          <select class="form-select" id="solicitud_id" name="solicitud_id" required>
            <option value="">Seleccione…</option>
            <?php foreach ($asignables as $a): ?>
              <option value="<?= (int) $a['id'] ?>">
                <?= e($a['numero']) ?> · <?= e($a['barrio']) ?><?= $a['fecha_programada'] ? ' · ' . e(date('d/m/Y', strtotime((string) $a['fecha_programada']))) : '' ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label" for="ruta_id">Ruta</label>
          <select class="form-select" id="ruta_id" name="ruta_id">
            <option value="0">Sin ruta (retirar)</option>
            <?php foreach ($rutas as $r): ?>
              <option value="<?= (int) $r['id'] ?>"><?= e($r['nombre']) ?> · <?= e(date('d/m/Y', strtotime((string) $r['fecha']))) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button class="btn btn-outline-recicla w-100" type="submit"><i class="fa-solid fa-check me-1"></i>Guardar asignación</button>
      </form>
    </div>
  </div>

  <div class="col-12 col-lg-7">
    <div class="tarjeta p-3 mb-3">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="seccion-titulo mb-0">Rutas creadas</span>
        <form method="get" action="<?= e(BASE_URL) ?>/index.php" class="d-flex gap-2">
          <input type="hidden" name="p" value="e_rutas">
          <input class="form-control form-control-sm" type="date" name="fecha" value="<?= e($fecha) ?>">
          <button class="btn btn-sm btn-outline-recicla" type="submit"><i class="fa-solid fa-filter"></i></button>
        </form>
      </div>
      <?php if ($rutas): ?>
        <div class="table-responsive">
          <table class="table table-sm table-recicla mb-0">
            <thead><tr><th>Ruta</th><th>Fecha</th><th class="text-center">Solicitudes</th><th class="text-center">Recolectadas</th></tr></thead>
            <tbody>
            <?php foreach ($rutas as $r): ?>
              <tr>
                <td><strong><?= e($r['nombre']) ?></strong><div class="hint"><?= e($r['zona'] ?: '') ?> <?= e($r['ciudad']) ?></div></td>
                <td><?= e(date('d/m/Y', strtotime((string) $r['fecha']))) ?></td>
                <td class="text-center"><?= (int) $r['total_solicitudes'] ?></td>
                <td class="text-center"><?= (int) $r['recolectadas'] ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p class="hint mb-0">Aún no ha creado rutas.</p>
      <?php endif; ?>
    </div>

    <div class="tarjeta p-3">
      <span class="seccion-titulo d-block mb-2">Recolecciones agrupadas por zona y fecha</span>
      <?php if ($zonas): ?>
        <div class="table-responsive">
          <table class="table table-sm table-recicla mb-0">
            <thead><tr><th>Ciudad / barrio</th><th>Fecha programada</th><th class="text-center">Total</th><th class="text-center">Recolectadas</th></tr></thead>
            <tbody>
            <?php foreach ($zonas as $z): ?>
              <tr>
                <td><?= e($z['ciudad']) ?><div class="hint"><?= e($z['barrio']) ?></div></td>
                <td><?= $z['fecha_programada'] ? e(date('d/m/Y', strtotime((string) $z['fecha_programada']))) : '<span class="text-muted">Sin programar</span>' ?></td>
                <td class="text-center"><?= (int) $z['total'] ?></td>
                <td class="text-center"><?= (int) $z['recolectadas'] ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p class="hint mb-0">No hay solicitudes aceptadas o programadas para agrupar.</p>
      <?php endif; ?>
      <p class="hint mt-2 mb-0"><i class="fa-solid fa-circle-info me-1"></i>La integración con mapas y geolocalización está prevista para una segunda versión del sistema.</p>
    </div>
  </div>
</div>
