<?php /** RECICLA+ | Publicar materiales y solicitar recolección (RF-06, RF-07, RF-08) */ ?>
<a class="btn btn-sm btn-outline-recicla mb-2" href="<?= e(url('panel')) ?>"><i class="fa-solid fa-arrow-left me-1"></i> Volver</a>
<h1 class="h5 fw-bold mb-1"><i class="fa-solid fa-circle-plus texto-verde me-2"></i>Quiero reciclar</h1>
<p class="hint">Registra los materiales que vas a entregar y solicita que los recojan en tu domicilio. El sistema generará un número de solicitud (REC-000001).</p>

<form method="post" action="<?= e(url('publicar_post')) ?>" enctype="multipart/form-data" novalidate id="form-publicar">
  <?= csrf_campo() ?>

  <div class="tarjeta-form">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <span class="seccion-titulo"><span class="badge bg-verde-claro text-dark me-1">1</span>Materiales a entregar</span>
      <span class="hint">Máx. <?= (int) $maxFilas ?></span>
    </div>

    <div id="lista-materiales" data-max="<?= (int) $maxFilas ?>">
      <?php $indice = 1; $preSel = get_int('material'); require BASE_PATH . '/views/partials/bloque_material.php'; ?>
    </div>

    <button type="button" class="btn btn-outline-recicla w-100" id="agregar-material">
      <i class="fa-solid fa-plus me-1"></i> Agregar otro material
    </button>
  </div>

  <template id="plantilla-material">
    <?php $indice = 1; $preSel = 0; require BASE_PATH . '/views/partials/bloque_material.php'; ?>
  </template>

  <div class="tarjeta-form">
    <span class="seccion-titulo d-block mb-2"><span class="badge bg-verde-claro text-dark me-1">2</span>¿Dónde y cuándo recogemos?</span>

    <div class="mb-2">
      <label class="form-label" for="direccion">Dirección *</label>
      <input class="form-control form-control-lg" id="direccion" name="direccion" required maxlength="160"
             value="<?= e($u['direccion'] ?? '') ?>" placeholder="Carrera 33 # 45-12, apto 201">
    </div>
    <div class="row g-2 mb-2">
      <div class="col-6">
        <label class="form-label" for="barrio">Barrio *</label>
        <input class="form-control form-control-lg" id="barrio" name="barrio" required maxlength="60" value="<?= e($u['barrio'] ?? '') ?>">
      </div>
      <div class="col-6">
        <label class="form-label" for="ciudad">Ciudad *</label>
        <input class="form-control form-control-lg" id="ciudad" name="ciudad" required maxlength="60" list="lista-ciudades" value="<?= e($u['ciudad'] ?? '') ?>">
        <datalist id="lista-ciudades">
          <?php foreach ($ciudades as $c): ?><option value="<?= e($c) ?>"><?php endforeach; ?>
        </datalist>
      </div>
    </div>
    <div class="row g-2 mb-2">
      <div class="col-6">
        <label class="form-label" for="fecha_disponible">Fecha disponible *</label>
        <input class="form-control form-control-lg" type="date" id="fecha_disponible" name="fecha_disponible" required
               min="<?= e(date('Y-m-d')) ?>" value="<?= e(date('Y-m-d', strtotime('+1 day'))) ?>">
      </div>
      <div class="col-6">
        <label class="form-label" for="horario_disponible">Horario *</label>
        <select class="form-select form-select-lg" id="horario_disponible" name="horario_disponible" required>
          <?php foreach ($horarios as $clave => $texto): ?>
            <option value="<?= e($clave) ?>" <?= $clave === 'indiferente' ? 'selected' : '' ?>><?= e($texto) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="mb-1">
      <label class="form-label" for="observaciones">Observaciones generales</label>
      <textarea class="form-control" id="observaciones" name="observaciones" rows="3" maxlength="400"
                placeholder="Ej: el material está en el patio, avisar antes de llegar"></textarea>
    </div>
  </div>

  <div class="alert alert-light border small">
    <i class="fa-solid fa-circle-info texto-verde me-1"></i>
    Al enviar la solicitud, las empresas operadoras de tu ciudad podrán verla, aceptarla y programar la visita.
  </div>

  <button class="btn btn-recicla btn-accion w-100" type="submit">
    <i class="fa-solid fa-paper-plane me-2"></i>Solicitar recolección
  </button>
</form>
