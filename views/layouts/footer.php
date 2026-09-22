<?php /** RECICLA+ | Pie de página, navegación inferior móvil y scripts */ ?>
</main>

<?php if (!empty($usuario) && ($navTipo ?? '') === 'ciudadano'): ?>
<nav class="nav-inferior" aria-label="Navegación principal">
  <a class="ni-item <?= (activo('panel') . activo('mi_solicitud')) !== '' ? 'activo' : '' ?>" href="<?= e(url('panel')) ?>">
    <i class="fa-solid fa-house"></i><span>Inicio</span>
  </a>
  <a class="ni-item <?= (activo('materiales') . activo('material')) !== '' ? 'activo' : '' ?>" href="<?= e(url('materiales')) ?>">
    <i class="fa-solid fa-list-check"></i><span>Materiales</span>
  </a>
  <a class="ni-item ni-principal" href="<?= e(url('publicar')) ?>">
    <i class="fa-solid fa-circle-plus"></i><span>Reciclar</span>
  </a>
  <a class="ni-item <?= activo('mis_solicitudes') ?>" href="<?= e(url('mis_solicitudes')) ?>">
    <i class="fa-solid fa-clipboard-list"></i><span>Solicitudes</span>
  </a>
  <a class="ni-item <?= (activo('perfil') . activo('historial')) !== '' ? 'activo' : '' ?>" href="<?= e(url('perfil')) ?>">
    <i class="fa-solid fa-user"></i><span>Perfil</span>
  </a>
</nav>
<?php endif; ?>

<footer class="pie-recicla">
  <div class="container text-center">
    <p class="mb-1"><i class="fa-solid fa-recycle me-1"></i><strong><?= e(config_get('nombre_sistema', 'RECICLA+')) ?></strong> · Sistema web de gestión de reciclaje</p>
    <p class="mb-0 small">
      <?= e(config_get('horario_atencion', '')) ?> ·
      <a href="mailto:<?= e(config_get('email_contacto', '')) ?>"><?= e(config_get('email_contacto', '')) ?></a> ·
      <?= e(config_get('telefono_contacto', '')) ?>
    </p>
    <p class="mb-0 small text-muted">Versión <?= e(config_get('version', '1.0')) ?> · PHP <?= e(PHP_VERSION) ?> · MySQL</p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(BASE_URL) ?>/assets/js/recicla.js"></script>
</body>
</html>
