# 🚀 Desplegar RECICLA+ en InfinityFree (hosting gratuito PHP + MySQL)

Guía paso a paso desde cero. Tiempo estimado: **15 minutos**.
Panel: <https://dash.infinityfree.com/>

---

## 0. Lo que va a necesitar

| Dato | Dónde se obtiene |
|---|---|
| Cuenta InfinityFree | <https://dash.infinityfree.com/> (registro gratis) |
| Subdominio o dominio | Se crea junto con la cuenta de hosting (ej. `recicla.wuaze.com`) |
| Datos de MySQL | Panel → **MySQL Databases** (host, base, usuario, contraseña) |
| Cliente FTP (opcional pero recomendado) | FileZilla · host `ftpupload.net`, puerto 21, su usuario `if0_...` |

> ⚠️ El plan gratuito **no tiene SSH ni cron**, y `mail()` está deshabilitado.
> Por eso la recuperación de contraseña muestra el enlace en pantalla (modo local, ya implementado).

---

## 1. Crear la cuenta de hosting

1. Entre a <https://dash.infinityfree.com/> y cree la cuenta (o inicie sesión).
2. Menú **Create Account** → elija un **subdominio gratuito** (ej. `recicla.wuaze.com`) o conecte su dominio.
3. Espere a que el panel muestre el estado **Active** (puede tardar unos minutos).

## 2. Elegir PHP 8.2

1. En el panel, abra su cuenta → **PHP Configuration** (o *Alter PHP Config*).
2. Seleccione **PHP 8.2** (el sistema funciona desde **PHP 8.0**; con 8.2 va probado).
3. Verifique que estén activas las extensiones: **pdo_mysql**, **mbstring**, **fileinfo**, **gd**
   (marcadas por defecto; si falta `gd`, las fotos no se redimensionan pero el sistema funciona).

## 3. Crear la base de datos

1. Panel → **MySQL Databases** → **Create Database**.
2. Nombre: `recicla` → el panel le mostrará algo como:

```
Host:      sql301.infinityfree.com
Database:  if0_12345678_recicla
Username:  if0_12345678
Password:  (el de su cuenta InfinityFree)
```

3. **Anote los cuatro datos**: los necesita en el paso 5.

## 4. Importar las tablas (17 tablas + catálogo)

1. Panel → **MySQL Databases** → botón **Admin / phpMyAdmin** de la base creada.
2. Pestaña **Importar** → *Choose file* → suba `deploy/infinityfree/recicla_hosting.sql`
   (ese archivo ya viene sin `CREATE DATABASE`, listo para hosting).
3. Ejecutar. Debe terminar con el mensaje de éxito y verse **17 tablas**.
4. *(Opcional, recomendado para la presentación)* Importe también
   `deploy/infinityfree/demo_datos_hosting.sql` → carga 6 solicitudes, 3 recolecciones (54 kg),
   los puntos, un canje y una ruta para que los tableros se vean con datos.

## 5. Editar las credenciales

Abra **`config/config.local.php`** (viene dentro del proyecto, ya con el formato correcto)
y escriba los datos del paso 3:

```php
define('DB_HOST', 'sql301.infinityfree.com');
define('DB_PORT', '3306');
define('DB_NAME', 'if0_12345678_recicla');
define('DB_USER', 'if0_12345678');
define('DB_PASS', 'su_contraseña');
define('APP_DEBUG', false);
```

> Este archivo **no se sube a GitHub** (está en `.gitignore`): así la contraseña
> no queda pública. En el repositorio está la plantilla `config/config.local.example.php`.

## 6. Subir los archivos

**Opción A · Administrador de archivos del hosting (más fácil)**
1. Panel → **File Manager** (o *Online File Manager*).
2. Entre a la carpeta **`htdocs`** ← ahí va la web (bórrele el `index2.html` de ejemplo si aparece).
3. **Upload** → suba el ZIP del proyecto → luego **Extract** en `htdocs`.
4. Verifique que quede `htdocs/index.php` (no `htdocs/RECICLA/index.php`).

**Opción B · FTP con FileZilla (más rápido para muchos archivos)**
1. Descargue FileZilla → nuevo sitio: host `ftpupload.net`, puerto `21`, usuario `if0_...`, su contraseña.
2. Arrastre el **contenido** del proyecto a `/htdocs`.

## 7. Permisos de la carpeta de evidencias

La carpeta `uploads/` (con sus subcarpetas `solicitudes/`, `recolecciones/`, `materiales/`, `premios/`)
debe poder recibir archivos. En el administrador de archivos seleccione `uploads` →
**Permissions** → `755` (y lo mismo para las subcarpetas si el hosting lo pide).

## 8. Probar el sistema

1. Abra `https://su-subdominio/index.php` → debe ver la página de RECICLA+ con el catálogo.
2. Ingrese con `admin@recicla.local` / `Admin123*`.
3. **Cambie de inmediato las contraseñas demo** (Usuarios → Editar) y cree su propia empresa operadora.
4. Haga una prueba real: registre un ciudadano de prueba, publique un material **con foto**
   (así confirma que `uploads/` es escribible) y recorra el flujo hasta *Recolectada*
   para ver los puntos acreditados.

## 9. Checklist final de seguridad

