<?php /** RECICLA+ | Configuración del sistema y datos técnicos */ ?>
<h1 class="h5 fw-bold mb-1"><i class="fa-solid fa-gear texto-verde me-2"></i>Configuración</h1>
<p class="hint">Parámetros generales que se muestran en el portal del ciudadano.</p>

<form method="post" action="<?= e(url('a_config_post')) ?>" novalidate>
  <?= csrf_campo() ?>
  <div class="tarjeta-form">
    <div class="row g-2">
      <?php
      $etiquetas = [
          'nombre_sistema'    => ['Nombre del sistema', 'text'],
          'email_contacto'    => ['Correo de contacto', 'email'],
          'telefono_contacto' => ['Teléfono de contacto', 'text'],
          'horario_atencion'  => ['Horario de atención', 'text'],
          'max_kg_solicitud'  => ['Máximo de kilogramos por solicitud', 'number'],
      ];
      foreach ($items as $item):
          if (!isset($etiquetas[$item['clave']])) { continue; }
          [$texto, $tipo] = $etiquetas[$item['clave']];
      ?>
        <div class="col-12 col-md-6">
          <label class="form-label" for="<?= e($item['clave']) ?>"><?= e($texto) ?></label>
          <input class="form-control" type="<?= e($tipo) ?>" id="<?= e($item['clave']) ?>" name="<?= e($item['clave']) ?>"
                 value="<?= e($item['valor']) ?>" maxlength="255">
        </div>
      <?php endforeach; ?>
    </div>
    <button class="btn btn-recicla btn-accion w-100 mt-3" type="submit"><i class="fa-solid fa-floppy-disk me-2"></i>Guardar configuración</button>
  </div>
</form>

<div class="row g-3">
  <div class="col-12 col-lg-6">
    <div class="tarjeta p-3">
      <span class="seccion-titulo d-block mb-2"><i class="fa-solid fa-server me-1"></i>Información técnica</span>
      <table class="table table-sm mb-0">
        <tbody>
          <tr><td>PHP</td><td><?= e(PHP_VERSION) ?></td></tr>
          <tr><td>Servidor</td><td><?= e($_SERVER['SERVER_SOFTWARE'] ?? 'PHP CLI') ?></td></tr>
          <tr><td>MySQL</td><td><?= e((string) db()->getAttribute(PDO::ATTR_SERVER_VERSION)) ?></td></tr>
          <tr><td>Base de datos</td><td><?= e(DB_NAME) ?></td></tr>
          <tr><td>Zona horaria</td><td><?= e(date_default_timezone_get()) ?></td></tr>
          <tr><td>Límite de subida (PHP)</td><td><?= e(ini_get('upload_max_filesize')) ?> / post <?= e(ini_get('post_max_size')) ?></td></tr>
          <tr><td>Límite por fotografía (sistema)</td><td><?= e(number_format(MAX_FOTO_BYTES / 1048576, 1)) ?> MB</td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="col-12 col-lg-6">
    <div class="tarjeta p-3">
      <span class="seccion-titulo d-block mb-2"><i class="fa-solid fa-database me-1"></i>Registros por tabla</span>
      <table class="table table-sm mb-0">
        <tbody>
        <?php
        $tablas = ['roles', 'usuarios', 'empresas', 'vehiculos', 'categorias', 'materiales',
                   'solicitudes', 'solicitud_materiales', 'recolecciones', 'recoleccion_materiales',
                   'rutas', 'historial_solicitudes', 'notificaciones', 'configuracion'];
        foreach ($tablas as $t):
            $total = (int) db()->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        ?>
          <tr><td><?= e($t) ?></td><td class="text-end"><?= $total ?></td></tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="tarjeta p-3 mt-3">
  <span class="seccion-titulo d-block mb-2"><i class="fa-solid fa-shield-halved me-1"></i>Controles de seguridad activos</span>
  <ul class="small mb-0 ps-3">
    <li>Contraseñas cifradas con <code>password_hash()</code> (bcrypt).</li>
    <li>Todas las consultas con <strong>sentencias preparadas</strong> (PDO).</li>
    <li>Salida escapada con <code>htmlspecialchars()</code> para prevenir XSS.</li>
    <li>Token CSRF en cada formulario que modifica información.</li>
    <li>Sesiones con <code>HttpOnly</code>, <code>SameSite=Lax</code> y regeneración periódica del identificador.</li>
    <li>Control de permisos por rol en el enrutador y en cada acción.</li>
    <li>Bloqueo temporal tras <?= (int) LOGIN_MAX_INTENTOS ?> intentos fallidos de inicio de sesión.</li>
    <li>Validación de fotografías por tipo real (finfo), tamaño máximo y extensión segura.</li>
    <li>Carpetas <code>config/</code>, <code>models/</code>, <code>views/</code> y <code>database/</code> bloqueadas para el navegador.</li>
  </ul>
</div>
