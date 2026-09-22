# RECICLA+ · Sistema Web de Gestión de Reciclaje

Aplicación web **Mobile First** desarrollada en **PHP 8 + MySQL 8 + Bootstrap 5**, que permite a los ciudadanos
consultar qué materiales son reciclables, publicar los elementos que desean entregar, adjuntar fotografías
tomadas desde el celular y **solicitar la recolección a domicilio**; a las **empresas operadoras**, gestionar,
programar y registrar las recolecciones; y al **administrador**, controlar usuarios, empresas, materiales,
categorías, solicitudes, recolecciones y reportes.

---

## 1. Requisitos

| Componente | Versión usada |
|---|---|
| PHP | 8.2.29 (extensiones: `pdo_mysql`, `gd`, `fileinfo`, `mbstring`, `openssl`) |
| MySQL | 8.4.7 |
| Apache | Servidor WAMP (con `mod_rewrite`/`AllowOverride` para los `.htaccess`) |
| Frontend | Bootstrap 5.3 + Font Awesome 6.5 + HTML5 + CSS3 + JavaScript (CDN) |

## 2. Instalación en WAMP

1. Copiar el proyecto en `C:\wamp64\www\RECICLA` (ya ubicado).
2. Iniciar MySQL y Apache en WAMP.
3. Crear la base de datos e importar los datos semilla:

   ```bash
   cd C:\wamp64\www\RECICLA
   mysql -uroot -h127.0.0.1 < database/recicla.sql
   ```

   > El script crea la base **recicla** (14 tablas), las categorías, 23 materiales, 2 empresas
   > operadoras, vehículos y las cuentas de prueba.

4. Verificar las credenciales de conexión. `config/config.local.php` no existe por defecto y el sistema
   usa los valores de desarrollo (`root` sin contraseña en `127.0.0.1:3306`, base `recicla`). Para cambiarlos:

   ```php
   <?php // config/config.local.php  (archivo NO versionado)
   define('DB_HOST', '127.0.0.1');
   define('DB_PORT', '3306');
   define('DB_NAME', 'recicla');
   define('DB_USER', 'root');
   define('DB_PASS', 'mi_contraseña');
   ```

5. (Opcional) Cargar el **historial de demostración** para que los tableros y reportes se vean con
   información real durante una presentación:

   ```bash
   mysql -uroot -h127.0.0.1 recicla < database/demo_datos.sql
   ```

   Crea 6 solicitudes en distintos estados (registrada, programada, 3 recolectadas y 1 cancelada),
   3 recolecciones con 54 kg, los puntos acreditados, un premio canjeado, una ruta y notificaciones.
   Las fechas son relativas al día de ejecución.

6. Bases de datos **ya instaladas** antes de estos cambios: ejecute una sola vez las migraciones
   (son idempotentes y conservan los datos existentes):

   ```bash
   mysql -uroot -h127.0.0.1 recicla < database/migracion_puntos_premios.sql
   mysql -uroot -h127.0.0.1 recicla < database/migracion_acentos_catalogo.sql
   ```

7. Abrir en el navegador: **http://localhost/RECICLA/** (o desde el celular en la misma red:
   `http://IP-DEL-PC/RECICLA/`).

## 3. Cuentas de prueba

### 2.1 Publicarlo en un hosting gratuito (InfinityFree)

Guía paso a paso: **[DEPLOY-INFINITYFREE.md](DEPLOY-INFINITYFREE.md)** (crear la cuenta, PHP 8.2,
base de datos, importar, editar credenciales, subir por FTP o administrador de archivos y probar).

Para hosting se incluyen archivos ya adaptados en `deploy/infinityfree/`:

| Archivo | Para qué sirve |
|---|---|
| `recicla_hosting.sql` | Esquema + datos semilla **sin `CREATE DATABASE`** (el hosting ya entrega la base) |
| `demo_datos_hosting.sql` | Historial de demostración, también sin `CREATE DATABASE` |
| `config/config.local.php` | Plantilla de credenciales: es el **único archivo que debe editar** en el hosting |

Requisitos del hosting: **PHP 8.0 o superior** (probado en 8.2) con `pdo_mysql`, `mbstring`,
`fileinfo` y `gd`; Apache con `.htaccess`; permiso de escritura en `uploads/`.

| Rol | Correo | Contraseña |
|---|---|---|
| Administrador | `admin@recicla.local` | `Admin123*` |
| Empresa operadora | `operador@recicla.local` | `Operador123*` |
| Empresa operadora 2 | `operador2@recicla.local` | `Operador123*` |
| Ciudadano | `ciudadano@recicla.local` | `Ciudadano123*` |
| Ciudadano 2 | `pedro@recicla.local` | `Ciudadano123*` |

## 4. Estructura del proyecto

