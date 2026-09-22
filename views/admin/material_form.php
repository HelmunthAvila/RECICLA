<?php /** RECICLA+ | Crear / editar material (RF-17) */ ?>
<a class="btn btn-sm btn-outline-recicla mb-2" href="<?= e(url('a_materiales')) ?>"><i class="fa-solid fa-arrow-left me-1"></i> Materiales</a>
<h1 class="h5 fw-bold mb-1"><i class="fa-solid fa-recycle texto-verde me-2"></i><?= e($titulo) ?></h1>
<p class="hint">La información que registre aquí es la que consulta el ciudadano desde su celular.</p>

<form method="post" action="<?= e(url('a_material_post')) ?>" enctype="multipart/form-data" novalidate>
  <?= csrf_campo() ?>
  <input type="hidden" name="id" value="<?= (int) ($m['id'] ?? 0) ?>">

  <div class="tarjeta-form">
    <div class="row g-2">
      <div class="col-12 col-md-7">
        <label class="form-label" for="nombre">Nombre del material *</label>
        <input class="form-control form-control-lg" id="nombre" name="nombre" required maxlength="90" value="<?= e((string) ($m['nombre'] ?? '')) ?>">
      </div>
      <div class="col-6 col-md-5">
        <label class="form-label" for="categoria_id">Categoría *</label>
        <select class="form-select form-select-lg" id="categoria_id" name="categoria_id" required>
          <option value="">Seleccione…</option>
          <?php foreach ($categorias as $c): ?>
            <option value="<?= (int) $c['id'] ?>" <?= (int) ($m['categoria_id'] ?? 0) === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12">
        <label class="form-label" for="descripcion">Descripción *</label>
        <textarea class="form-control" id="descripcion" name="descripcion" rows="2" maxlength="400" required><?= e((string) ($m['descripcion'] ?? '')) ?></textarea>
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label" for="recomendaciones">Recomendaciones</label>
        <textarea class="form-control" id="recomendaciones" name="recomendaciones" rows="3"><?= e((string) ($m['recomendaciones'] ?? '')) ?></textarea>
        <div class="hint">Consejos para preparar el material antes de entregarlo.</div>
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label" for="condiciones_entrega">Condiciones para su entrega</label>
        <textarea class="form-control" id="condiciones_entrega" name="condiciones_entrega" rows="3"><?= e((string) ($m['condiciones_entrega'] ?? '')) ?></textarea>
        <div class="hint">Cómo debe entregarse: seco, atado, en caja, etc.</div>
      </div>
      <div class="col-6 col-md-4">
        <label class="form-label" for="unidad_medida">Unidad de medida *</label>
        <select class="form-select" id="unidad_medida" name="unidad_medida" required>
          <?php foreach ($unidades as $clave => $texto): ?>
            <option value="<?= e($clave) ?>" <?= ($m['unidad_medida'] ?? 'kg') === $clave ? 'selected' : '' ?>><?= e($texto) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-4">
        <label class="form-label" for="puntos_por_unidad">Puntos por unidad recolectada *</label>
        <input class="form-control" type="number" min="0" step="1" id="puntos_por_unidad" name="puntos_por_unidad" required
               value="<?= e((string) ($m['puntos_por_unidad'] ?? 10)) ?>">
        <div class="hint">Puntos que gana el ciudadano por cada <?= e(strtolower((string) ($m['unidad_medida'] ?? 'kg'))) ?> de este material.</div>
      </div>
      <div class="col-6 col-md-4">
        <label class="form-label" for="icono">Icono (Font Awesome)</label>
        <input class="form-control" id="icono" name="icono" maxlength="40" value="<?= e((string) ($m['icono'] ?? 'fa-box')) ?>">
        <div class="hint">Ej: fa-file-lines, fa-bottle-water</div>
      </div>
      <div class="col-12 col-md-4">
        <label class="form-label" for="imagen">Imagen del material</label>
        <input class="form-control" type="file" id="imagen" name="imagen" accept="image/*" data-foto data-max="<?= (int) MAX_FOTO_BYTES ?>">
        <div class="vista-previa"></div>
      </div>
    </div>
  </div>

  <?php if (!empty($m['imagen'])): ?>
    <div class="tarjeta p-3 mb-3">
      <span class="etiqueta-mini d-block mb-1">Imagen actual</span>
      <img class="miniatura" style="width:8rem" src="<?= e(urlFoto($m['imagen'])) ?>" alt="<?= e($m['nombre']) ?>">
      <div class="hint">Si carga una nueva imagen, reemplazará la actual.</div>
    </div>
  <?php endif; ?>

  <div class="d-grid gap-2">
    <button class="btn btn-recicla btn-accion" type="submit"><i class="fa-solid fa-floppy-disk me-2"></i>Guardar material</button>
    <a class="btn btn-outline-recicla btn-accion" href="<?= e(url('a_materiales')) ?>">Cancelar</a>
  </div>
</form>
