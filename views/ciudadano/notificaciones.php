<?php /** RECICLA+ | Avisos del ciudadano */ ?>
<h1 class="h5 fw-bold mb-2"><i class="fa-solid fa-bell texto-verde me-2"></i>Avisos</h1>
<?php foreach ($avisos as $a): ?>
  <div class="tarjeta p-3 mb-2">
    <div class="d-flex justify-content-between align-items-start gap-2">
      <div class="fw-semibold small"><?= e($a['titulo']) ?></div>
      <span class="hint text-nowrap"><?= e(date('d/m/Y H:i', strtotime((string) $a['created_at']))) ?></span>
    </div>
    <div class="hint"><?= e($a['mensaje']) ?></div>
    <?php if (!empty($a['url'])): ?>
      <a class="btn btn-sm btn-outline-recicla mt-2" href="<?= e($a['url']) ?>">Ver detalle <i class="fa-solid fa-arrow-right ms-1"></i></a>
    <?php endif; ?>
  </div>
<?php endforeach; ?>
<?php if (!$avisos): ?>
  <div class="tarjeta p-4 text-center">
    <i class="fa-solid fa-bell-slash fa-2x text-muted mb-2"></i>
    <p class="mb-0">No tienes avisos por ahora.</p>
  </div>
<?php endif; ?>
