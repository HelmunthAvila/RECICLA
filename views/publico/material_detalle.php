<?php /** RECICLA+ | Detalle de un material (RF-04) */ ?>
<a class="btn btn-sm btn-outline-recicla mb-2" href="<?= e(url('materiales', ['categoria' => (int) $m['categoria_id']])) ?>">
  <i class="fa-solid fa-arrow-left me-1"></i> Volver
</a>

<div class="tarjeta p-3 mb-3">
  <?php if (!empty($m['imagen'])): ?>
    <img class="foto-material mb-3" src="<?= e(urlFoto($m['imagen'])) ?>" alt="<?= e($m['nombre']) ?>">
  <?php else: ?>
    <div class="placeholder-foto mb-3" style="height:9.5rem;background:<?= e($m['categoria_color']) ?>22;color:<?= e($m['categoria_color']) ?>;font-size:2.6rem">
      <i class="fa-solid <?= e($m['icono']) ?>"></i>
    </div>
  <?php endif; ?>

  <span class="badge rounded-pill mb-2" style="background:<?= e($m['categoria_color']) ?>"><?= e($m['categoria']) ?></span>
  <h1 class="h5 fw-bold mb-1"><?= e($m['nombre']) ?></h1>
  <p class="mb-3"><?= e((string) $m['descripcion']) ?></p>

  <div class="row g-2 small">
    <div class="col-12">
      <div class="bg-verde-claro rounded p-3">
        <div class="etiqueta-mini mb-1"><i class="fa-solid fa-lightbulb me-1"></i>Recomendaciones</div>
        <?= e((string) ($m['recomendaciones'] ?: 'Sin recomendaciones adicionales.')) ?>
      </div>
    </div>
    <div class="col-12">
      <div class="bg-light rounded p-3">
        <div class="etiqueta-mini mb-1"><i class="fa-solid fa-clipboard-check me-1"></i>Condiciones para su entrega</div>
        <?= e((string) ($m['condiciones_entrega'] ?: 'Entregar limpio y seco.')) ?>
      </div>
    </div>
    <div class="col-6">
      <div class="etiqueta-mini">Unidad de medida</div>
      <div class="fw-bold"><?= e(unidadesMedida()[$m['unidad_medida']] ?? $m['unidad_medida']) ?></div>
    </div>
    <div class="col-6">
      <div class="etiqueta-mini">Categoría</div>
      <div class="fw-bold"><?= e($m['categoria']) ?></div>
    </div>
  </div>
</div>

<div class="d-grid gap-2 mb-3">
  <?php if (estaLogueado() && rolActual() === 'ciudadano'): ?>
    <a class="btn btn-recicla btn-accion" href="<?= e(url('publicar', ['material' => (int) $m['id']])) ?>">
      <i class="fa-solid fa-circle-plus me-2"></i>Quiero reciclar este material
    </a>
  <?php else: ?>
    <a class="btn btn-recicla btn-accion" href="<?= e(url('registro')) ?>">
      <i class="fa-solid fa-user-plus me-2"></i>Registrarme y solicitar recolección
    </a>
    <a class="btn btn-outline-recicla btn-accion" href="<?= e(url('login')) ?>">
      <i class="fa-solid fa-right-to-bracket me-2"></i>Ya tengo cuenta
    </a>
  <?php endif; ?>
</div>

<?php if (!empty($relacionados)): ?>
  <h2 class="seccion-titulo mb-2">Otros materiales de <?= e($m['categoria']) ?></h2>
  <div class="row g-2">
    <?php foreach ($relacionados as $r): ?>
      <?php if ((int) $r['id'] === (int) $m['id']) { continue; } ?>
      <div class="col-12 col-md-6">
        <a class="tarjeta p-2 d-flex gap-2 align-items-center text-dark" href="<?= e(url('material', ['id' => (int) $r['id']])) ?>">
          <div class="placeholder-foto" style="width:3.5rem;height:3.5rem;background:<?= e($r['categoria_color']) ?>22;color:<?= e($r['categoria_color']) ?>">
            <i class="fa-solid <?= e($r['icono']) ?>"></i>
          </div>
          <div class="flex-grow-1">
            <div class="fw-bold small"><?= e($r['nombre']) ?></div>
            <div class="hint text-truncate"><?= e((string) $r['descripcion']) ?></div>
          </div>
        </a>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
