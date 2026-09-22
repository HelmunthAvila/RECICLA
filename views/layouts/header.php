<?php
/** RECICLA+ | Cabecera y navegación (diseño Mobile First) */
$u = $usuario ?? null;
$nav = $navTipo ?? '';
$sysName = config_get('nombre_sistema', 'RECICLA+');
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#2e8800">
<meta name="description" content="RECICLA+ · Consulta materiales reciclables y solicita la recolección a domicilio.">
<title><?= e($titulo ?? 'Inicio') ?> | <?= e($sysName) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/css/recicla.css">
</head>
<body class="rol-<?= e($nav !== '' ? $nav : 'publico') ?><?= $u ? ' con-nav-inferior' : '' ?>">

<a class="visually-hidden-focusable" href="#contenido">Saltar al contenido principal</a>

<nav class="navbar navbar-expand-lg navbar-recicla sticky-top">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="<?= e(url($u ? ($u['rol'] === 'ciudadano' ? 'panel' : ($u['rol'] === 'empresa' ? 'e_panel' : 'a_panel')) : 'inicio')) ?>">
      <span class="marca-icono"><i class="fa-solid fa-recycle"></i></span>
      <span class="marca-texto"><?= e($sysName) ?></span>
    </a>

    <div class="d-flex align-items-center gap-2 order-lg-3">
      <?php if ($u): ?>
        <?php $avisos = notificacionesNoLeidas((int) $u['id']); ?>
        <a class="btn-aviso position-relative" href="<?= e($u['rol'] === 'ciudadano' ? url('notificaciones') : url($u['rol'] === 'empresa' ? 'e_panel' : 'a_panel')) ?>" title="Avisos">
          <i class="fa-solid fa-bell"></i>
          <?php if ($avisos > 0): ?><span class="badge rounded-pill bg-danger badge-aviso"><?= $avisos ?></span><?php endif; ?>
        </a>
        <div class="dropdown">
          <button class="btn btn-usuario dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fa-solid fa-circle-user"></i>
            <span class="d-none d-sm-inline ms-1"><?= e($u['nombres']) ?></span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow">
            <li class="dropdown-header text-uppercase small"><?= e(ucfirst($u['rol'])) ?><?= $u['empresa'] ? ' · ' . e($u['empresa']) : '' ?></li>
            <li><a class="dropdown-item" href="<?= e($u['rol'] === 'ciudadano' ? url('perfil') : url($u['rol'] === 'empresa' ? 'e_perfil' : 'a_config')) ?>"><i class="fa-solid fa-id-card me-2"></i>Mi cuenta</a></li>
            <?php if ($u['rol'] === 'ciudadano'): ?>
              <li><a class="dropdown-item" href="<?= e(url('mis_solicitudes')) ?>"><i class="fa-solid fa-clipboard-list me-2"></i>Mis solicitudes</a></li>
              <li><a class="dropdown-item" href="<?= e(url('historial')) ?>"><i class="fa-solid fa-clock-rotate-left me-2"></i>Mi historial</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="<?= e(url('mis_puntos')) ?>"><i class="fa-solid fa-star me-2"></i>Mis puntos</a></li>
              <li><a class="dropdown-item" href="<?= e(url('premios')) ?>"><i class="fa-solid fa-gift me-2"></i>Cambiar por premios</a></li>
            <?php endif; ?>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="<?= e(url('logout')) ?>"><i class="fa-solid fa-right-from-bracket me-2"></i>Cerrar sesión</a></li>
          </ul>
        </div>
      <?php else: ?>
        <a class="btn btn-sm btn-claro" href="<?= e(url('materiales')) ?>"><i class="fa-solid fa-list-check me-1"></i>Materiales</a>
        <a class="btn btn-sm btn-amarillo" href="<?= e(url('login')) ?>"><i class="fa-solid fa-right-to-bracket me-1"></i>Ingresar</a>
      <?php endif; ?>

      <?php if ($u && $nav !== 'ciudadano'): ?>
        <button class="navbar-toggler ms-1" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal" aria-controls="menuPrincipal" aria-expanded="false" aria-label="Abrir menú">
          <span class="navbar-toggler-icon"></span>
        </button>
      <?php endif; ?>
    </div>

    <?php if ($u && $nav !== 'ciudadano'): ?>
      <div class="collapse navbar-collapse order-lg-2" id="menuPrincipal">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <?php if ($nav === 'empresa'): ?>
            <li class="nav-item"><a class="nav-link <?= activo('e_panel') ?>" href="<?= e(url('e_panel')) ?>"><i class="fa-solid fa-gauge-high me-1"></i>Dashboard</a></li>
            <li class="nav-item"><a class="nav-link <?= activo('e_solicitudes') ?>" href="<?= e(url('e_solicitudes')) ?>"><i class="fa-solid fa-inbox me-1"></i>Solicitudes</a></li>
            <li class="nav-item"><a class="nav-link <?= activo('e_rutas') ?>" href="<?= e(url('e_rutas')) ?>"><i class="fa-solid fa-route me-1"></i>Rutas</a></li>
            <li class="nav-item"><a class="nav-link <?= activo('e_historial') ?>" href="<?= e(url('e_historial')) ?>"><i class="fa-solid fa-truck-ramp-box me-1"></i>Recolecciones</a></li>
            <li class="nav-item"><a class="nav-link <?= activo('e_perfil') ?>" href="<?= e(url('e_perfil')) ?>"><i class="fa-solid fa-building me-1"></i>Mi empresa</a></li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link <?= activo('a_panel') ?>" href="<?= e(url('a_panel')) ?>"><i class="fa-solid fa-gauge-high me-1"></i>Dashboard</a></li>
            <li class="nav-item"><a class="nav-link <?= activo('a_solicitudes') ?>" href="<?= e(url('a_solicitudes')) ?>"><i class="fa-solid fa-clipboard-list me-1"></i>Solicitudes</a></li>
            <li class="nav-item"><a class="nav-link <?= activo('a_recolecciones') ?>" href="<?= e(url('a_recolecciones')) ?>"><i class="fa-solid fa-truck-ramp-box me-1"></i>Recolecciones</a></li>
            <li class="nav-item"><a class="nav-link <?= activo('a_reportes') ?>" href="<?= e(url('a_reportes')) ?>"><i class="fa-solid fa-chart-column me-1"></i>Reportes</a></li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle <?= (activo('a_materiales') . activo('a_categorias')) !== '' ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown"><i class="fa-solid fa-boxes-stacked me-1"></i>Catálogos</a>
              <ul class="dropdown-menu shadow">
                <li><a class="dropdown-item" href="<?= e(url('a_usuarios')) ?>"><i class="fa-solid fa-users me-2"></i>Usuarios</a></li>
                <li><a class="dropdown-item" href="<?= e(url('a_empresas')) ?>"><i class="fa-solid fa-building me-2"></i>Empresas</a></li>
                <li><a class="dropdown-item" href="<?= e(url('a_categorias')) ?>"><i class="fa-solid fa-tags me-2"></i>Categorías</a></li>
                <li><a class="dropdown-item" href="<?= e(url('a_materiales')) ?>"><i class="fa-solid fa-recycle me-2"></i>Materiales</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?= e(url('a_premios')) ?>"><i class="fa-solid fa-gift me-2"></i>Premios</a></li>
                <li><a class="dropdown-item" href="<?= e(url('a_canjes')) ?>"><i class="fa-solid fa-list-check me-2"></i>Canjes de premios</a></li>
                <li><a class="dropdown-item" href="<?= e(url('a_puntos')) ?>"><i class="fa-solid fa-star me-2"></i>Puntos de ciudadanos</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?= e(url('a_config')) ?>"><i class="fa-solid fa-gear me-2"></i>Configuración</a></li>
              </ul>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    <?php endif; ?>
  </div>
</nav>

<main id="contenido" class="container py-3 py-lg-4">
<?= flash_render() ?>
