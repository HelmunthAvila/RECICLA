<?php /** RECICLA+ | Gestión de una solicitud (RF-11 … RF-14) */ ?>
<a class="btn btn-sm btn-outline-recicla mb-2" href="<?= e(url('e_solicitudes', ['tipo' => $esDisponible ? 'disponibles' : 'programadas'])) ?>">
  <i class="fa-solid fa-arrow-left me-1"></i> Volver al listado
</a>

<div class="tarjeta p-3 mb-3">
  <div class="d-flex justify-content-between align-items-start">
    <div>
      <span class="etiqueta-mini">Solicitud</span>
      <h1 class="h5 fw-bold mb-0"><?= e($s['numero']) ?></h1>
    </div>
    <?= badgeEstado($s['estado']) ?>
  </div>

  <?php if ($esDisponible): ?>
    <div class="alert alert-warning small mt-3 mb-2">
      <i class="fa-solid fa-triangle-exclamation me-1"></i>Esta solicitud aún no tiene empresa asignada. Al aceptarla, su empresa será la encargada de la recolección.
    </div>
    <form method="post" action="<?= e(url('e_aceptar')) ?>" data-confirmar="¿Aceptar la solicitud <?= e($s['numero']) ?>?" class="d-grid gap-2">
      <?= csrf_campo() ?>
      <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
      <input class="form-control" type="text" name="observacion" maxlength="300" placeholder="Observación para el ciudadano (opcional)">
      <button class="btn btn-recicla btn-accion" type="submit"><i class="fa-solid fa-hand-holding-heart me-2"></i>Aceptar solicitud</button>
    </form>
  <?php endif; ?>
</div>

<div class="tarjeta p-3 mb-3">
  <h2 class="seccion-titulo mb-2"><i class="fa-solid fa-user texto-verde me-2"></i>Datos del ciudadano</h2>
  <div class="row g-2 small">
    <div class="col-12 col-md-6"><span class="etiqueta-mini">Nombre</span><div><?= e((string) $s['ciudadano_nombre']) ?></div></div>
    <div class="col-6 col-md-3"><span class="etiqueta-mini">Documento</span><div><?= e((string) $s['ciudadano_documento']) ?></div></div>
    <div class="col-6 col-md-3"><span class="etiqueta-mini">Teléfono</span>
      <div><?= e((string) $s['ciudadano_telefono']) ?>
        <?php if (!empty($s['ciudadano_telefono'])): ?>
          <a class="ms-1" href="tel:<?= e((string) $s['ciudadano_telefono']) ?>" title="Llamar"><i class="fa-solid fa-phone"></i></a>
          <a class="ms-1" href="https://wa.me/57<?= e(preg_replace('/\D/', '', (string) $s['ciudadano_telefono'])) ?>" target="_blank" rel="noopener" title="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
        <?php endif; ?>
      </div>
    </div>
    <div class="col-12"><span class="etiqueta-mini">Dirección</span>
      <div><i class="fa-solid fa-location-dot texto-verde me-1"></i><?= e($s['direccion']) ?> · <?= e($s['barrio']) ?>, <?= e($s['ciudad']) ?></div>
    </div>
    <div class="col-6"><span class="etiqueta-mini">Fecha solicitada</span>
      <div><?= e(date('d/m/Y', strtotime((string) $s['fecha_disponible']))) ?> · <?= e(etiquetaHorario($s['horario_disponible'])) ?></div>
    </div>
    <div class="col-6"><span class="etiqueta-mini">Correo</span><div><?= e((string) $s['ciudadano_email']) ?></div></div>
    <?php if (!empty($s['observaciones'])): ?>
      <div class="col-12"><span class="etiqueta-mini">Observaciones del ciudadano</span><div class="bg-light rounded p-2"><?= e($s['observaciones']) ?></div></div>
    <?php endif; ?>
  </div>
</div>

<h2 class="seccion-titulo mb-2">Materiales solicitados</h2>
<div class="tarjeta p-3 mb-3">
  <?php foreach ($detalle as $d): ?>
    <div class="d-flex gap-2 border-bottom py-2">
      <?php if (!empty($d['foto'])): ?>
        <a href="<?= e(urlFoto($d['foto'])) ?>" target="_blank" rel="noopener">
          <img class="miniatura" style="width:4.5rem" src="<?= e(urlFoto($d['foto'])) ?>" alt="Fotografía del material">
        </a>
      <?php else: ?>
        <div class="placeholder-foto" style="width:4.5rem;height:4.5rem;background:<?= e($d['categoria_color']) ?>22;color:<?= e($d['categoria_color']) ?>">
          <i class="fa-solid <?= e($d['icono']) ?>"></i>
        </div>
      <?php endif; ?>
      <div>
        <div class="fw-bold small"><?= e($d['material']) ?></div>
        <div class="hint"><?= e($d['categoria']) ?> · <?= e(formatearCantidad((float) $d['cantidad'])) ?> <?= e(unidadesMedida()[$d['unidad']] ?? $d['unidad']) ?></div>
        <?php if ($d['descripcion']): ?><div class="hint"><?= e($d['descripcion']) ?></div><?php endif; ?>
        <?php if ($d['observaciones']): ?><div class="hint"><i class="fa-solid fa-comment-dots me-1"></i><?= e($d['observaciones']) ?></div><?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php if ($puedeProgramar): ?>
  <a class="btn btn-recicla btn-accion w-100 mb-2" href="<?= e(url('e_programar', ['id' => (int) $s['id']])) ?>">
    <i class="fa-solid fa-calendar-plus me-2"></i>Programar recolección
  </a>
