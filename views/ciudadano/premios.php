<?php /** RECICLA+ | Catálogo de premios para cambiar los puntos */ ?>
<a class="btn btn-sm btn-outline-recicla mb-2" href="<?= e(url('mis_puntos')) ?>"><i class="fa-solid fa-arrow-left me-1"></i> Mis puntos</a>
<h1 class="h5 fw-bold mb-1"><i class="fa-solid fa-gift texto-verde me-2"></i>Premios disponibles</h1>
<p class="hint">Tus puntos se cambian por premios de este catálogo. Al canjear, un administrador coordinará la entrega.</p>

<div class="tarjeta p-3 mb-3 d-flex align-items-center justify-content-between">
  <div>
    <div class="etiqueta-mini">Saldo disponible</div>
    <div class="h2 mb-0 texto-verde fw-bold"><?= number_format($saldo, 0, ',', '.') ?> pts</div>
  </div>
  <a class="btn btn-outline-recicla" href="<?= e(url('publicar')) ?>"><i class="fa-solid fa-recycle me-1"></i>Ganar más</a>
</div>

<?php if (!$puedeCanjear): ?>
  <div class="alert alert-warning small">
    <i class="fa-solid fa-circle-info me-1"></i>Necesitas mínimo <?= number_format($minimos, 0, ',', '.') ?> puntos para realizar tu primer canje.
  </div>
<?php endif; ?>

<?php foreach ($premios as $p): ?>
  <?php
  $alcanza = $saldo >= (int) $p['puntos_requeridos'];
  $agotado = (int) $p['stock'] <= 0;
  ?>
  <div class="tarjeta p-3 mb-2">
    <div class="d-flex gap-3">
      <?php if (!empty($p['imagen'])): ?>
        <img class="miniatura" style="width:5rem" src="<?= e(urlFoto($p['imagen'])) ?>" alt="<?= e($p['nombre']) ?>">
      <?php else: ?>
        <div class="icono-circulo <?= $alcanza ? '' : 'icono-suave' ?>" style="width:3.6rem;height:3.6rem;font-size:1.5rem">
          <i class="fa-solid <?= e($p['icono']) ?>"></i>
        </div>
      <?php endif; ?>
      <div class="flex-grow-1">
        <div class="d-flex justify-content-between align-items-start">
          <div class="fw-bold"><?= e($p['nombre']) ?></div>
          <span class="badge <?= $agotado ? 'bg-secondary' : ($alcanza ? 'bg-success' : 'bg-warning text-dark') ?>">
            <?= number_format((int) $p['puntos_requeridos'], 0, ',', '.') ?> pts
          </span>
        </div>
        <div class="hint"><?= e((string) $p['descripcion']) ?></div>
        <div class="hint">
          <?php if ($agotado): ?>
            <i class="fa-solid fa-ban me-1"></i>Agotado por ahora
          <?php elseif ($alcanza): ?>
            <i class="fa-solid fa-circle-check texto-verde me-1"></i>¡Ya puedes canjearlo!
            <?php if ((int) $p['stock'] <= 10): ?><span class="text-danger"> Últimas <?= (int) $p['stock'] ?> unidades</span><?php endif; ?>
          <?php else: ?>
            <i class="fa-solid fa-hourglass-half me-1"></i>Te faltan <?= number_format((int) $p['puntos_requeridos'] - $saldo, 0, ',', '.') ?> puntos
          <?php endif; ?>
        </div>
      </div>
    </div>

    <?php if (!$agotado): ?>
      <form method="post" action="<?= e(url('canjear')) ?>" class="mt-2"
            data-confirmar="¿Canjear <?= e($p['nombre']) ?> por <?= number_format((int) $p['puntos_requeridos'], 0, ',', '.') ?> puntos?">
        <?= csrf_campo() ?>
        <input type="hidden" name="premio_id" value="<?= (int) $p['id'] ?>">
        <button class="btn <?= $alcanza ? 'btn-recicla' : 'btn-outline-recicla' ?> btn-accion w-100" type="submit" <?= $alcanza ? '' : 'disabled' ?>>
          <i class="fa-solid fa-gift me-2"></i>Canjear por <?= number_format((int) $p['puntos_requeridos'], 0, ',', '.') ?> puntos
        </button>
      </form>
    <?php endif; ?>
  </div>
<?php endforeach; ?>

<?php if (!$premios): ?>
  <div class="tarjeta p-4 text-center">
    <i class="fa-solid fa-gift fa-2x text-muted mb-2"></i>
    <p class="mb-0">El catálogo de premios está vacío por ahora. Sigue reciclando y acumula puntos.</p>
  </div>
<?php endif; ?>

<?php if ($canjes): ?>
  <h2 class="seccion-titulo mt-4 mb-2">Mis últimos canjes</h2>
  <?php foreach ($canjes as $c): ?>
    <div class="tarjeta p-3 mb-2 d-flex justify-content-between align-items-start">
      <div>
        <div class="fw-semibold small"><?= e($c['premio']) ?></div>
        <div class="hint"><?= e($c['codigo']) ?> · <?= e(date('d/m/Y', strtotime((string) $c['created_at']))) ?></div>
      </div>
      <span class="badge <?= $c['estado'] === 'solicitado' ? 'bg-warning text-dark' : ($c['estado'] === 'entregado' ? 'bg-success' : 'bg-secondary') ?>"><?= e(ucfirst($c['estado'])) ?></span>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