- [ ] `APP_DEBUG` en `false`
- [ ] Contraseñas demo cambiadas (admin, operadores, ciudadanos)
- [ ] `config/config.local.php` solo en el hosting (nunca en GitHub)
- [ ] `deploy/infinityfree/recicla_hosting.sql` no quedó accesible por web
      (los `.htaccess` del proyecto ya bloquean `*.sql`, y `config/`, `models/`, `views/`, `database/`)
- [ ] Respaldos: phpMyAdmin → **Exportar** → SQL, una vez al mes

---

## Solución de problemas frecuentes

| Síntoma | Causa y solución |
|---|---|
| “No fue posible conectar con MySQL” | Los datos de `config/config.local.php` no coinciden con los del panel, o importó el SQL en otra base. |
| **Error 500** al abrir el sitio | Suele ser el PHP muy viejo: cámbielo a **8.2** en *PHP Configuration*. Si persiste, revise que subió el contenido y no la carpeta contenedora. |
| Las fotos no se ven | `uploads/` sin permiso de escritura (paso 7) o el archivo se subió sin la carpeta `uploads/`. |
| Los estilos se ven “sin diseño” | Faltó subir `assets/` o la subida quedó incompleta. |
| Al importar el SQL da error | Importe `recicla_hosting.sql` **estando dentro** de la base creada (no use el que tiene `CREATE DATABASE`). |
| El primer acceso muestra una pantalla de verificación | Es la protección anti-bot de InfinityFree: espere unos segundos y recargue. |

> 💡 **Para la clase:** cree un usuario por aprendiz con rol *ciudadano* y una empresa operadora
> “de prueba”; así todos publican solicitudes y usted demuestra el flujo completo desde el proyector.

---

## 🧭 Lecciones del despliegue real (recicla.42web.io · septiembre 2026)

Esto fue lo que realmente ocurrió al publicar RECICLA+ y cómo se resolvió.
Sirve tal cual para el próximo proyecto PHP+MySQL en InfinityFree.

### Datos reales de la cuenta
- La cuenta agrupa **varios sitios**: por FTP se ven como carpetas hermanas
  (`amatista.42web.io/htdocs/`, `recicla.42web.io/htdocs/`). Suba siempre dentro de la carpeta
  del sitio, en su `htdocs`.
- El servidor MySQL de la cuenta es **`sql304.infinityfree.com`** (el mismo para todas las bases);
  la base quedó como `if0_42646470_recicla`.
- **No hay acceso a MySQL desde fuera**: `sql304.infinityfree.com` ni resuelve DNS desde un PC
  externo. La base se administra **solo** con el phpMyAdmin del panel, o desde PHP dentro del hosting.

### Compatibilidad encontrada
| Punto | Resultado real |
|---|---|
| PHP del hosting | **8.4.25**, con `pdo_mysql`, `mysqli`, `gd`, `fileinfo`, `mbstring` ✅ |
| `uploads/` | Escribible: las fotos se guardan y se sirven ✅ |
| `.htaccess` del proyecto | Funciona: `config/`, `database/`, `models/`, `views/` responden **403** y el PHP dentro de `uploads/` **no** se ejecuta (por eso no se usa `php_flag`, que daría error 500) |
| Funciones desactivadas | `exec`, `shell_exec`, `system`, `sleep`, `set_time_limit`, `getallheaders`, `curl_multi_exec`… (el proyecto no las usa) |
| `SHOW DATABASES` | Denegado: no se pueden listar bases desde PHP, hay que probar por nombre |

### Tres tropiezos reales (y su solución)
1. **La importación por phpMyAdmin quedó incompleta.** Entraron categorías, materiales, empresas,
   vehículos, premios y configuración, pero **`usuarios` quedó vacío**: el sistema abría, pero
   nadie podía entrar. ➜ Después de importar, **verifique siempre los conteos**
   (`SELECT COUNT(*) FROM usuarios;` debe dar 5) y, si falta, ejecute de nuevo solo el bloque
   `INSERT INTO usuarios` del SQL.
2. **La subida por FTP quedó a medias.** Llegaron 63 de 87 archivos (faltaban `assets/`, `config/`,
   `controllers/` y `database/`) y el sitio decía “no se puede conectar” porque faltaba
   `config/config.php`. ➜ Suba **el proyecto completo** y compare el listado del servidor con el del PC.
3. **`curl` y los scripts automáticos no pasan.** InfinityFree responde un **reto JavaScript**
   (`aes.js` + cookie `__test`) a quien no parezca un navegador y bloquea los POST.
   ➜ Verifique con un **navegador real**; para pruebas automáticas, ejecute el script *dentro* del
   hosting (subirlo por FTP, abrirlo con el navegador y borrarlo al terminar).

### Despliegue exprés (resumen para la próxima vez)
1. Suba el proyecto completo dentro del `htdocs` del sitio (FTP o ZIP + descomprimir).
2. Cree la base en *MySQL Databases* e importe **`deploy/infinityfree/recicla_hosting.sql`**.
3. **Compruebe los conteos**: 5 usuarios, 7 categorías, 23 materiales, 2 empresas, 8 premios.
4. Cree `config/config.local.php` con host, base, usuario y contraseña del panel, con `APP_DEBUG` en `false`.
5. Entre con `admin@recicla.local` y **cambie de inmediato las contraseñas de prueba**.
