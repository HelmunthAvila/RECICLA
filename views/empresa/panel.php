<?php /** RECICLA+ | Panel de la empresa operadora */ ?>
<div class="tarjeta p-3 mb-3">
  <div class="d-flex justify-content-between align-items-start">
    <div>
      <span class="etiqueta-mini">Empresa operadora</span>
      <h1 class="h5 fw-bold mb-0"><?= e($emp['nombre'] ?? 'Sin empresa') ?></h1>
      <div class="hint">NIT <?= e((string) $emp['nit']) ?> · <?= e((string) $emp['ciudad']) ?> · Responsable: <?= e((string) $emp['responsable']) ?></div>
    </div>
    <span class="badge <?= ($emp['estado'] ?? '') === 'activo' ? 'bg-success' : 'bg-secondary' ?>"><?= e(ucfirst((string) $emp['estado'])) ?></span>
  </div>
  <div class="d-grid gap-2 d-sm-flex mt-3">
    <a class="btn btn-recicla btn-accion flex-fill" href="<?= e(url('e_solicitudes', ['tipo' => 'disponibles'])) ?>">
      <i class="fa-solid fa-inbox me-1"></i> Solicitudes disponibles <span class="badge bg-light text-dark ms-1"><?= (int) $resumen['pendientes'] ?></span>
    </a>
    <a class="btn btn-outline-recicla btn-accion flex-fill" href="<?= e(url('e_rutas')) ?>"><i class="fa-solid fa-route me-1"></i> Organizar rutas</a>
  </div>
</div>

<div class="row g-2 mb-3">
  <div class="col-6 col-lg-3">
    <div class="stat-chip"><div class="icono mb-1"><i class="fa-solid fa-hand-holding-heart"></i></div>
      <div class="valor"><?= (int) $resumen['aceptadas'] ?></div><div class="etiqueta">Aceptadas</div></div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-chip"><div class="icono mb-1"><i class="fa-solid fa-calendar-check"></i></div>
      <div class="valor"><?= (int) $resumen['programadas'] ?></div><div class="etiqueta">Programadas</div></div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-chip"><div class="icono mb-1"><i class="fa-solid fa-truck-fast"></i></div>
      <div class="valor"><?= (int) $resumen['en_ruta'] ?></div><div class="etiqueta">En ruta</div></div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-chip"><div class="icono mb-1"><i class="fa-solid fa-circle-check"></i></div>
      <div class="valor"><?= (int) $resumen['recolectadas'] ?></div><div class="etiqueta">Recolectadas</div></div>
  </div>
</div>

<div class="tarjeta p-3 mb-3 d-flex align-items-center gap-3">
  <div class="icono-circulo icono-suave"><i class="fa-solid fa-weight-hanging"></i></div>
  <div>
    <div class="etiqueta-mini">Producción registrada</div>
    <div class="fw-bold"><?= e(formatearCantidad((float) $produccion['kg'])) ?> kg recolectados</div>
    <div class="hint"><?= (int) $produccion['visitas'] ?> recolección(es) · <?= count($vehiculos) ?> vehículo(s) registrados</div>
  </div>
</div>

<h2 class="seccion-titulo mb-2"><i class="fa-solid fa-calendar-day texto-verde me-2"></i>Agenda de recolecciones</h2>
<?php if ($agenda): ?>
  <div class="tarjeta p-3 mb-3">
    <?php foreach ($agenda as $a): ?>
      <div class="d-flex justify-content-between align-items-center border-bottom py-2 gap-2">
        <div>
          <div class="fw-bold small"><?= e($a['numero']) ?> · <?= e($a['ciudadano_nombre']) ?></div>
          <div class="hint">
            <i class="fa-solid fa-location-dot me-1"></i><?= e($a['barrio']) ?>, <?= e($a['ciudad']) ?>
            · <i class="fa-solid fa-phone me-1"></i><?= e((string) $a['ciudadano_telefono']) ?>
          </div>
          <div class="hint">
            <?php if ($a['fecha_programada']): ?>
              <i class="fa-solid fa-calendar-check texto-verde me-1"></i><?= e(date('d/m/Y', strtotime((string) $a['fecha_programada']))) ?>
              <?= $a['hora_programada'] ? '· ' . e(substr((string) $a['hora_programada'], 0, 5)) : '' ?>
            <?php else: ?>
              <i class="fa-solid fa-calendar-day me-1"></i>Sin programar · solicitada para <?= e(date('d/m/Y', strtotime((string) $a['fecha_disponible']))) ?>
            <?php endif; ?>
          </div>
        </div>
        <div class="text-end">
          <?= badgeEstado($a['estado']) ?>
          <a class="btn btn-sm btn-outline-recicla d-block mt-2" href="<?= e(url('e_solicitud', ['id' => (int) $a['id']])) ?>">Gestionar</a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <div class="tarjeta p-3 mb-3 text-center hint">No hay recolecciones aceptadas o programadas por ahora.</div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-12 col-lg-6">
    <h2 class="seccion-titulo mb-2"><i class="fa-solid fa-inbox texto-verde me-2"></i>Nuevas solicitudes sin asignar</h2>
    <?php if ($disponibles): ?>
      <?php foreach ($disponibles as $d): ?>
        <div class="tarjeta p-3 mb-2">
          <div class="d-flex justify-content-between">
            <div>
              <div class="fw-bold small"><?= e($d['numero']) ?></div>
              <div class="hint"><i class="fa-solid fa-location-dot me-1"></i><?= e($d['barrio']) ?>, <?= e($d['ciudad']) ?></div>
              <div class="hint"><?= (int) $d['materiales'] ?> material(es) · <?= e(date('d/m/Y', strtotime((string) $d['fecha_disponible']))) ?>
                · <?= e(etiquetaHorario($d['horario_disponible'])) ?></div>
            </div>
            <a class="btn btn-sm btn-recicla align-self-start" href="<?= e(url('e_solicitud', ['id' => (int) $d['id']])) ?>">Ver</a>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="tarjeta p-3 text-center hint">No hay solicitudes pendientes de asignar.</div>
    <?php endif; ?>
  </div>

  <div class="col-12 col-lg-6">
    <h2 class="seccion-titulo mb-2"><i class="fa-solid fa-map-location-dot texto-verde me-2"></i>Zonas con recolecciones</h2>
    <?php if ($zonas): ?>
      <div class="tarjeta p-2">
        <div class="table-responsive">
          <table class="table table-sm table-recicla mb-0">
            <thead><tr><th>Barrio</th><th>Fecha</th><th class="text-center">Total</th><th class="text-center">Recolectadas</th></tr></thead>
            <tbody>
            <?php foreach ($zonas as $z): ?>
              <tr>
                <td><?= e($z['barrio']) ?><div class="hint"><?= e($z['ciudad']) ?></div></td>
                <td><?= $z['fecha_programada'] ? e(date('d/m/Y', strtotime((string) $z['fecha_programada']))) : '<span class="text-muted">Sin fecha</span>' ?></td>
                <td class="text-center"><?= (int) $z['total'] ?></td>
                <td class="text-center"><?= (int) $z['recolectadas'] ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php else: ?>
      <div class="tarjeta p-3 text-center hint">Aún no hay solicitudes agrupables por zona.</div>
    <?php endif; ?>
  </div>
</div>
