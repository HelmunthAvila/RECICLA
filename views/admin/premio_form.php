<?php /** RECICLA+ | Crear / editar premio del programa de incentivos */ ?>
<a class="btn btn-sm btn-outline-recicla mb-2" href="<?= e(url('a_premios')) ?>"><i class="fa-solid fa-arrow-left me-1"></i> Premios</a>
<h1 class="h5 fw-bold mb-1"><i class="fa-solid fa-gift texto-verde me-2"></i><?= e($titulo) ?></h1>
<p class="hint">Los puntos requeridos definen qué tan difícil es alcanzar el premio. El stock se descuenta con cada canje.</p>

<form method="post" action="<?= e(url('a_premio_post')) ?>" enctype="multipart/form-data" novalidate>
  <?= csrf_campo() ?>
  <input type="hidden" name="id" value="<?= (int) ($p['id'] ?? 0) ?>">

  <div class="tarjeta-form">
    <div class="row g-2">
      <div class="col-12">
        <label class="form-label" for="nombre">Nombre del premio *</label>
        <input class="form-control form-control-lg" id="nombre" name="nombre" required maxlength="120" value="<?= e((string) ($p['nombre'] ?? '')) ?>">
      </div>
      <div class="col-12">
        <label class="form-label" for="descripcion">Descripción</label>
        <textarea class="form-control" id="descripcion" name="descripcion" rows="2" maxlength="300"><?= e((string) ($p['descripcion'] ?? '')) ?></textarea>
      </div>
      <div class="col-6 col-md-4">
        <label class="form-label" for="puntos_requeridos">Puntos requeridos *</label>
        <input class="form-control form-control-lg" type="number" min="1" step="1" id="puntos_requeridos" name="puntos_requeridos" required
               value="<?= e((string) ($p['puntos_requeridos'] ?? 100)) ?>">
        <div class="hint">Ej.: 150 puntos = 15 kg de papel o 5 kg de aluminio.</div>
      </div>
      <div class="col-6 col-md-4">
        <label class="form-label" for="stock">Unidades disponibles *</label>
        <input class="form-control form-control-lg" type="number" min="0" step="1" id="stock" name="stock" required
               value="<?= e((string) ($p['stock'] ?? 10)) ?>">
        <div class="hint">0 = agotado.</div>
      </div>
      <div class="col-12 col-md-4">
        <label class="form-label" for="icono">Icono (Font Awesome)</label>
        <input class="form-control" id="icono" name="icono" maxlength="40" value="<?= e((string) ($p['icono'] ?? 'fa-gift')) ?>">
        <div class="hint">Ej.: fa-bottle-water, fa-bicycle, fa-tree</div>
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label" for="imagen">Imagen del premio</label>
        <input class="form-control" type="file" id="imagen" name="imagen" accept="image/*" data-foto data-max="<?= (int) MAX_FOTO_BYTES ?>">
        <div class="vista-previa"></div>
      </div>
    </div>
  </div>

  <div class="d-grid gap-2">
    <button class="btn btn-recicla btn-accion" type="submit"><i class="fa-solid fa-floppy-disk me-2"></i>Guardar premio</button>
    <a class="btn btn-outline-recicla btn-accion" href="<?= e(url('a_premios')) ?>">Cancelar</a>
  </div>
</form>