```
RECICLA/
├── index.php                Front controller: enrutador + control de roles + CSRF
├── config/
│   ├── config.php           Sesión segura, PDO (singleton), helpers, CSRF, cargue de fotos
│   └── validar.php          Clase Validador (validación de formularios)
├── controllers/             AuthController · CatalogoController · CiudadanoController
│                            EmpresaController · AdminController
├── models/                  Usuario · Material · Empresa · Solicitud · Recoleccion · Reporte
├── views/
│   ├── layouts/             header.php (navbar) y footer.php (nav inferior móvil)
│   ├── partials/            solicitud_card.php · bloque_material.php
│   ├── publico/             inicio, materiales, material_detalle
│   ├── auth/                login, registro, recuperar, restablecer
│   ├── ciudadano/           panel, publicar, solicitudes, solicitud, historial, perfil, notificaciones
│   ├── empresa/             panel, solicitudes, solicitud, programar, recoleccion, rutas, historial, perfil
│   ├── admin/               panel, usuarios, empresas, categorias, materiales, solicitudes,
│   │                        recolecciones, reportes, configuracion y sus formularios
│   └── errores/             404.php, 500.php
├── assets/
│   ├── css/recicla.css      Estilos Mobile First (verde/amarillo, tarjetas, nav inferior)
│   └── js/recicla.js        Cámara/galería, vista previa, bloques repetibles, confirmaciones
├── uploads/                 solicitudes/ · recolecciones/ · materiales/  (PHP deshabilitado)
└── database/recicla.sql     Esquema + datos semilla
    (incluye el programa de puntos y premios)
    ├── demo_datos.sql                    Historial de demostración (opcional)
    ├── migracion_puntos_premios.sql      Puntos y premios sobre una base existente
    └── migracion_acentos_catalogo.sql    Corrección de tildes del catálogo
```

## 4.1 Programa de puntos y premios (incentivos para reciclar)

Para motivar la participación ciudadana, RECICLA+ acredita **puntos** al ciudadano cuando la
empresa operadora **confirma la recolección** (RF-14). Los puntos se cambian por **premios**
del catálogo.

| Elemento | Regla por defecto |
|---|---|
| Puntos por unidad recolectada | Se configuran por material (`materiales.puntos_por_unidad`): papel 10/kg, plásticos 15/kg, vidrio 8/kg, metales 25/kg (aluminio 30, cobre 40), electrónicos 20/kg (celulares 30), textiles 10/kg, madera 5/kg |
| Bono por recolección completada | 20 puntos (`puntos_bonus_recoleccion`, editable en Configuración) |
| Mínimo para canjear | 100 puntos (`puntos_minimos_canje`) |
| Niveles | Semilla (0-199), Reciclador activo (200-799), Guardián verde (800-1999), Embajador del reciclaje (≥2000) |

Tablas nuevas: `premios`, `puntos_movimientos` (historial auditable y firmado de puntos) y
`canjes` (con código CANJE-000001 y estados solicitado → entregado / cancelado).

Funcionamiento:
1. El ciudadano publica el material y solicita la recolección.
2. La empresa acepta, programa, marca en ruta y **registra la recolección**.
3. El sistema acredita los puntos (una fila por material + el bono) y notifica al ciudadano.
4. En **Mis puntos** el ciudadano ve su saldo, su nivel, su posición en el ranking y el historial.
5. En **Cambiar por premios** canjea el premio que alcance; el sistema descuenta puntos y stock,
   genera el código de canje y avisa a los administradores.
6. El administrador entrega el premio (**Canjes de premios** → "Marcar entregado") o lo cancela,
   en cuyo caso el sistema **devuelve los puntos y el stock** y notifica al ciudadano.
7. El administrador puede hacer **ajustes manuales** de puntos (positivos o negativos) con motivo
   obligatorio, y ver el reporte **Puntos y premios** (por material, por mes y top de ciudadanos).

Rutas nuevas: `mis_puntos`, `premios`, `canjear` (ciudadano) y `a_premios`, `a_premio`,
`a_premio_post`, `a_premio_estado`, `a_canjes`, `a_canje_estado`, `a_puntos`, `a_puntos_ajuste`
(administrador). Archivo: `models/Puntos.php`.

> Si la base ya estaba instalada, ejecute una sola vez:
> `mysql -uroot -h127.0.0.1 recicla < database/migracion_puntos_premios.sql`

Rutas internas: `index.php?p=<ruta>` (ver la tabla `$rutas` en `index.php`).
Cada ruta declara el rol requerido; las rutas de escritura terminan en `_post` y exigen token CSRF.

## 5. Base de datos (14 tablas)

`roles`, `usuarios`, `empresas`, `vehiculos`, `categorias`, `materiales`, `solicitudes`,
`solicitud_materiales`, `recolecciones`, `recoleccion_materiales`, `rutas`, `historial_solicitudes`,
`notificaciones`, `configuracion`.

