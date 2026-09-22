<?php /** RECICLA+ | Error interno */ ?>
<div class="tarjeta p-4">
  <h1 class="h4 fw-bold text-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i>Ocurrió un error</h1>
  <p class="mb-2">El sistema no pudo completar la operación solicitada. El detalle quedó registrado en el log del servidor.</p>
  <?php if (!empty($mensaje) && (($_SESSION['usuario_id'] ?? 0) === 1)): ?>
    <pre class="bg-light p-3 rounded small"><?= e((string) $mensaje) ?></pre>
  <?php endif; ?>
  <div class="d-grid gap-2 col-12 col-md-6 mt-3">
    <a class="btn btn-recicla btn-accion" href="<?= e(url('inicio')) ?>"><i class="fa-solid fa-house me-1"></i> Ir al inicio</a>
  </div>
</div>
