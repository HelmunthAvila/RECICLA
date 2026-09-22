<?php /** RECICLA+ | Dashboard administrativo (RF-20) */ ?>
<h1 class="h5 fw-bold mb-1"><i class="fa-solid fa-gauge-high texto-verde me-2"></i>Panel administrativo</h1>
<p class="hint">Indicadores generales del sistema RECICLA+.</p>

<div class="row g-2 mb-3">
  <?php
  $tarjetas = [
      ['fa-users', $r['usuarios'], 'Usuarios registrados', 'a_usuarios', ''],
      ['fa-clipboard-list', $r['solicitudes'], 'Solicitudes registradas', 'a_solicitudes', ''],
      ['fa-hourglass-half', $r['registradas'], 'Solicitudes pendientes', 'a_solicitudes', 'estado=REGISTRADA'],
      ['fa-calendar-check', $r['programadas'], 'Programadas / en ruta', 'a_solicitudes', 'estado=PROGRAMADA'],
      ['fa-circle-check', $r['recolectadas'], 'Recolecciones realizadas', 'a_recolecciones', ''],
      ['fa-weight-hanging', formatearCantidad($r['peso_total']) . ' kg', 'Material recolectado', 'a_recolecciones', ''],
      ['fa-building', $r['empresas'], 'Empresas operadoras', 'a_empresas', ''],
      ['fa-recycle', $r['materiales'], 'Materiales activos', 'a_materiales', ''],
  ];
  foreach ($tarjetas as [$icono, $valor, $etiqueta, $ruta, $extra]):
      $parametros = [];
      if ($extra !== '') {
          parse_str($extra, $parametros);
      }
  ?>
    <div class="col-6 col-lg-3">
      <a class="stat-chip d-block text-dark h-100" href="<?= e(url($ruta, $parametros)) ?>">
        <div class="icono mb-1"><i class="fa-solid <?= e($icono) ?>"></i></div>
        <div class="valor"><?= e((string) $valor) ?></div>
        <div class="etiqueta"><?= e($etiqueta) ?></div>
      </a>
    </div>
  <?php endforeach; ?>
</div>

