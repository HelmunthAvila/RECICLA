<?php /** RECICLA+ | Detalle de la solicitud del ciudadano (RF-09, RF-10) */ ?>
<a class="btn btn-sm btn-outline-recicla mb-2" href="<?= e(url('mis_solicitudes')) ?>"><i class="fa-solid fa-arrow-left me-1"></i> Mis solicitudes</a>

<div class="tarjeta p-3 mb-3">
  <div class="d-flex justify-content-between align-items-start">
    <div>
      <span class="etiqueta-mini">Número de solicitud</span>
      <h1 class="h5 fw-bold mb-0"><?= e($s['numero']) ?></h1>
    </div>
    <?= badgeEstado($s['estado']) ?>
  </div>

  <?php
  $flujo = ['REGISTRADA', 'EN_REVISION', 'ACEPTADA', 'PROGRAMADA', 'EN_RUTA', 'RECOLECTADA'];
  $indiceActual = array_search($s['estado'], $flujo, true);
  ?>
  <?php if ($s['estado'] === 'CANCELADA'): ?>
    <div class="linea-estados mt-3"><span class="paso cancelado"><i class="fa-solid fa-ban me-1"></i>Solicitud cancelada</span></div>
  <?php else: ?>
    <div class="linea-estados mt-3">
      <?php foreach ($flujo as $i => $paso): ?>
        <span class="paso <?= $indiceActual === false ? '' : ($i < $indiceActual ? 'hecho' : ($i === $indiceActual ? 'actual' : '')) ?>">
          <?= $i < $indiceActual ? '<i class="fa-solid fa-check me-1"></i>' : '' ?><?= e(etiquetaEstado($paso)) ?>
        </span>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if (!in_array($s['estado'], ['RECOLECTADA', 'CANCELADA'], true)): ?>
    <div class="alert alert-light border small mt-3 mb-0">
      <i class="fa-solid fa-star texto-verde me-1"></i>Cuando la empresa confirme la recolección ganarás aproximadamente
      <strong><?= number_format((int) $puntosEstimados, 0, ',', '.') ?> puntos</strong> RECICLA+ que podrás cambiar por premios.
    </div>
  <?php endif; ?>

  <div class="row g-2 small mt-3">
    <div class="col-12">
      <span class="etiqueta-mini">Dirección de recolección</span>
      <div><i class="fa-solid fa-location-dot texto-verde me-1"></i><?= e($s['direccion']) ?> · <?= e($s['barrio']) ?>, <?= e($s['ciudad']) ?></div>
    </div>
    <div class="col-6">
      <span class="etiqueta-mini">Fecha solicitada</span>
      <div><?= e(date('d/m/Y', strtotime((string) $s['fecha_disponible']))) ?></div>
    </div>
    <div class="col-6">
      <span class="etiqueta-mini">Horario</span>
      <div><?= e(etiquetaHorario($s['horario_disponible'])) ?></div>
    </div>
    <div class="col-6">
      <span class="etiqueta-mini">Empresa asignada</span>
      <div><?= $s['empresa_nombre'] ? '<i class="fa-solid fa-building me-1"></i>' . e($s['empresa_nombre']) : '<span class="text-muted">Pendiente de asignar</span>' ?></div>
    </div>
    <div class="col-6">
      <span class="etiqueta-mini">Fecha programada</span>
      <div>
        <?php if ($s['fecha_programada']): ?>
          <i class="fa-solid fa-calendar-check texto-verde me-1"></i><?= e(date('d/m/Y', strtotime((string) $s['fecha_programada']))) ?>
          <?= $s['hora_programada'] ? '· ' . e(substr((string) $s['hora_programada'], 0, 5)) : '' ?>
        <?php else: ?>
          <span class="text-muted">Sin programar</span>
        <?php endif; ?>
      </div>
    </div>
    <?php if (!empty($s['operador'])): ?>
      <div class="col-12">
        <span class="etiqueta-mini">Operador asignado</span>
        <div><i class="fa-solid fa-id-badge me-1"></i><?= e($s['operador']) ?>
          <?= !empty($s['placa']) ? ' · <i class="fa-solid fa-truck me-1"></i>' . e($s['placa']) : '' ?></div>
      </div>
    <?php endif; ?>
    <?php if (!empty($s['observaciones'])): ?>
      <div class="col-12">
        <span class="etiqueta-mini">Tus observaciones</span>
        <div><?= e($s['observaciones']) ?></div>
      </div>
    <?php endif; ?>
    <?php if (!empty($s['observaciones_empresa'])): ?>
      <div class="col-12">
        <span class="etiqueta-mini">Nota de la empresa</span>
        <div class="bg-verde-claro rounded p-2"><?= e($s['observaciones_empresa']) ?></div>
      </div>
    <?php endif; ?>
  </div>
</div>

