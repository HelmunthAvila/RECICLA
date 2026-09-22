<?php
/**
 * RECICLA+ | Plantilla de credenciales  (config.local.example.php)
 * ------------------------------------------------------------------
 * CÓMO USARLA
 *   1. Copie este archivo como  config/config.local.php
 *   2. Descomente UNO de los dos bloques (WAMP o InfinityFree).
 *   3. Complete los datos reales del hosting.
 *
 * El archivo config/config.local.php NO se sube a GitHub (.gitignore)
 * porque contiene la contraseña de la base de datos.
 */

/* ----------------------------- OPCIÓN A: WAMP local -----------------------------
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'recicla');
define('DB_USER', 'root');
define('DB_PASS', '');
define('APP_DEBUG', true);          // muestra los errores en pantalla (solo desarrollo)
---------------------------------------------------------------------------------- */

/* -------------------- OPCIÓN B: InfinityFree (producción) -----------------------
   Los datos exactos aparecen en  https://dash.infinityfree.com/  →  MySQL Databases
   (host, nombre de la base y usuario). La contraseña es la de SU cuenta del panel.

define('DB_HOST', 'sqlXXX.infinityfree.com');   // ej: sql301.infinityfree.com
define('DB_PORT', '3306');
define('DB_NAME', 'if0_XXXXXXXXXX_recicla');    // nombre que le dio el panel
define('DB_USER', 'if0_XXXXXXXXXX');            // usuario de la cuenta
define('DB_PASS', 'la_contraseña_del_panel');
define('APP_DEBUG', false);                     // en producción los errores NO se muestran
---------------------------------------------------------------------------------- */
