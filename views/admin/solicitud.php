<?php /** RECICLA+ | Detalle de solicitud (vista del administrador) */ ?>
<a class="btn btn-sm btn-outline-recicla mb-2" href="<?= e(url('a_solicitudes')) ?>"><i class="fa-solid fa-arrow-left me-1"></i> Solicitudes</a>

<div class="tarjeta p-3 mb-3">
  <div class="d-flex justify-content-between align-items-start">
    <div>
      <span class="etiqueta-mini">Solicitud</span>
      <h1 class="h5 fw-bold mb-0"><?= e($s['numero']) ?></h1>
      <div class="hint">Registrada el <?= e(date('d/m/Y H:i', strtotime((string) $s['created_at']))) ?></div>
    </div>
    <?= badgeEstado($s['estado']) ?>
  </div>
</div>

<div class="row g-3">
  <div class="col-12 col-lg-6">
    <div class="tarjeta p-3 h-100">
      <span class="seccion-titulo d-block mb-2"><i class="fa-solid fa-user texto-verde me-2"></i>Ciudadano</span>
      <div class="row g-2 small">
        <div class="col-12"><span class="etiqueta-mini">Nombre</span><div><?= e((string) $s['ciudadano_nombre']) ?></div></div>
        <div class="col-6"><span class="etiqueta-mini">Documento</span><div><?= e((string) $s['ciudadano_documento']) ?></div></div>
        <div class="col-6"><span class="etiqueta-mini">Teléfono</span><div><?= e((string) $s['ciudadano_telefono']) ?></div></div>
        <div class="col-12"><span class="etiqueta-mini">Correo</span><div><?= e((string) $s['ciudadano_email']) ?></div></div>
        <div class="col-12"><span class="etiqueta-mini">Dirección de recolección</span>
          <div><?= e($s['direccion']) ?> · <?= e($s['barrio']) ?>, <?= e($s['ciudad']) ?></div></div>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-6">
    <div class="tarjeta p-3 h-100">
      <span class="seccion-titulo d-block mb-2"><i class="fa-solid fa-building texto-verde me-2"></i>Empresa y programación</span>
      <div class="row g-2 small">
        <div class="col-12"><span class="etiqueta-mini">Empresa asignada</span>
          <div><?= $s['empresa_nombre'] ? e($s['empresa_nombre']) . ' (NIT ' . e((string) $s['empresa_nit']) . ')' : '<span class="text-muted">Sin asignar</span>' ?></div></div>
        <div class="col-6"><span class="etiqueta-mini">Aceptada por</span><div><?= e((string) ($s['aceptada_por'] ?: 'N/A')) ?></div></div>
        <div class="col-6"><span class="etiqueta-mini">Fecha de aceptación</span>
          <div><?= $s['fecha_aceptacion'] ? e(date('d/m/Y H:i', strtotime((string) $s['fecha_aceptacion']))) : 'N/A' ?></div></div>
        <div class="col-6"><span class="etiqueta-mini">Fecha solicitada</span>
          <div><?= e(date('d/m/Y', strtotime((string) $s['fecha_disponible']))) ?> · <?= e(etiquetaHorario($s['horario_disponible'])) ?></div></div>
        <div class="col-6"><span class="etiqueta-mini">Programada</span>
          <div><?= $s['fecha_programada'] ? e(date('d/m/Y', strtotime((string) $s['fecha_programada']))) : 'Sin programar' ?>
            <?= $s['hora_programada'] ? '· ' . e(substr((string) $s['hora_programada'], 0, 5)) : '' ?></div></div>
        <div class="col-6"><span class="etiqueta-mini">Operador</span><div><?= e((string) ($s['operador'] ?: 'N/A')) ?></div></div>
        <div class="col-6"><span class="etiqueta-mini">Vehículo / ruta</span>
          <div><?= e((string) ($s['placa'] ?: 'N/A')) ?><?= !empty($s['ruta_nombre']) ? ' · ' . e($s['ruta_nombre']) : '' ?></div></div>
        <?php if (!empty($s['observaciones_empresa'])): ?>
          <div class="col-12"><span class="etiqueta-mini">Observaciones de la empresa</span>
            <div class="bg-light rounded p-2"><?= e($s['observaciones_empresa']) ?></div></div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-6">
    <div class="tarjeta p-3 h-100">
      <span class="seccion-titulo d-block mb-2"><i class="fa-solid fa-boxes-stacked texto-verde me-2"></i>Materiales</span>
      <?php foreach ($detalle as $d): ?>
        <div class="d-flex gap-2 border-bottom py-2">
          <?php if (!empty($d['foto'])): ?>
            <a href="<?= e(urlFoto($d['foto'])) ?>" target="_blank" rel="noopener">
              <img class="miniatura" style="width:4rem" src="<?= e(urlFoto($d['foto'])) ?>" alt="Foto">
            </a>
          <?php else: ?>
            <div class="placeholder-foto" style="width:4rem;height:4rem;background:<?= e($d['categoria_color']) ?>22;color:<?= e($d['categoria_color']) ?>">
              <i class="fa-solid <?= e($d['icono']) ?>"></i>
            </div>
          <?php endif; ?>
          <div>
            <div class="fw-bold small"><?= e($d['material']) ?></div>
            <div class="hint"><?= e($d['categoria']) ?> · <?= e(formatearCantidad((float) $d['cantidad'])) ?> <?= e(unidadesMedida()[$d['unidad']] ?? $d['unidad']) ?></div>
            <?php if ($d['descripcion']): ?><div class="hint"><?= e($d['descripcion']) ?></div><?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (!empty($s['observaciones'])): ?>
        <div class="mt-2"><span class="etiqueta-mini">Observaciones del ciudadano</span>
          <div class="bg-light rounded p-2 small"><?= e($s['observaciones']) ?></div></div>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-12 col-lg-6">
    <div class="tarjeta p-3 h-100">
      <span class="seccion-titulo d-block mb-2"><i class="fa-solid fa-circle-check texto-verde me-2"></i>Recolección</span>
      <?php if ($rec): ?>
        <div class="row g-2 small">
          <div class="col-6"><span class="etiqueta-mini">Fecha y hora</span>
            <div><?= e(date('d/m/Y', strtotime((string) $rec['fecha']))) ?> · <?= e(substr((string) $rec['hora'], 0, 5)) ?></div></div>
          <div class="col-6"><span class="etiqueta-mini">Peso total</span>
            <div><?= $rec['peso_total'] !== null ? e(formatearCantidad((float) $rec['peso_total'])) . ' kg' : 'No registrado' ?></div></div>
          <div class="col-12"><span class="etiqueta-mini">Registrada por</span>
            <div><?= e($rec['nombres'] . ' ' . $rec['apellidos']) ?> (<?= e($rec['empresa_nombre']) ?>)</div></div>
          <div class="col-12"><span class="etiqueta-mini">Materiales recolectados</span>
            <ul class="mb-0 ps-3">
              <?php foreach ($recMateriales as $rm): ?>
                <li><?= e($rm['material']) ?>: <?= e(formatearCantidad((float) $rm['cantidad'])) ?> <?= e(unidadesMedida()[$rm['unidad']] ?? $rm['unidad']) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
          <?php if (!empty($rec['evidencia'])): ?>
            <div class="col-12"><span class="etiqueta-mini">Evidencia</span>
              <a href="<?= e(urlFoto($rec['evidencia'])) ?>" target="_blank" rel="noopener">
                <img class="rounded mt-1" style="max-height:12rem" src="<?= e(urlFoto($rec['evidencia'])) ?>" alt="Evidencia">
              </a>
            </div>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <p class="hint mb-0">La recolección aún no ha sido registrada por la empresa operadora.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<h2 class="seccion-titulo mt-3 mb-2">Historial de estados</h2>
<ul class="timeline tarjeta p-3">
  <?php foreach ($historial as $h): ?>
    <li>
      <div class="titulo"><?= e(etiquetaEstado((string) $h['estado_nuevo'])) ?></div>
      <div class="fecha"><?= e(date('d/m/Y H:i', strtotime((string) $h['created_at']))) ?><?= $h['usuario'] ? ' · ' . e($h['usuario']) . ' (' . e((string) $h['rol']) . ')' : '' ?></div>
      <?php if ($h['observacion']): ?><div class="hint"><?= e($h['observacion']) ?></div><?php endif; ?>
    </li>
  <?php endforeach; ?>
</ul>
