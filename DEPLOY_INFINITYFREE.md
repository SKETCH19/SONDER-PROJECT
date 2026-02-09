# Despliegue en InfinityFree (GRATIS con PHP)

InfinityFree es la opción perfecta para Sonder: es completamente GRATIS, incluye PHP, MySQL/SQLite y no necesita tarjeta de crédito.

## Paso 1: Crear Cuenta en InfinityFree

1. Ve a **https://www.infinityfree.net**
2. Click en **"Get Started"** (esquina superior derecha)
3. Completa el formulario:
   - **Email**: Tu correo
   - **Password**: Contraseña segura
   - **Confirm Password**: Repite la contraseña
4. Click en **"Create Account"**
5. **Confirma tu email** (busca en tu bandeja de entrada)

## Paso 2: Crear una Aplicación Web

1. Después de confirmar, haz login en el Dashboard
2. Click en **"Create Website"** o **"New Website"**
3. Selecciona **subdomain** (te dará una URL gratis como `tu_sitio.infinityfreeapp.com`)
   - O USA TU PROPIO DOMINIO si ya tienes uno
4. Completa:
   - **Website Name**: `sonder` (o lo que prefieras)
   - **Domain**: Elige un subdominio
5. Click en **"Create"**

## Paso 3: Subir los Archivos (Opción A: FTP - Recomendado)

### Obtener Credenciales FTP

1. En el Dashboard, localiza tu sitio web
2. Click en el sitio para ver los detalles
3. Busca la sección **"FTP Information"** o **"SSH/SFTP"**
4. Copia:
   - **FTP Host**: `ftpupload.net` (o similar)
   - **FTP Username**: Tu usuario FTP
   - **FTP Password**: Tu contraseña FTP
5. **Nota**: El **FTP Port** es generalmente `21` (o `22` para SFTP)

### Usar FileZilla (Cliente FTP Gratuito)

1. **Descarga FileZilla**: https://filezilla-project.org/ (selecciona FileZilla Client)
2. **Abre FileZilla** y completa en la barra superior:
   - **Host**: Tu FTP Host
   - **Username**: Tu usuario FTP
   - **Password**: Tu contraseña FTP
   - **Port**: 21 (o 22 si es SFTP)
3. Click en **"Quickconnect"**

### Subir Archivos

1. En la **sección izquierda** (computadora local), navega a tu carpeta `SONDER-PROJECT/sonder`
2. **Selecciona TODO** (Ctrl+A):
   - `*.php`
   - `css/` (carpeta)
   - `js/` (carpeta)
   - `includes/` (carpeta)
   - `.htaccess`
   - **NO** incluyas `.git` ni `Base de datos.txt` (opcional)

3. **Arrastra** a la sección derecha o click derecho → **Upload**
4. El FTP empezará a subir (puede tardar 1-2 minutos)

### Crear Carpeta de Uploads

1. En la **sección derecha** (servidor), haz clic derecho → **Create folder**
2. Nombre: `uploads`
3. Presiona Enter

## Paso 4: Subir Archivos (Opción B: Administrador de Archivos Web)

Si prefieres no instalar FTP:

1. En el Dashboard del sitio, click en **"File Manager"**
2. Por defecto estás en `public_html`
3. Click en **"Upload"** o arrastra archivos
4. Sube todos los archivos de la carpeta `sonder/`:
   - Los archivos `.php`
   - Las carpetas `css/`, `js/`, `includes/`
   - El archivo `.htaccess`

5. Crea una carpeta llamada `uploads`:
   - Click derecho → **Create Folder**
   - Nombre: `uploads`

## Paso 5: Configurar Base de Datos (Si usas MySQL)

Para usar MySQL en lugar de SQLite (recomendado para hosting):

### Crear BD MySQL

1. En el Dashboard, ve a **"Databases"** (o **MySQL Manager**)
2. Click en **"Create New Database"**
3. Nombre: `sonder_db`
4. Click en **"Create"**
5. Se crearán:
   - **Database Name**: `nombreusuario_sonder_db`
   - **Database User**: `nombreusuario_usuario`
   - **Password**: Se genera automáticamente
   - **Host**: `localhost` (generalmente)

### Modificar config.php para MySQL

Después de crear la BD, actualiza tu archivo `includes/config.php`:

