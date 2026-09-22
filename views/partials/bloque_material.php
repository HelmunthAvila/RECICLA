<?php
/** RECICLA+ | Bloque de un material dentro del formulario de publicación (RF-06, RF-07)
 *  Requiere: $materiales, $unidades, $indice, $preSel
 */
$indice = $indice ?? 1;
$preSel = $preSel ?? 0;
?>
<div class="bloque-material border rounded-3 p-3 mb-3 bg-white">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <span class="badge bg-verde-claro text-dark"><i class="fa-solid fa-box me-1"></i>Material <span class="numero-material"><?= (int) $indice ?></span></span>
    <button type="button" class="btn btn-sm btn-outline-danger quitar-material" title="Quitar este material"><i class="fa-solid fa-trash-can"></i></button>
  </div>

  <div class="mb-2">
    <label class="form-label">Tipo de material *</label>
    <select class="form-select form-select-lg" name="material_id[]">
      <option value="">Seleccione el material…</option>
      <?php foreach ($materiales as $m): ?>
        <option value="<?= (int) $m['id'] ?>" data-unidad="<?= e($m['unidad_medida']) ?>" <?= (int) $preSel === (int) $m['id'] ? 'selected' : '' ?>>
          <?= e($m['nombre']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="row g-2 mb-2">
    <div class="col-7">
      <label class="form-label">Cantidad aproximada *</label>
      <input class="form-control form-control-lg" type="number" step="0.01" min="0.01" name="cantidad[]" inputmode="decimal" placeholder="0">
    </div>
    <div class="col-5">
      <label class="form-label">Unidad</label>
      <select class="form-select form-select-lg" name="unidad[]">
        <?php foreach ($unidades as $clave => $texto): ?>
          <option value="<?= e($clave) ?>"><?= e($texto) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="mb-2">
    <label class="form-label">Descripción del material</label>
    <input class="form-control" type="text" name="descripcion[]" maxlength="300" placeholder="Ej: 3 cajas de cartón seco de 40x40 cm">
  </div>

  <div class="mb-2">
    <label class="form-label">Observaciones para el operador</label>
    <input class="form-control" type="text" name="observaciones_material[]" maxlength="300" placeholder="Ej: dejar en la portería, hay perro">
  </div>

  <label class="form-label" for="foto-<?= (int) $indice ?>"><i class="fa-solid fa-camera me-1"></i>Fotografía (opcional)</label>
  <input class="form-control" type="file" id="foto-<?= (int) $indice ?>" name="foto[]" accept="image/*" data-foto data-max="<?= (int) MAX_FOTO_BYTES ?>">
  <div class="hint">Puedes tomar la foto con la cámara del celular o elegir una de tu galería.
    Máximo <?= e(number_format(MAX_FOTO_BYTES / 1048576, 0)) ?> MB (JPG, PNG o WEBP).</div>
  <div class="vista-previa"></div>
</div>