<h2 class="seccion-titulo mb-2">Materiales publicados</h2>
<div class="tarjeta p-3 mb-3">
  <?php foreach ($detalle as $d): ?>
    <div class="d-flex gap-2 align-items-start border-bottom py-2">
      <?php if (!empty($d['foto'])): ?>
        <a class="link-foto" href="<?= e(urlFoto($d['foto'])) ?>" target="_blank" rel="noopener">
          <img class="miniatura" style="width:4.5rem" src="<?= e(urlFoto($d['foto'])) ?>" alt="Fotografía del material">
        </a>
      <?php else: ?>
        <div class="placeholder-foto" style="width:4.5rem;height:4.5rem;background:<?= e($d['categoria_color']) ?>22;color:<?= e($d['categoria_color']) ?>">
          <i class="fa-solid <?= e($d['icono']) ?>"></i>
        </div>
      <?php endif; ?>
      <div class="flex-grow-1">
        <div class="fw-bold small"><?= e($d['material']) ?></div>
        <div class="hint"><?= e($d['categoria']) ?> · <?= e(formatearCantidad((float) $d['cantidad'])) ?>
          <?= e(unidadesMedida()[$d['unidad']] ?? $d['unidad']) ?></div>
        <?php if ($d['descripcion']): ?><div class="hint"><?= e($d['descripcion']) ?></div><?php endif; ?>
        <?php if ($d['observaciones']): ?><div class="hint"><i class="fa-solid fa-comment-dots me-1"></i><?= e($d['observaciones']) ?></div><?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php if ($rec): ?>
  <h2 class="seccion-titulo mb-2">Recolección realizada</h2>
  <div class="tarjeta p-3 mb-3">
    <div class="row g-2 small">
      <div class="col-6">
        <span class="etiqueta-mini">Fecha y hora</span>
        <div><?= e(date('d/m/Y', strtotime((string) $rec['fecha']))) ?> · <?= e(substr((string) $rec['hora'], 0, 5)) ?></div>
      </div>
      <div class="col-6">
        <span class="etiqueta-mini">Peso total</span>
        <div><?= $rec['peso_total'] !== null ? e(formatearCantidad((float) $rec['peso_total'])) . ' kg' : 'No registrado' ?></div>
      </div>
      <div class="col-6">
        <span class="etiqueta-mini">Puntos ganados</span>
        <div class="fw-bold texto-verde"><i class="fa-solid fa-star me-1"></i><?= number_format((int) $puntosOtorgados, 0, ',', '.') ?> pts</div>
      </div>
      <div class="col-12">
        <span class="etiqueta-mini">Empresa / operador</span>
        <div><?= e($rec['empresa_nombre']) ?> · <?= e($rec['nombres'] . ' ' . $rec['apellidos']) ?>
          <?= !empty($rec['placa']) ? '· ' . e($rec['placa']) : '' ?></div>
      </div>
      <?php if ($recMateriales): ?>
        <div class="col-12">
          <span class="etiqueta-mini">Material recolectado</span>
          <ul class="mb-0 ps-3">
            <?php foreach ($recMateriales as $rm): ?>
              <li><?= e($rm['material']) ?>: <?= e(formatearCantidad((float) $rm['cantidad'])) ?> <?= e(unidadesMedida()[$rm['unidad']] ?? $rm['unidad']) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>
      <?php if (!empty($rec['evidencia'])): ?>
        <div class="col-12">
          <span class="etiqueta-mini">Evidencia fotográfica</span>
          <a class="link-foto" href="<?= e(urlFoto($rec['evidencia'])) ?>" target="_blank" rel="noopener">
            <img class="mt-1 rounded" style="max-height:12rem" src="<?= e(urlFoto($rec['evidencia'])) ?>" alt="Evidencia de la recolección">
          </a>
        </div>
      <?php endif; ?>
      <?php if (!empty($rec['observaciones'])): ?>
        <div class="col-12"><span class="etiqueta-mini">Observaciones</span><div><?= e($rec['observaciones']) ?></div></div>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>

<h2 class="seccion-titulo mb-2">Historial de la solicitud</h2>
<ul class="timeline tarjeta p-3 mb-3">
  <?php foreach ($historial as $h): ?>
    <li>
      <div class="titulo"><?= e(etiquetaEstado((string) $h['estado_nuevo'])) ?></div>
      <div class="fecha"><?= e(date('d/m/Y H:i', strtotime((string) $h['created_at']))) ?>
        <?= $h['usuario'] ? '· ' . e($h['usuario']) . ' (' . e((string) $h['rol']) . ')' : '' ?></div>
      <?php if ($h['observacion']): ?><div class="hint"><?= e($h['observacion']) ?></div><?php endif; ?>
    </li>
  <?php endforeach; ?>
</ul>

<?php if ($puedeCancelar): ?>
  <form method="post" action="<?= e(url('cancelar')) ?>" data-confirmar="¿Confirma que desea cancelar la solicitud <?= e($s['numero']) ?>?" class="mb-3">
    <?= csrf_campo() ?>
    <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
    <button class="btn btn-outline-danger btn-accion w-100" type="submit"><i class="fa-solid fa-ban me-2"></i>Cancelar solicitud</button>
  </form>
<?php endif; ?>