<?php endif; ?>

<?php if ($puedeEnRuta): ?>
  <form method="post" action="<?= e(url('e_en_ruta')) ?>" data-confirmar="¿Marcar la solicitud <?= e($s['numero']) ?> como EN RUTA?" class="mb-2">
    <?= csrf_campo() ?>
    <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
    <button class="btn btn-warning btn-accion w-100" type="submit"><i class="fa-solid fa-truck-fast me-2"></i>Marcar en ruta</button>
  </form>
<?php endif; ?>

<?php if ($puedeRecolectar): ?>
  <a class="btn btn-recicla btn-accion w-100 mb-2" href="<?= e(url('e_recoleccion', ['id' => (int) $s['id']])) ?>">
    <i class="fa-solid fa-clipboard-check me-2"></i>Registrar recolección realizada
  </a>
<?php endif; ?>

<div class="tarjeta p-3 mb-3 d-flex align-items-center gap-3">
  <div class="icono-circulo" style="background:var(--amarillo);color:#1c5202"><i class="fa-solid fa-star"></i></div>
  <div>
    <div class="etiqueta-mini">Programa de incentivos del ciudadano</div>
    <?php if ($rec): ?>
      <div class="fw-bold"><?= number_format((int) $puntosOtorgados, 0, ',', '.') ?> puntos acreditados</div>
      <div class="hint">Se otorgaron al confirmar esta recolección; el ciudadano puede cambiarlos por premios.</div>
    <?php else: ?>
      <div class="fw-bold">Otorgará aproximadamente <?= number_format((int) $puntosEstimados, 0, ',', '.') ?> puntos</div>
      <div class="hint">Los puntos se acreditan cuando usted confirma la recolección realizada.</div>
    <?php endif; ?>
  </div>
</div>

<?php if ($rec): ?>
  <div class="tarjeta p-3 mb-3">
    <h2 class="seccion-titulo mb-2"><i class="fa-solid fa-circle-check texto-verde me-2"></i>Recolección registrada</h2>
    <div class="row g-2 small">
      <div class="col-6"><span class="etiqueta-mini">Fecha y hora</span>
        <div><?= e(date('d/m/Y', strtotime((string) $rec['fecha']))) ?> · <?= e(substr((string) $rec['hora'], 0, 5)) ?></div></div>
      <div class="col-6"><span class="etiqueta-mini">Peso total</span>
        <div><?= $rec['peso_total'] !== null ? e(formatearCantidad((float) $rec['peso_total'])) . ' kg' : 'No registrado' ?></div></div>
      <div class="col-12"><span class="etiqueta-mini">Material recolectado</span>
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
      <?php if (!empty($rec['observaciones'])): ?>
        <div class="col-12"><span class="etiqueta-mini">Observaciones</span><div><?= e($rec['observaciones']) ?></div></div>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>

<h2 class="seccion-titulo mb-2">Historial</h2>
<ul class="timeline tarjeta p-3 mb-3">
  <?php foreach ($historial as $h): ?>
    <li>
      <div class="titulo"><?= e(etiquetaEstado((string) $h['estado_nuevo'])) ?></div>
      <div class="fecha"><?= e(date('d/m/Y H:i', strtotime((string) $h['created_at']))) ?><?= $h['usuario'] ? ' · ' . e($h['usuario']) : '' ?></div>
      <?php if ($h['observacion']): ?><div class="hint"><?= e($h['observacion']) ?></div><?php endif; ?>
    </li>
  <?php endforeach; ?>
</ul>

<?php if (!$esDisponible && $s['estado'] !== 'RECOLECTADA' && $s['estado'] !== 'CANCELADA'): ?>
  <div class="tarjeta p-3">
    <h2 class="seccion-titulo mb-2">Asignar a una ruta (RF-15)</h2>
    <form method="post" action="<?= e(url('e_asignar_ruta')) ?>" class="row g-2">
      <?= csrf_campo() ?>
      <input type="hidden" name="solicitud_id" value="<?= (int) $s['id'] ?>">
      <div class="col-8">
        <select class="form-select" name="ruta_id">
          <option value="0">Sin ruta asignada</option>
          <?php foreach ($rutas as $r): ?>
            <option value="<?= (int) $r['id'] ?>" <?= (int) $s['ruta_id'] === (int) $r['id'] ? 'selected' : '' ?>>
              <?= e($r['nombre']) ?> · <?= e($r['zona'] ?: $r['ciudad']) ?> · <?= e(date('d/m/Y', strtotime((string) $r['fecha']))) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-4"><button class="btn btn-outline-recicla w-100" type="submit">Guardar</button></div>
    </form>
  </div>
<?php endif; ?>
