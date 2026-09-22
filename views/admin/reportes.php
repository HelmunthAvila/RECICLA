<?php /** RECICLA+ | Reportes y estadísticas (RF-21) */ ?>
<h1 class="h5 fw-bold mb-1"><i class="fa-solid fa-chart-column texto-verde me-2"></i>Reportes</h1>
<p class="hint">Consulte información por fecha, material, ciudad, barrio, empresa, estado y cantidad recolectada.</p>

<div class="row g-2 mb-3">
  <?php
  $tipos = [
      'resumen'  => ['fa-gauge-high', 'Resumen general'],
      'fecha'    => ['fa-calendar-day', 'Por fecha'],
      'material' => ['fa-recycle', 'Por material'],
      'zona'     => ['fa-location-dot', 'Por ciudad y barrio'],
      'empresa'  => ['fa-building', 'Por empresa'],
      'estado'   => ['fa-list-check', 'Por estado'],
      'puntos'   => ['fa-star', 'Puntos y premios'],
  ];
  foreach ($tipos as $clave => [$icono, $texto]): ?>
    <div class="col-6 col-lg-2">
      <a class="stat-chip d-block text-dark h-100 <?= $tipo === $clave ? 'border border-2 border-success' : '' ?>"
         href="<?= e(url('a_reportes', ['tipo' => $clave, 'desde' => $filtros['desde'], 'hasta' => $filtros['hasta']])) ?>">
        <div class="icono mb-1"><i class="fa-solid <?= $icono ?>"></i></div>
        <div class="etiqueta"><?= e($texto) ?></div>
      </a>
    </div>
  <?php endforeach; ?>
</div>

