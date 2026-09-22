<?php /** RECICLA+ | Mis solicitudes (RF-09) */ ?>
<div class="d-flex justify-content-between align-items-center mb-2">
  <h1 class="h5 fw-bold mb-0"><i class="fa-solid fa-clipboard-list texto-verde me-2"></i>Mis solicitudes</h1>
  <a class="btn btn-sm btn-recicla" href="<?= e(url('publicar')) ?>"><i class="fa-solid fa-plus me-1"></i>Nueva</a>
</div>

<form class="tarjeta-form py-2" method="get" action="<?= e(BASE_URL) ?>/index.php">
  <input type="hidden" name="p" value="mis_solicitudes">
  <label class="form-label" for="estado">Filtrar por estado</label>
  <select class="form-select" name="estado" id="estado" data-auto-filtro>
    <option value="">Todos los estados</option>
    <?php foreach (estadosSolicitud() as $clave => $cfg): ?>
      <option value="<?= e($clave) ?>" <?= ($filtros['estado'] ?? '') === $clave ? 'selected' : '' ?>><?= e($cfg['label']) ?></option>
    <?php endforeach; ?>
  </select>
</form>

<p class="hint"><?= (int) $res['pag']['total'] ?> solicitud(es).</p>

<?php $rutaDetalle = 'mi_solicitud'; ?>
<?php foreach ($res['filas'] as $s): ?>
  <?php require BASE_PATH . '/views/partials/solicitud_card.php'; ?>
<?php endforeach; ?>

<?php if (!$res['filas']): ?>
  <div class="tarjeta p-4 text-center">
    <i class="fa-solid fa-inbox fa-2x text-muted mb-2"></i>
    <p class="mb-2">No hay solicitudes con ese criterio.</p>
    <a class="btn btn-recicla btn-accion" href="<?= e(url('publicar')) ?>"><i class="fa-solid fa-circle-plus me-2"></i>Registrar material</a>
  </div>
<?php endif; ?>

<?= paginador($res['pag'], 'mis_solicitudes', ['estado' => $filtros['estado'] ?? '']) ?>
