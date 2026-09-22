<?php /** RECICLA+ | Categorías de materiales (RF-05, RF-17) */ ?>
<h1 class="h5 fw-bold mb-1"><i class="fa-solid fa-tags texto-verde me-2"></i>Categorías</h1>
<p class="hint">Las categorías organizan los materiales que el ciudadano puede consultar.</p>

<div class="row g-3">
  <div class="col-12 col-lg-4">
    <div class="tarjeta-form">
      <span class="seccion-titulo d-block mb-2"><i class="fa-solid fa-plus me-1"></i>Nueva categoría</span>
      <form method="post" action="<?= e(url('a_categoria_post')) ?>" novalidate>
        <?= csrf_campo() ?>
        <div class="mb-2">
          <label class="form-label" for="nombre">Nombre *</label>
          <input class="form-control" id="nombre" name="nombre" required maxlength="60" placeholder="Ej: Papel y cartón">
        </div>
        <div class="mb-2">
          <label class="form-label" for="descripcion">Descripción</label>
          <input class="form-control" id="descripcion" name="descripcion" maxlength="200">
        </div>
        <div class="row g-2 mb-3">
          <div class="col-7">
            <label class="form-label" for="icono">Icono (Font Awesome)</label>
            <input class="form-control" id="icono" name="icono" maxlength="40" value="fa-recycle">
            <div class="hint">Ej: fa-scroll, fa-bottle-water, fa-gears</div>
          </div>
          <div class="col-5">
            <label class="form-label" for="color">Color</label>
            <input class="form-control form-control-color w-100" type="color" id="color" name="color" value="#2e8800">
          </div>
        </div>
        <button class="btn btn-recicla w-100" type="submit"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar categoría</button>
      </form>
    </div>
  </div>

  <div class="col-12 col-lg-8">
    <?php foreach ($categorias as $c): ?>
      <div class="tarjeta p-3 mb-2" style="border-left:.35rem solid <?= e($c['color']) ?>">
        <div class="d-flex justify-content-between align-items-start">
          <div class="d-flex gap-2">
            <div class="icono-circulo" style="background:<?= e($c['color']) ?>"><i class="fa-solid <?= e($c['icono']) ?>"></i></div>
            <div>
              <div class="fw-bold"><?= e($c['nombre']) ?></div>
              <div class="hint"><?= e((string) $c['descripcion']) ?></div>
              <div class="hint"><?= (int) $c['total_materiales'] ?> material(es) activo(s)</div>
            </div>
          </div>
          <span class="badge <?= $c['estado'] === 'activo' ? 'bg-success' : 'bg-secondary' ?>"><?= e($c['estado']) ?></span>
        </div>

        <details class="mt-2">
          <summary class="small text-verde" style="cursor:pointer">Editar categoría</summary>
          <form method="post" action="<?= e(url('a_categoria_post')) ?>" class="row g-2 mt-2">
            <?= csrf_campo() ?>
            <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
            <div class="col-12 col-md-5"><input class="form-control" name="nombre" value="<?= e($c['nombre']) ?>" required maxlength="60"></div>
            <div class="col-12 col-md-4"><input class="form-control" name="descripcion" value="<?= e((string) $c['descripcion']) ?>" maxlength="200"></div>
            <div class="col-6 col-md-2"><input class="form-control" name="icono" value="<?= e($c['icono']) ?>" maxlength="40"></div>
            <div class="col-6 col-md-1"><input class="form-control form-control-color w-100" type="color" name="color" value="<?= e($c['color']) ?>"></div>
            <div class="col-12"><button class="btn btn-sm btn-recicla" type="submit">Actualizar</button></div>
          </form>
        </details>

        <form method="post" action="<?= e(url('a_categoria_estado')) ?>" class="mt-2"
              data-confirmar="¿<?= $c['estado'] === 'activo' ? 'Desactivar' : 'Activar' ?> la categoría <?= e($c['nombre']) ?>?">
          <?= csrf_campo() ?>
          <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
          <button class="btn btn-sm btn-outline-<?= $c['estado'] === 'activo' ? 'danger' : 'success' ?>" type="submit">
            <i class="fa-solid <?= $c['estado'] === 'activo' ? 'fa-ban' : 'fa-check' ?> me-1"></i><?= $c['estado'] === 'activo' ? 'Desactivar' : 'Activar' ?>
          </button>
        </form>
      </div>
    <?php endforeach; ?>
  </div>
</div>
