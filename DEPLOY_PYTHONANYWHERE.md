# Instalación y Despliegue en PythonAnywhere

## Paso 1: Crear una Cuenta en PythonAnywhere

1. Ve a https://www.pythonanywhere.com
2. Haz clic en **"Sign up for a free account"**
3. Selecciona el plan **"Beginner"** (GRATIS)
4. Completa el registro con:
   - Email
   - Nombre de usuario
   - Contraseña

## Paso 2: Clonar el Repositorio de GitHub

1. En el Dashboard de PythonAnywhere, haz clic en **"Bash"** para abrir una consola
2. Ejecuta los siguientes comandos:

```bash
# Navegar al directorio home
cd ~

# Clonar el repositorio
git clone https://github.com/SKETCH19/SONDER-PROJECT.git

# Entrar al directorio
cd SONDER-PROJECT/sonder

# Ver el contenido
ls -la
```

## Paso 3: Crear una Web App PHP

### Opción A: Manual (Recomendado)

1. En el Dashboard, haz clic en **"Web"**
2. Haz clic en **"Add a new web app"**
3. Selecciona:
   - Dominio: `tu_usuario.pythonanywhere.com` (automático)
   - Tecnología: **"PHP"**
   - Versión: **7.4** o superior
4. Haz clic en **"Next"**

### Opción B: Desde la Consola

```bash
# Instalar la aplicación web automáticamente
cp -r ~/SONDER-PROJECT/sonder /var/www/tu_usuario_pythonanywhere_com
```

## Paso 4: Configurar el Directorio Raíz (Web Root)

1. En el Dashboard, ve a **"Web"**
2. Busca la sección **"Source code"**
3. Cambia la ruta del **Source code** a:
   ```
   /home/tu_usuario/SONDER-PROJECT/sonder
   ```

4. En **"Working directory"**, asegúrate de que sea:
   ```
   /home/tu_usuario/
   ```

5. Guarda y **recarga** la web app

## Paso 5: Dar Permisos al Directorio de Uploads

En la consola de Bash de PythonAnywhere:

```bash
# Navegar al directorio
cd ~/SONDER-PROJECT/sonder

# Crear directorio uploads si no existe
mkdir -p uploads

# Dar permisos de lectura y escritura
chmod 755 uploads
chmod 644 uploads/*  # Si hay archivos

# Crear directorio de logs (opcional)
mkdir -p ../logs
chmod 755 ../logs
```

## Paso 6: Configurar Variables de Entorno (Seguridad)

En la sección **"Web"** de PythonAnywhere:

1. Busca **"WSGI configuration file"**
2. Si es necesario, edita el archivo de configuración para asegurar que PHP se ejecute correctamente

Para PythonAnywhere con PHP, normalmente no necesitas hace nada especial. El servidor ya maneja PHP automáticamente.

## Paso 7: Probar la Aplicación

1. Abre tu navegador en: `https://tu_usuario.pythonanywhere.com`
2. Deberías ver la página de inicio de Sonder
3. Intenta:
   - Registrar una cuenta
   - Iniciar sesión
   - Crear un perfil

## Paso 8: Monitoreo de Errores (Importante)

Si algo falla, revisa los logs:

### En PythonAnywhere Dashboard:
1. Ve a **"Web"**
2. En la sección **"Log files"**, revisa:
   - **Access log**: Peticiones HTTP
   - **Error log**: Errores del servidor
   - **Server log**: Logs del servidor web

### O desde la consola:
```bash
tail -f /var/log/apache2.error.log  # Ver errores en tiempo real
tail -f /var/log/apache2.access.log  # Ver acceso en tiempo real
```

## Paso 9: Configuración de Base de Datos

La base de datos SQLite se crea automáticamente en:
```
/home/tu_usuario/SONDER-PROJECT/sonder/sonder.db
```

Para acceder a ella desde la consola:
```bash
cd ~/SONDER-PROJECT/sonder
sqlite3 sonder.db

# Ver tablas
.tables

# Ver logs de auditoría
SELECT * FROM audit_logs LIMIT 10;

# Salir
.quit
```

## Paso 10: Configuración de Dominio Personalizado (Opcional)

Si quieres tu propio dominio (ej: `sonder.com`):

1. Compra un dominio en:
   - GoDaddy
   - Namecheap
   - Google Domains

2. En PythonAnywhere:
   - Ve a **"Web"**
   - Ve a **"Add a new web app"**
   - Selecciona "Manual" y tu dominio personalizado

3. Configura los DNS del dominio:
   - Apunta a: `tu_usuario.pythonanywhere.com`

## Restricciones del Plan Gratuito de PythonAnywhere

⚠️ **Importante**: Ten en cuenta estas limitaciones:

- **Ancho de banda limitado**: ~100MB/mes
- **CPU limitada**: Para pruebas, no para tráfico alto
- **Almacenamiento**: 512MB
- **Sin HTTPS en dominio personalizado** (en plan gratuito)
- **Tiempo de ejecución limitado**

**Cuando la app crezca, contempla**: Actualizar a plan premium o migrar a DigitalOcean VPS

## Solución de Problemas

### Error: "404 - Archivo no encontrado"
- Verifica que el **Source code** apunta a la carpeta `sonder/`
- No debe apuntar a `SONDER-PROJECT/`

### Error: "500 - Internal Server Error"
- Revisa los logs en el Dashboard
- Asegúrate de que `uploads/` tiene permisos `755`
- Verifica que `config.php` puede crear la BD

### Base de datos no se crea
```bash
# Dar permisos al directorio sonder/
chmod 755 ~/SONDER-PROJECT/sonder
```

### Problemas con avatares
```bash
# Asegurate de que el directorio existe
ls -la ~/SONDER-PROJECT/sonder/uploads/

# Si no existe, créalo
mkdir -p ~/SONDER-PROJECT/sonder/uploads
chmod 755 ~/SONDER-PROJECT/sonder/uploads
```

## Actualizar el Código (Después de cambios en GitHub)

Cuando hagas cambios en el código:

1. En la consola de PythonAnywhere:
```bash
cd ~/SONDER-PROJECT

# Descarga los cambios
git pull origin develop

# Recarga la web app desde el Dashboard
```

2. En el Dashboard:
   - Ve a **"Web"**
   - Busca el botón de **"Reload"**
   - Haz clic para aplicar los cambios

## Escalabilidad Futura

Cuando necesites más:

### Plan Premium de PythonAnywhere
- Mejor performance
- Más almacenamiento
- Sin limitaciones de ancho de banda

### Migración a DigitalOcean
```bash
# Crear un Droplet (VPS) con:
# - Ubuntu 20.04 o superior
# - 1GB RAM (mínimo)
# - PHP 7.4+ instalado
# - MySQL o PostgreSQL

# Luego:
git clone https://github.com/SKETCH19/SONDER-PROJECT.git
cd SONDER-PROJECT/sonder

# Configurar Nginx o Apache
# Instalar certificado SSL
# Configurar firewall
```

## Soporte

- Documentación PythonAnywhere: https://www.pythonanywhere.com/help/
- GitHub Sonder: https://github.com/SKETCH19/SONDER-PROJECT
- Email de soporte PythonAnywhere: support@pythonanywhere.com

---

**¡Listo!** Ya deberías tener Sonder funcionando en línea. Comparte el URL con tus amigos para que se registren y empiecen a usar la app. 🚀