```php
<?php
// Cambiar de SQLite a MySQL
// DE ESTO:
// $pdo = new PDO("sqlite:" . DB_PATH);

// A ESTO:
define('DB_HOST', 'localhost');
define('DB_USER', 'nombreusuario_usuario');  // Reemplaza
define('DB_PASSWORD', 'tu_password');         // Reemplaza
define('DB_NAME', 'nombreusuario_sonder_db'); // Reemplaza

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASSWORD,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
    
    // ... resto del código igual ...
}
```

Si prefieres seguir usando **SQLite**, no cambies nada - debería funcionar igual.

## Paso 6: Acceder a tu App

1. Tu sitio estará disponible en:
   ```
   https://tusitio.infinityfreeapp.com
   ```
   
   Ejemplo:
   ```
   https://sonder.infinityfreeapp.com
   ```

2. Ingresa esta URL en tu navegador
3. Deberías ver la página de **inicio de Sonder**
4. Prueba:
   - Click en **"Registrarse"**
   - Crea una cuenta de prueba
   - Sube un avatar
   - Prueba el registro y login

## Paso 7: Solucionar Problemas

### Error 500 - Internal Server Error

**Causa más probable**: Permisos de carpetas

1. Abre **File Manager** en InfinityFree
2. Haz clic derecho en carpeta `uploads` → **Change Permissions**
3. Establece a `755`
4. Haz lo mismo con `includes/`
5. Recarga la página

### Errores de Base de Datos

Si tienes errores de BD:

1. Verifica que aún uses SQLite (sonder.db se crea automáticamente) O
2. Si usas MySQL, verifica las credenciales en `config.php`
3. Si ves "Cannot read/write database":
   - Asegúrate de que el servidor tiene permisos de escritura
   - En File Manager: `uploads/` y `includes/` deben ser `755`

### La app es lenta

Es normal en el plan gratuito. Cosas que puedes hacer:

- Comprimir las imágenes de avatar
- Esperar 10-20 segundos si es la primera carga del día
- Considera actualizar a plan premium si crece mucho

## Configuración de Dominio Personalizado (Opcional)

Si tienes tu propio dominio:

1. En InfinityFree, ve a **"Domains"** o **"Addon Domains"**
2. Apunta los nameservers de tu dominio a InfinityFree:
   - **NS1**: `ns1.infinityfree.net`
   - **NS2**: `ns2.infinityfree.net`
3. Espera 24-48 horas para que se propague

## Límites y Características del Plan Gratuito

✅ **Incluido**:
- Hosting ilimitado (almacenamiento)
- Ancho de banda ilimitado
- PHP ilimitado
- Bases de datos MySQL (aunque limitadas)
- Subdominio gratis
- SSL/HTTPS automático

⚠️ **Limitaciones**:
- CPU limitado (pero suficiente para Sonder)
- Si al sitio no se accede en 24 horas, se suspende temporalmente
- Soporte comunitario (no oficial)

## Mantener Activo el Sitio

Para evitar suspensión por inactividad:

- Visita tu sitio al menos 1 vez cada 24 horas O
- Configura un "uptime monitor" externo:
  - Usa: https://uptimerobot.com (GRATIS)
  - Configura para que visite tu sitio cada hora
  - Así nunca se suspenderá

## Actualizar el Código (Cuando Hagas Cambios)

Cuando actualices el código en GitHub:

1. Descarga los cambios:
   ```bash
   cd ~/SONDER-PROJECT
   git pull origin develop
   ```

2. Sube los archivos nuevamente vía FTP (FileZilla)
3. Sobrescribe los archivos cuando pregunte
4. Recarga la página en el navegador (Ctrl+F5)

## Respaldo de Datos

Regularmente, descarga tu BD (si usas MySQL):

1. En InfinityFree, ve a **"phpMyAdmin"**
2. Selecciona tu base de datos
3. Click en **"Export"**
4. Descarga el archivo SQL de respaldo

O si usas SQLite: Descarga el archivo `sonder.db` vía FTP

## Escalabilidad Futura

Cuando Sonder crezca, puedes:

- **Upgrade a Premium en InfinityFree**: Mejor performance, sin suspensión
- **Migrar a Hostinger**: $2.99/mes, mejor velocidad y soporte
- **Migrar a un VPS** (DigitalOcean, Linode): Control total

---

## ¡Listo!

Tu app Sonder debería estar en línea y lista para que otros se registren. 

**Tu URL**: `https://tusitio.infinityfreeapp.com`

Comparte el enlace con tus amigos para que se registren y empiecen a usar la aplicación. 🚀