<div class="row g-3">
  <div class="col-12">
    <div class="tarjeta p-3 mb-3">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="seccion-titulo mb-0"><i class="fa-solid fa-star texto-verde me-2"></i>Programa de puntos y premios</span>
        <a class="small" href="<?= e(url('a_premios')) ?>">Administrar premios</a>
      </div>
      <div class="row g-2">
        <?php
        $kpisPuntos = [
            ['fa-star', $puntos['otorgados'], 'Puntos otorgados', 'a_puntos'],
            ['fa-hand-holding-dollar', $puntos['saldo_total'], 'Saldo en circulación', 'a_puntos'],
            ['fa-gift', $puntos['canjeados'], 'Puntos canjeados', 'a_canjes'],
            ['fa-hourglass-half', $puntos['pendientes'], 'Canjes por entregar', 'a_canjes'],
            ['fa-users', $puntos['participantes'], 'Ciudadanos con puntos', 'a_puntos'],
            ['fa-box-open', $puntos['agotados'], 'Premios agotados', 'a_premios'],
        ];
        foreach ($kpisPuntos as [$ic, $valor, $etq, $rutaP]): ?>
          <div class="col-6 col-lg-2">
            <a class="stat-chip d-block text-dark h-100" href="<?= e(url($rutaP)) ?>">
              <div class="icono mb-1"><i class="fa-solid <?= $ic ?>"></i></div>
              <div class="valor"><?= number_format((int) $valor, 0, ',', '.') ?></div>
              <div class="etiqueta"><?= e($etq) ?></div>
            </a>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if ($canjesPend): ?>
        <div class="mt-3">
          <div class="etiqueta-mini mb-1">Canjes pendientes de entrega</div>
          <?php foreach ($canjesPend as $cj): ?>
            <div class="d-flex justify-content-between border-bottom py-1 small">
              <span><?= e($cj['codigo']) ?> · <?= e($cj['ciudadano']) ?> · <?= e($cj['premio']) ?></span>
              <a href="<?= e(url('a_canjes', ['estado' => 'solicitado'])) ?>">Gestionar</a>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if ($ranking): ?>
        <div class="mt-3">
          <div class="etiqueta-mini mb-1">Ciudadanos que más puntos han ganado</div>
          <div class="d-flex flex-wrap gap-2">
            <?php foreach ($ranking as $rk): ?>
              <span class="badge bg-verde-claro text-dark">
                #<?= (int) $rk['puesto'] ?> <?= e($rk['nombres'] . ' ' . substr((string) $rk['apellidos'], 0, 1)) ?>.
                · <?= number_format((int) $rk['puntos'], 0, ',', '.') ?> pts
              </span>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-12 col-lg-7">
    <div class="tarjeta p-3 mb-3">
      <span class="seccion-titulo d-block mb-2"><i class="fa-solid fa-chart-column texto-verde me-2"></i>Material recolectado por mes (kg)</span>
      <?php $maxKg = max(array_map(fn($s) => $s['kg'], $serie) ?: [1]); ?>
      <?php foreach ($serie as $mes): ?>
        <div class="d-flex align-items-center gap-2 mb-2">
          <span class="etiqueta-mini text-nowrap" style="width:3.2rem"><?= e($mes['label']) ?></span>
          <div class="flex-grow-1 bg-light rounded" style="height:.9rem;overflow:hidden">
            <div style="height:100%;width:<?= $maxKg > 0 ? round($mes['kg'] / $maxKg * 100) : 0 ?>%;background:linear-gradient(90deg,#2e8800,#7ac943)"></div>
          </div>
          <span class="small text-nowrap" style="width:5rem;text-align:right"><?= e(formatearCantidad($mes['kg'])) ?> kg</span>
        </div>
      <?php endforeach; ?>
      <?php if (!$serie): ?><p class="hint mb-0">Sin datos de recolección.</p><?php endif; ?>
    </div>

    <div class="tarjeta p-3">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="seccion-titulo mb-0">Últimas solicitudes</span>
        <a class="small" href="<?= e(url('a_solicitudes')) ?>">Ver todas</a>
      </div>
      <?php $rutaDetalle = 'a_solicitud'; $mostrarCiudadano = true; ?>
      <?php foreach ($ultimas as $s): ?>
        <?php require BASE_PATH . '/views/partials/solicitud_card.php'; ?>
      <?php endforeach; ?>
      <?php if (!$ultimas): ?><p class="hint mb-0">Aún no hay solicitudes registradas.</p><?php endif; ?>
    </div>
  </div>

  <div class="col-12 col-lg-5">
    <div class="tarjeta p-3 mb-3">
      <span class="seccion-titulo d-block mb-2"><i class="fa-solid fa-chart-pie texto-verde me-2"></i>Solicitudes por estado</span>
      <?php foreach ($r['estados'] as $clave => $total): ?>
        <div class="d-flex justify-content-between align-items-center border-bottom py-1 small">
          <span><?= badgeEstado($clave) ?></span>
          <strong><?= (int) $total ?></strong>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="tarjeta p-3 mb-3">
      <span class="seccion-titulo d-block mb-2"><i class="fa-solid fa-calendar-day texto-verde me-2"></i>Próximas recolecciones</span>
      <?php foreach ($programadas as $p): ?>
        <div class="border-bottom py-2 small">
          <div class="fw-bold"><?= e($p['numero']) ?> · <?= e(date('d/m/Y', strtotime((string) $p['fecha_programada']))) ?>
            <?= $p['hora_programada'] ? '· ' . e(substr((string) $p['hora_programada'], 0, 5)) : '' ?></div>
          <div class="hint"><?= e($p['ciudadano_nombre']) ?> · <?= e($p['barrio']) ?>, <?= e($p['ciudad']) ?></div>
          <div class="hint"><?= e((string) $p['empresa_nombre']) ?><?= $p['operador'] ? ' · ' . e($p['operador']) : '' ?></div>
        </div>
      <?php endforeach; ?>
      <?php if (!$programadas): ?><p class="hint mb-0">No hay recolecciones programadas.</p><?php endif; ?>
    </div>

    <div class="tarjeta p-3">
      <span class="seccion-titulo d-block mb-2"><i class="fa-solid fa-clock-rotate-left texto-verde me-2"></i>Últimos movimientos</span>
      <ul class="timeline mb-0">
        <?php foreach ($movimientos as $m): ?>
          <li>
            <div class="titulo"><?= e(etiquetaEstado((string) $m['estado_nuevo'])) ?></div>
            <div class="fecha"><?= e($m['numero']) ?> · <?= e(date('d/m/Y H:i', strtotime((string) $m['created_at']))) ?></div>
            <?php if ($m['usuario']): ?><div class="hint"><?= e($m['usuario']) ?> (<?= e((string) $m['rol']) ?>)</div><?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</div>
