<?php /** RECICLA+ | Registrar la recolección realizada (RF-14) */ ?>
<a class="btn btn-sm btn-outline-recicla mb-2" href="<?= e(url('e_solicitud', ['id' => (int) $s['id']])) ?>"><i class="fa-solid fa-arrow-left me-1"></i> Volver</a>
<h1 class="h5 fw-bold mb-1"><i class="fa-solid fa-clipboard-check texto-verde me-2"></i>Registrar recolección</h1>
<p class="hint">Solicitud <?= e($s['numero']) ?> · <?= e($s['direccion']) ?> · <?= e($s['barrio']) ?>, <?= e($s['ciudad']) ?></p>

<form method="post" action="<?= e(url('e_recoleccion_post')) ?>" enctype="multipart/form-data" novalidate>
  <?= csrf_campo() ?>
  <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">

  <div class="tarjeta-form">
    <span class="seccion-titulo d-block mb-2">1. Datos de la visita</span>
    <div class="row g-2">
      <div class="col-6">
        <label class="form-label" for="fecha">Fecha *</label>
        <input class="form-control form-control-lg" type="date" id="fecha" name="fecha" required value="<?= e(date('Y-m-d')) ?>">
      </div>
      <div class="col-6">
        <label class="form-label" for="hora">Hora *</label>
        <input class="form-control form-control-lg" type="time" id="hora" name="hora" required value="<?= e(date('H:i')) ?>">
      </div>
      <div class="col-12">
        <label class="form-label" for="operador">Operador que realizó la visita *</label>
        <input class="form-control form-control-lg" id="operador" name="operador" required maxlength="120"
               value="<?= e((string) ($s['operador'] ?: (usuarioActual()['nombres'] . ' ' . usuarioActual()['apellidos']))) ?>">
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label" for="vehiculo_id">Vehículo</label>
        <select class="form-select form-select-lg" id="vehiculo_id" name="vehiculo_id">
          <option value="0">Sin asignar</option>
          <?php foreach ($vehiculos as $v): ?>
            <option value="<?= (int) $v['id'] ?>" <?= (int) $s['vehiculo_id'] === (int) $v['id'] ? 'selected' : '' ?>>
              <?= e($v['placa']) ?> · <?= e($v['tipo']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label" for="ruta_id">Ruta</label>
        <select class="form-select form-select-lg" id="ruta_id" name="ruta_id">
          <option value="0">Sin ruta</option>
          <?php foreach ($rutas as $r): ?>
            <option value="<?= (int) $r['id'] ?>" <?= (int) $s['ruta_id'] === (int) $r['id'] ? 'selected' : '' ?>>
              <?= e($r['nombre']) ?> · <?= e($r['zona'] ?: $r['ciudad']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
  </div>

  <div class="tarjeta-form">
    <span class="seccion-titulo d-block mb-2">2. Cantidades realmente recolectadas</span>
    <p class="hint">El ciudadano publicó estas cantidades. Ajuste lo que efectivamente recogió.</p>
    <?php foreach ($detalle as $d): ?>
      <div class="row g-2 align-items-end mb-2">
        <div class="col-7">
          <label class="form-label" for="cant-<?= (int) $d['material_id'] ?>"><?= e($d['material']) ?></label>
          <input class="form-control form-control-lg" type="number" step="0.01" min="0" inputmode="decimal"
                 id="cant-<?= (int) $d['material_id'] ?>" name="cant_<?= (int) $d['material_id'] ?>"
                 value="<?= e(formatearCantidad((float) $d['cantidad'])) ?>">
        </div>
        <div class="col-5">
          <span class="hint d-block"><?= e(unidadesMedida()[$d['unidad']] ?? $d['unidad']) ?></span>
          <span class="hint">Publicado: <?= e(formatearCantidad((float) $d['cantidad'])) ?></span>
        </div>
      </div>
    <?php endforeach; ?>

    <div class="mb-2 mt-3">
      <label class="form-label" for="peso_total">Peso total recolectado (kg)</label>
      <input class="form-control form-control-lg" type="number" step="0.01" min="0" inputmode="decimal" id="peso_total" name="peso_total" placeholder="Se calcula automáticamente si lo deja vacío">
      <div class="hint">Si no registra peso, el sistema sumará las cantidades expresadas en kilogramos.</div>
    </div>
  </div>

  <div class="tarjeta-form">
    <span class="seccion-titulo d-block mb-2">3. Evidencia y observaciones</span>
    <label class="form-label" for="evidencia"><i class="fa-solid fa-camera me-1"></i>Fotografía de evidencia</label>
    <input class="form-control form-control-lg" type="file" id="evidencia" name="evidencia" accept="image/*" data-foto data-max="<?= (int) MAX_FOTO_BYTES ?>">
    <div class="hint">Tome la foto del material recolectado con la cámara del celular.
      Máximo <?= e(number_format(MAX_FOTO_BYTES / 1048576, 0)) ?> MB (JPG, PNG o WEBP).</div>
    <div class="vista-previa"></div>

    <div class="mt-3">
      <label class="form-label" for="observaciones">Observaciones</label>
      <textarea class="form-control" id="observaciones" name="observaciones" rows="3" maxlength="400" placeholder="Novedades de la visita, material no apto, etc."></textarea>
    </div>
  </div>

  <div class="alert alert-warning small">
    <i class="fa-solid fa-circle-info me-1"></i>Al guardar, la solicitud pasará al estado <strong>RECOLECTADA</strong> y el ciudadano recibirá una notificación.
  </div>

  <button class="btn btn-recicla btn-accion w-100" type="submit" data-confirmar="¿Confirmar que la recolección fue realizada?">
    <i class="fa-solid fa-circle-check me-2"></i>Confirmar recolección
  </button>
</form>
