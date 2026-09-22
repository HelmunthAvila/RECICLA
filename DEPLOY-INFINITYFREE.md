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