<?php if ($tipo !== 'resumen'): ?>
  <form class="tarjeta-form" method="get" action="<?= e(BASE_URL) ?>/index.php">
    <input type="hidden" name="p" value="a_reportes">
    <input type="hidden" name="tipo" value="<?= e($tipo) ?>">
    <div class="row g-2">
      <div class="col-6 col-md-3">
        <label class="form-label" for="desde">Desde</label>
        <input class="form-control" type="date" id="desde" name="desde" value="<?= e($filtros['desde']) ?>">
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label" for="hasta">Hasta</label>
        <input class="form-control" type="date" id="hasta" name="hasta" value="<?= e($filtros['hasta']) ?>">
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label" for="ciudad">Ciudad</label>
        <select class="form-select" id="ciudad" name="ciudad">
          <option value="">Todas</option>
          <?php foreach ($ciudades as $c): ?>
            <option value="<?= e($c) ?>" <?= $filtros['ciudad'] === $c ? 'selected' : '' ?>><?= e($c) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label" for="empresa_id">Empresa</label>
        <select class="form-select" id="empresa_id" name="empresa_id">
          <option value="0">Todas</option>
          <?php foreach ($empresas as $em): ?>
            <option value="<?= (int) $em['id'] ?>" <?= (int) $filtros['empresa_id'] === (int) $em['id'] ? 'selected' : '' ?>><?= e($em['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label" for="categoria">Categoría</label>
        <select class="form-select" id="categoria" name="categoria">
          <option value="0">Todas</option>
          <?php foreach ($categorias as $c): ?>
            <option value="<?= (int) $c['id'] ?>" <?= (int) $filtros['categoria_id'] === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12 col-md-4">
        <label class="form-label" for="material_id">Material</label>
        <select class="form-select" id="material_id" name="material_id">
          <option value="0">Todos</option>
          <?php foreach ($materiales as $m): ?>
            <option value="<?= (int) $m['id'] ?>" <?= (int) $filtros['material_id'] === (int) $m['id'] ? 'selected' : '' ?>><?= e($m['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12 col-md-8 d-flex align-items-end gap-2">
        <button class="btn btn-recicla flex-fill" type="submit"><i class="fa-solid fa-filter me-1"></i>Generar reporte</button>
        <a class="btn btn-outline-recicla" href="<?= e(url('a_reporte_csv', [
              'tipo' => $tipo, 'desde' => $filtros['desde'], 'hasta' => $filtros['hasta'],
              'ciudad' => $filtros['ciudad'], 'empresa_id' => $filtros['empresa_id'],
              'material_id' => $filtros['material_id'], 'categoria' => $filtros['categoria_id'],
          ])) ?>"><i class="fa-solid fa-file-csv me-1"></i>Exportar CSV</a>
      </div>
    </div>
  </form>
<?php endif; ?>

<?php if ($tipo === 'resumen'): ?>
  <div class="row g-2 mb-3">
    <?php
    $indicadores = [
        ['fa-users', $resumen['usuarios'], 'Usuarios registrados'],
        ['fa-clipboard-list', $resumen['solicitudes'], 'Solicitudes registradas'],
        ['fa-hourglass-half', $resumen['registradas'], 'Solicitudes pendientes'],
        ['fa-calendar-check', $resumen['programadas'], 'Programadas / en ruta'],
        ['fa-circle-check', $resumen['recolectadas'], 'Recolectadas'],
        ['fa-weight-hanging', formatearCantidad($resumen['peso_total']) . ' kg', 'Material recolectado'],
        ['fa-building', $resumen['empresas'], 'Empresas operadoras'],
        ['fa-recycle', $resumen['materiales'], 'Materiales activos'],
    ];
    foreach ($indicadores as [$icono, $valor, $etiqueta]): ?>
      <div class="col-6 col-lg-3">
        <div class="stat-chip"><div class="icono mb-1"><i class="fa-solid <?= $icono ?>"></i></div>
          <div class="valor"><?= e((string) $valor) ?></div><div class="etiqueta"><?= e($etiqueta) ?></div></div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="tarjeta p-3 mb-3">
    <span class="seccion-titulo d-block mb-2">Recolecciones por mes</span>
    <?php $maxKg = max(array_map(fn($s) => $s['kg'], $serie) ?: [1]); ?>
    <?php foreach ($serie as $mes): ?>
      <div class="d-flex align-items-center gap-2 mb-2">
        <span class="etiqueta-mini text-nowrap" style="width:3.2rem"><?= e($mes['label']) ?></span>
        <div class="flex-grow-1 bg-light rounded" style="height:.9rem;overflow:hidden">
          <div style="height:100%;width:<?= $maxKg > 0 ? round($mes['kg'] / $maxKg * 100) : 0 ?>%;background:linear-gradient(90deg,#2e8800,#7ac943)"></div>
        </div>
        <span class="small text-nowrap" style="width:6rem;text-align:right"><?= e(formatearCantidad($mes['kg'])) ?> kg · <?= (int) $mes['total'] ?></span>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="tarjeta p-3">
    <span class="seccion-titulo d-block mb-2">Solicitudes por estado</span>
    <table class="table table-sm table-recicla mb-0">
      <thead><tr><th>Estado</th><th class="text-center">Total</th></tr></thead>
      <tbody>
      <?php foreach ($resumen['estados'] as $clave => $total): ?>
        <tr><td><?= badgeEstado($clave) ?></td><td class="text-center"><?= (int) $total ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

<?php elseif ($tipo === 'fecha'): ?>
  <div class="tarjeta p-2">
    <div class="table-responsive">
      <table class="table table-sm table-recicla mb-0">
        <thead><tr><th>Fecha</th><th class="text-center">Recolecciones</th><th class="text-center">Kg</th>
          <th class="text-center">Ciudadanos</th><th class="text-center">Empresas</th></tr></thead>
        <tbody>
        <?php foreach ($datos['filas'] as $f): ?>
          <tr>
            <td><?= e(date('d/m/Y', strtotime((string) $f['fecha']))) ?></td>
            <td class="text-center"><?= (int) $f['recolecciones'] ?></td>
            <td class="text-center"><?= e(formatearCantidad((float) $f['kg'])) ?></td>
            <td class="text-center"><?= (int) $f['ciudadanos'] ?></td>
            <td class="text-center"><?= (int) $f['empresas'] ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php if (!$datos['filas']): ?><p class="hint text-center mb-0 py-3">Sin recolecciones en el rango seleccionado.</p><?php endif; ?>
  </div>

<?php elseif ($tipo === 'material'): ?>
  <div class="tarjeta p-2">
    <div class="table-responsive">
      <table class="table table-sm table-recicla mb-0">
        <thead><tr><th>Material</th><th>Categoría</th><th class="text-center">Recolecciones</th>
          <th class="text-center">Cantidad</th><th class="text-center">Kg</th></tr></thead>
        <tbody>
        <?php foreach ($datos['filas'] as $f): ?>
          <tr>
            <td><?= e($f['material']) ?></td>
            <td><span class="badge" style="background:<?= e($f['color']) ?>"><?= e($f['categoria']) ?></span></td>
            <td class="text-center"><?= (int) $f['recolecciones'] ?></td>
            <td class="text-center"><?= e(formatearCantidad((float) $f['cantidad_total'])) ?> <?= e(unidadesMedida()[$f['unidad']] ?? $f['unidad']) ?></td>
            <td class="text-center"><?= e(formatearCantidad((float) $f['kg'])) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php if (!$datos['filas']): ?><p class="hint text-center mb-0 py-3">Sin datos para el rango seleccionado.</p><?php endif; ?>
  </div>

<?php elseif ($tipo === 'zona'): ?>
  <div class="tarjeta p-2">
    <div class="table-responsive">
      <table class="table table-sm table-recicla mb-0">
        <thead><tr><th>Ciudad</th><th>Barrio</th><th class="text-center">Solicitudes</th>
          <th class="text-center">Pendientes</th><th class="text-center">Recolectadas</th><th class="text-center">Canceladas</th></tr></thead>
        <tbody>
        <?php foreach ($datos['filas'] as $f): ?>
          <tr>
            <td><?= e($f['ciudad']) ?></td>
            <td><?= e($f['barrio']) ?></td>
            <td class="text-center"><?= (int) $f['solicitudes'] ?></td>
            <td class="text-center"><?= (int) $f['pendientes'] ?></td>
            <td class="text-center"><?= (int) $f['recolectadas'] ?></td>
            <td class="text-center"><?= (int) $f['canceladas'] ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php if (!$datos['filas']): ?><p class="hint text-center mb-0 py-3">Sin solicitudes en el rango seleccionado.</p><?php endif; ?>
  </div>

<?php elseif ($tipo === 'empresa'): ?>
  <div class="tarjeta p-2">
    <div class="table-responsive">
      <table class="table table-sm table-recicla mb-0">
        <thead><tr><th>Empresa</th><th>NIT</th><th>Ciudad</th><th class="text-center">Solicitudes</th>
          <th class="text-center">Programadas</th><th class="text-center">Recolectadas</th><th class="text-center">Kg</th></tr></thead>
        <tbody>
        <?php foreach ($datos['filas'] as $f): ?>
          <tr>
            <td><?= e($f['empresa']) ?> <span class="badge <?= $f['estado'] === 'activo' ? 'bg-success' : 'bg-secondary' ?>"><?= e($f['estado']) ?></span></td>
            <td><?= e($f['nit']) ?></td>
            <td><?= e((string) $f['ciudad']) ?></td>
            <td class="text-center"><?= (int) $f['solicitudes'] ?></td>
            <td class="text-center"><?= (int) $f['programadas'] ?></td>
            <td class="text-center"><?= (int) $f['recolectadas'] ?></td>
            <td class="text-center"><?= e(formatearCantidad((float) $f['kg'])) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

<?php elseif ($tipo === 'estado'): ?>
  <div class="tarjeta p-3">
    <span class="seccion-titulo d-block mb-2">Solicitudes por estado entre <?= e($datos['desde']) ?> y <?= e($datos['hasta']) ?></span>
    <table class="table table-sm table-recicla mb-0">
      <thead><tr><th>Estado</th><th class="text-center">Total</th></tr></thead>
      <tbody>
      <?php foreach ($datos['filas'] as $clave => $total): ?>
        <tr><td><?= badgeEstado((string) $clave) ?></td><td class="text-center"><?= (int) $total ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php elseif ($tipo === 'puntos'): ?>
  <div class="row g-2 mb-3">
    <?php
    $kpisPuntos = [
        ['fa-star', $puntosResumen['otorgados'], 'Puntos otorgados'],
        ['fa-gift', $puntosResumen['canjeados'], 'Puntos canjeados'],
        ['fa-hand-holding-dollar', $puntosResumen['saldo_total'], 'Saldo en circulación'],
        ['fa-users', $puntosResumen['participantes'], 'Ciudadanos participantes'],
    ];
    foreach ($kpisPuntos as [$ic, $valor, $etq]): ?>
      <div class="col-6 col-lg-3">
        <div class="stat-chip"><div class="icono mb-1"><i class="fa-solid <?= $ic ?>"></i></div>
          <div class="valor"><?= number_format((int) $valor, 0, ',', '.') ?></div>
          <div class="etiqueta"><?= e($etq) ?></div></div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="tarjeta p-2 mb-3">
    <div class="table-responsive">
      <table class="table table-sm table-recicla mb-0">
        <thead><tr><th>Material</th><th>Categoría</th><th class="text-center">Puntos por unidad</th>
          <th class="text-center">Puntos otorgados</th></tr></thead>
        <tbody>
        <?php foreach ($datos['filas'] as $f): ?>
          <tr>
            <td><?= e($f['material']) ?></td>
            <td><span class="badge" style="background:<?= e($f['color']) ?>"><?= e($f['categoria']) ?></span></td>
            <td class="text-center"><?= (int) $f['puntos_por_unidad'] ?></td>
            <td class="text-center"><strong class="texto-verde"><?= number_format((int) $f['puntos'], 0, ',', '.') ?></strong></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php if (!$datos['filas']): ?><p class="hint text-center mb-0 py-3">Sin puntos otorgados en el rango seleccionado.</p><?php endif; ?>
  </div>

  <div class="row g-3">
    <div class="col-12 col-lg-6">
      <div class="tarjeta p-3">
        <span class="seccion-titulo d-block mb-2">Puntos ganados y canjeados por mes</span>
        <?php $maxPP = max(array_map(fn($s) => max($s['ganados'], $s['canjeados']), $seriePuntos) ?: [1]); ?>
        <?php foreach ($seriePuntos as $m): ?>
          <div class="d-flex align-items-center gap-2 mb-2">
            <span class="etiqueta-mini text-nowrap" style="width:3.2rem"><?= e($m['label']) ?></span>
            <div class="flex-grow-1">
              <div class="bg-light rounded mb-1" style="height:.7rem;overflow:hidden">
                <div style="height:100%;width:<?= $maxPP > 0 ? round($m['ganados'] / $maxPP * 100) : 0 ?>%;background:linear-gradient(90deg,#2e8800,#7ac943)"></div>
              </div>
              <div class="bg-light rounded" style="height:.7rem;overflow:hidden">
                <div style="height:100%;width:<?= $maxPP > 0 ? round($m['canjeados'] / $maxPP * 100) : 0 ?>%;background:linear-gradient(90deg,#b02a37,#f06b7b)"></div>
              </div>
            </div>
            <span class="small text-nowrap" style="width:7.5rem;text-align:right">
              <span class="texto-verde">+<?= number_format($m['ganados'], 0, ',', '.') ?></span> /
              <span class="text-danger">-<?= number_format($m['canjeados'], 0, ',', '.') ?></span>
            </span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="col-12 col-lg-6">
      <div class="tarjeta p-3">
        <span class="seccion-titulo d-block mb-2">Ciudadanos con más puntos</span>
        <table class="table table-sm table-recicla mb-0">
          <thead><tr><th>Ciudadano</th><th>Zona</th><th class="text-center">Puntos</th><th class="text-center">Recolecciones</th></tr></thead>
          <tbody>
          <?php foreach ($topCiudadanos as $c2): ?>
            <tr>
              <td><?= e($c2['ciudadano']) ?><div class="hint">Doc. <?= e($c2['documento']) ?></div></td>
              <td><?= e((string) $c2['barrio']) ?><div class="hint"><?= e((string) $c2['ciudad']) ?></div></td>
              <td class="text-center"><strong class="texto-verde"><?= number_format((int) $c2['puntos'], 0, ',', '.') ?></strong></td>
              <td class="text-center"><?= (int) $c2['recolecciones'] ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php if (!$topCiudadanos): ?><p class="hint mb-0">Sin datos de puntos todavía.</p><?php endif; ?>
      </div>
    </div>
  </div>
<?php endif; ?>