Relaciones principales: `roles → usuarios → solicitudes → solicitud_materiales → materiales → categorias`
y `solicitudes → recolecciones → (empresas, vehiculos, rutas)`.
Índices en llaves foráneas, `estado`, `ciudad`, `barrio`, `fecha_disponible`, `fecha_programada` y
`numero` (único) para mantener el rendimiento con muchas filas (RNF-05).

## 6. Flujo de estados de la solicitud (RF-10)

```
REGISTRADA → EN REVISIÓN → ACEPTADA → PROGRAMADA → EN RUTA → RECOLECTADA
                    ↘ CANCELADA (en cualquier paso previo a la recolección)
```

El número de solicitud se genera automáticamente con el formato **REC-000001**.
Cada cambio de estado queda en `historial_solicitudes` y genera una notificación al ciudadano.

## 7. Requerimientos cubiertos

| Código | Requerimiento | Dónde |
|---|---|---|
| RF-01 | Registro de ciudadanos | `auth/registro`, `Usuario::crearCiudadano` |
| RF-02 | Inicio de sesión con rol automático | `auth/login`, `AuthController::login` |
| RF-03 | Recuperación de contraseña | `auth/recuperar`, `auth/restablecer` |
| RF-04/05 | Consulta y categorías de materiales | `CatalogoController`, `publico/materiales` |
| RF-06 | Publicación de material | `ciudadano/publicar` |
| RF-07 | Fotografía desde cámara o galería | `assets/js/recicla.js` + `guardarFoto()` |
| RF-08 | Crear solicitud con número REC-000001 | `Solicitud::crear` |
| RF-09 | Consulta de la solicitud | `ciudadano/solicitud` |
| RF-10 | Estados de la solicitud | `config/config.php` + `Solicitud::cambiarEstado` |
| RF-11 | Consulta y filtros de solicitudes | `empresa/solicitudes` |
| RF-12 | Aceptar solicitud | `Solicitud::aceptar` |
| RF-13 | Programar recolección | `empresa/programar` |
| RF-14 | Registrar recolección + evidencia | `empresa/recoleccion`, `Recoleccion::registrar` |
| RF-15 | Organización de rutas y zonas | `empresa/rutas` |
| RF-16/17/18 | Gestión de usuarios, materiales y empresas | `admin/*` |
| RF-19 | Consulta de todas las solicitudes | `admin/solicitudes`, `admin/solicitud` |
| RF-20 | Dashboard administrativo | `admin/panel` |
| RF-21 | Reportes (fecha, material, ciudad, barrio, empresa, estado, cantidad) | `admin/reportes` + exportación CSV |
| Incentivos | Puntos por reciclar y canje de premios (niveles, ranking, canjes) | `models/Puntos.php`, `ciudadano/puntos`, `ciudadano/premios`, `admin/premios`, `admin/canjes`, `admin/puntos` |

## 8. Seguridad implementada (RNF-04)

- Contraseñas con `password_hash()` (bcrypt) y verificación con `password_verify()`.
- **PDO con sentencias preparadas** en el 100 % de las consultas (sin concatenar datos del usuario).
- Escapado de salida con `htmlspecialchars()` (`e()`) → previene XSS.
- **Token CSRF** en todos los formularios que modifican datos.
- Sesiones con cookie `HttpOnly`, `SameSite=Lax` y `session_regenerate_id()` periódico.
- Bloqueo temporal tras 5 intentos fallidos de inicio de sesión.
- Autorización por rol en el enrutador **y** validación de propiedad del recurso en cada acción
  (un ciudadano no puede ver ni cancelar solicitudes de otro; una empresa no puede gestionar
  solicitudes asignadas a otra).
- Fotografías validadas por tipo real con `finfo`, tamaño máximo de 2 MB (`MAX_FOTO_BYTES`, igual al `upload_max_filesize` de WAMP), extensión segura y
  redimensionado a 1280 px; la carpeta `uploads/` tiene la ejecución de PHP deshabilitada.
- `.htaccess` que bloquea el acceso web a `config/`, `controllers/`, `models/`, `views/` y `database/`.

## 9. Rendimiento y Mobile First (RNF-01, RNF-02, RNF-05)

- Interfaz con botones grandes, formularios de una columna en móvil, tarjetas, navegación inferior fija
  con cinco accesos y sin desplazamiento horizontal.
- Paginación en todos los listados, `LIMIT`/`OFFSET` en las consultas y sólo los campos necesarios.
- Índices MySQL en las columnas usadas en `JOIN`, `WHERE` y `ORDER BY`.
- Optimización automática de las imágenes cargadas; `loading="lazy"` en las miniaturas.

## 10. Pendientes previstos para la versión 2 (no incluidos en la 1.0)

- Geolocalización y mapas de rutas optimizadas.
- Envío real de correos (recuperación de contraseña) y notificaciones por WhatsApp.
- Códigos QR para la entrega y estadísticas ambientales (CO₂ evitado).
- Copias de seguridad automáticas de la base de datos.
