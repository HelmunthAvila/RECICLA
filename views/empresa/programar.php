<?php /** RECICLA+ | Programar la recolección (RF-13) */ ?>
<a class="btn btn-sm btn-outline-recicla mb-2" href="<?= e(url('e_solicitud', ['id' => (int) $s['id']])) ?>"><i class="fa-solid fa-arrow-left me-1"></i> Volver</a>
<h1 class="h5 fw-bold mb-1"><i class="fa-solid fa-calendar-plus texto-verde me-2"></i>Programar recolección</h1>
<p class="hint">Solicitud <?= e($s['numero']) ?> · <?= e($s['barrio']) ?>, <?= e($s['ciudad']) ?></p>

<form method="post" action="<?= e(url('e_programar_post')) ?>" novalidate>
  <?= csrf_campo() ?>
  <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">

  <div class="tarjeta-form">
    <div class="row g-2">
      <div class="col-6">
        <label class="form-label" for="fecha_programada">Fecha de la visita *</label>
        <input class="form-control form-control-lg" type="date" id="fecha_programada" name="fecha_programada" required
               min="<?= e(date('Y-m-d')) ?>" value="<?= e((string) ($s['fecha_programada'] ?: $s['fecha_disponible'])) ?>">
      </div>
      <div class="col-6">
        <label class="form-label" for="hora_programada">Hora *</label>
        <input class="form-control form-control-lg" type="time" id="hora_programada" name="hora_programada" required
               value="<?= e($s['hora_programada'] ? substr((string) $s['hora_programada'], 0, 5) : '09:00') ?>">
      </div>
      <div class="col-12">
        <label class="form-label" for="operador">Operador / conductor *</label>
        <input class="form-control form-control-lg" id="operador" name="operador" required maxlength="120"
               value="<?= e((string) $s['operador']) ?>" placeholder="Nombre de quien realizará la visita">
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label" for="vehiculo_id">Vehículo</label>
        <select class="form-select form-select-lg" id="vehiculo_id" name="vehiculo_id">
          <option value="0">Sin asignar</option>
          <?php foreach ($vehiculos as $v): ?>
            <option value="<?= (int) $v['id'] ?>" <?= (int) $s['vehiculo_id'] === (int) $v['id'] ? 'selected' : '' ?>>
              <?= e($v['placa']) ?> · <?= e($v['tipo']) ?><?= $v['capacidad'] ? ' · ' . e($v['capacidad']) : '' ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label" for="ruta_id">Ruta / recorrido</label>
        <select class="form-select form-select-lg" id="ruta_id" name="ruta_id">
          <option value="0">Sin ruta</option>
          <?php foreach ($rutas as $r): ?>
            <option value="<?= (int) $r['id'] ?>" <?= (int) $s['ruta_id'] === (int) $r['id'] ? 'selected' : '' ?>>
              <?= e($r['nombre']) ?> · <?= e($r['zona'] ?: $r['ciudad']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12">
        <label class="form-label" for="observaciones_empresa">Observaciones para el ciudadano</label>
        <textarea class="form-control" id="observaciones_empresa" name="observaciones_empresa" rows="3" maxlength="400"><?= e((string) $s['observaciones_empresa']) ?></textarea>
      </div>
    </div>
  </div>

  <div class="tarjeta p-3 mb-3">
    <div class="etiqueta-mini mb-1">Materiales que se recogerán</div>
    <ul class="mb-0 small ps-3">
      <?php foreach ($detalle as $d): ?>
        <li><?= e($d['material']) ?> · <?= e(formatearCantidad((float) $d['cantidad'])) ?> <?= e(unidadesMedida()[$d['unidad']] ?? $d['unidad']) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>

  <button class="btn btn-recicla btn-accion w-100" type="submit"><i class="fa-solid fa-calendar-check me-2"></i>Programar y notificar al ciudadano</button>
</form>
