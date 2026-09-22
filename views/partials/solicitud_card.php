<?php
/** RECICLA+ | Tarjeta reutilizable de solicitud
 *  Requiere: $s (fila de solicitud) y $rutaDetalle (nombre de la ruta de detalle).
 *  Opcional: $mostrarCiudadano (bool)
 */
$mostrarCiudadano = $mostrarCiudadano ?? false;
$campos = $campos ?? ['materiales' => true];
?>
<div class="tarjeta p-3 mb-2">
  <div class="d-flex justify-content-between align-items-start gap-2">
    <div>
      <div class="fw-bold"><?= e($s['numero']) ?></div>
      <div class="hint">
        <i class="fa-solid fa-location-dot me-1"></i><?= e($s['barrio']) ?>, <?= e($s['ciudad']) ?>
      </div>
    </div>
    <?= badgeEstado($s['estado']) ?>
  </div>

  <?php if ($mostrarCiudadano && !empty($s['ciudadano_nombre'])): ?>
    <div class="hint mt-1"><i class="fa-solid fa-user me-1"></i><?= e($s['ciudadano_nombre']) ?>
      <?= !empty($s['ciudadano_telefono']) ? '· <i class="fa-solid fa-phone me-1"></i>' . e($s['ciudadano_telefono']) : '' ?>
    </div>
  <?php endif; ?>

  <div class="row g-1 mt-2 small">
    <div class="col-6">
      <span class="etiqueta-mini">Fecha solicitada</span>
      <div><?= e(date('d/m/Y', strtotime((string) $s['fecha_disponible']))) ?> · <?= e(etiquetaHorario($s['horario_disponible'])) ?></div>
    </div>
    <?php if (!empty($s['fecha_programada'])): ?>
      <div class="col-6">
        <span class="etiqueta-mini">Programada</span>
        <div><i class="fa-solid fa-calendar-check texto-verde me-1"></i><?= e(date('d/m/Y', strtotime((string) $s['fecha_programada']))) ?>
          <?= !empty($s['hora_programada']) ? e(substr((string) $s['hora_programada'], 0, 5)) : '' ?></div>
      </div>
    <?php endif; ?>
    <?php if (!empty($s['total_materiales'])): ?>
      <div class="col-6">
        <span class="etiqueta-mini">Materiales</span>
        <div><?= (int) $s['total_materiales'] ?> tipo(s)<?= (float) ($s['total_kg'] ?? 0) > 0 ? ' · ' . e(formatearCantidad((float) $s['total_kg'])) . ' kg' : '' ?></div>
      </div>
    <?php endif; ?>
    <?php if (!empty($s['empresa_nombre'])): ?>
      <div class="col-6">
        <span class="etiqueta-mini">Empresa asignada</span>
        <div><i class="fa-solid fa-building me-1"></i><?= e($s['empresa_nombre']) ?></div>
      </div>
    <?php endif; ?>
    <?php if (!empty($pesoRecolectado)): ?>
      <div class="col-6">
        <span class="etiqueta-mini">Material recolectado</span>
        <div class="fw-semibold texto-verde"><i class="fa-solid fa-weight-hanging me-1"></i><?= e(formatearCantidad((float) $pesoRecolectado)) ?> kg</div>
      </div>
    <?php endif; ?>
  </div>

  <a class="btn btn-sm btn-outline-recicla w-100 mt-3" href="<?= e(url($rutaDetalle, ['id' => (int) $s['id']])) ?>">
    Ver detalle <i class="fa-solid fa-arrow-right ms-1"></i>
  </a>
</div>
