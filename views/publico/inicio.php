<?php /** RECICLA+ | Página de inicio (pública, Mobile First) */ ?>
<div class="hero-recicla mb-3">
  <span class="hero-badge mb-3"><i class="fa-solid fa-mobile-screen"></i> Diseñado para el celular</span>
  <h1 class="mb-2">Recicla desde tu celular<br>y pide que lo recojan en tu casa</h1>
  <p class="mb-4 opacity-75">Consulta qué materiales son reciclables, publica lo que quieres entregar y solicita la recolección a domicilio a una empresa operadora.</p>
  <div class="d-grid gap-2 d-sm-flex">
    <a class="btn btn-amarillo btn-accion px-4" href="<?= e(url('registro')) ?>"><i class="fa-solid fa-user-plus me-2"></i>Quiero reciclar</a>
    <a class="btn btn-outline-light btn-accion px-4" href="<?= e(url('materiales')) ?>"><i class="fa-solid fa-list-check me-2"></i>Ver materiales</a>
  </div>
</div>

<div class="row g-2 mb-4">
  <div class="col-6 col-lg-3">
    <div class="stat-chip"><div class="icono mb-1"><i class="fa-solid fa-recycle"></i></div><div class="valor"><?= (int) Material::contar(true) ?></div><div class="etiqueta">Materiales</div></div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-chip"><div class="icono mb-1"><i class="fa-solid fa-tags"></i></div><div class="valor"><?= (int) Material::totalCategorias() ?></div><div class="etiqueta">Categorías</div></div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-chip"><div class="icono mb-1"><i class="fa-solid fa-building"></i></div><div class="valor"><?= (int) Empresa::contar(true) ?></div><div class="etiqueta">Empresas</div></div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-chip"><div class="icono mb-1"><i class="fa-solid fa-users"></i></div><div class="valor"><?= (int) Usuario::contar('ciudadano') ?></div><div class="etiqueta">Ciudadanos</div></div>
  </div>
</div>

<h2 class="seccion-titulo mb-2"><i class="fa-solid fa-tags texto-verde me-2"></i>¿Qué puedo reciclar?</h2>
<div class="row g-2 mb-4">
  <?php foreach ($categorias as $c): ?>
    <div class="col-6 col-lg-3">
      <a class="tarjeta tarjeta-categoria p-3 h-100 d-block text-dark" href="<?= e(url('materiales', ['categoria' => (int) $c['id']])) ?>">
        <div class="icono-circulo mb-2" style="background:<?= e($c['color']) ?>"><i class="fa-solid <?= e($c['icono']) ?>"></i></div>
        <div class="fw-bold small"><?= e($c['nombre']) ?></div>
        <div class="hint"><?= (int) $c['total_materiales'] ?> materiales</div>
      </a>
    </div>
  <?php endforeach; ?>
</div>

<div class="tarjeta p-3 mb-4">
  <h2 class="seccion-titulo mb-3"><i class="fa-solid fa-diagram-project texto-verde me-2"></i>Cómo funciona</h2>
  <div class="row g-3">
    <?php
    $pasos = [
        ['fa-user-plus', '1. Crea tu cuenta', 'Regístrate con tu correo, dirección y barrio.'],
        ['fa-box-open', '2. Publica el material', 'Indica material, cantidad y toma una foto con tu celular.'],
        ['fa-calendar-check', '3. Solicita la recolección', 'Una empresa operadora acepta y programa la visita.'],
        ['fa-circle-check', '4. Entrega y consulta', 'Sigue el estado en tiempo real y revisa tu historial.'],
    ];
    foreach ($pasos as [$ico, $tit, $des]): ?>
      <div class="col-12 col-lg-3">
        <div class="d-flex gap-3">
          <div class="icono-circulo icono-suave"><i class="fa-solid <?= $ico ?>"></i></div>
          <div><div class="fw-bold small"><?= e($tit) ?></div><div class="hint"><?= e($des) ?></div></div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-2">
  <h2 class="seccion-titulo mb-0"><i class="fa-solid fa-star texto-verde me-2"></i>Materiales frecuentes</h2>
  <a class="btn btn-sm btn-outline-recicla" href="<?= e(url('materiales')) ?>">Ver todos <i class="fa-solid fa-arrow-right ms-1"></i></a>
</div>
<div class="grid-materiales mb-4">
  <?php foreach ($materiales as $m): ?>
    <a class="tarjeta p-2 text-dark d-block h-100" href="<?= e(url('material', ['id' => (int) $m['id']])) ?>">
      <?php if (!empty($m['imagen'])): ?>
        <img class="foto-material" src="<?= e(urlFoto($m['imagen'])) ?>" alt="<?= e($m['nombre']) ?>" loading="lazy">
      <?php else: ?>
        <div class="placeholder-foto" style="height:9.5rem;background:<?= e($m['categoria_color']) ?>22;color:<?= e($m['categoria_color']) ?>">
          <i class="fa-solid <?= e($m['icono']) ?>"></i>
        </div>
      <?php endif; ?>
      <div class="fw-bold small mt-2"><?= e($m['nombre']) ?></div>
      <div class="hint"><?= e($m['categoria']) ?> · <?= e(unidadesMedida()[$m['unidad_medida']] ?? '') ?></div>
    </a>
  <?php endforeach; ?>
</div>

<div class="tarjeta p-3 text-center">
  <div class="icono-circulo icono-suave mx-auto mb-2"><i class="fa-solid fa-hand-holding-heart"></i></div>
  <h2 class="h6 fw-bold">Tu material puede tener una segunda vida</h2>
  <p class="hint mb-3">Regístrate gratis y publica los materiales que ya no uses. Una empresa operadora los recogerá en tu domicilio.</p>
  <a class="btn btn-recicla btn-accion w-100" href="<?= e(url('registro')) ?>"><i class="fa-solid fa-user-plus me-2"></i>Crear mi cuenta</a>
</div>
