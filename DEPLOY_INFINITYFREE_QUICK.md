# DESPLIEGUE RÁPIDO EN INFINITYFREE (10 minutos)

## ⚡ Pasos Rápidos

### 1️⃣ Crear Cuenta (2 min)
- Ve a: https://www.infinityfreeapp.com
- Click en **"Get Started"**
- Completa email + contraseña
- **Confirma tu email**

### 2️⃣ Crear Sitio Web (2 min)
- Click en **"Create Website"**
- Nombre: `sonder` (o lo que quieras)
- Dominio: Elige un subdominio gratuito
- Click en **"Create"**

### 3️⃣ Obtener Credenciales FTP (1 min)
En tu sitio web, busca **"FTP Information"**:
- Anota: **FTP Host**, **Username**, **Password**, **Port 21**

### 4️⃣ Descargar FileZilla (1 min)
- Ve a: https://filezilla-project.org/
- Descarga **FileZilla Client**
- Instala

### 5️⃣ Conectar e Ir a Carpeta Raíz (1 min)
En FileZilla:
- **Host**: `ftpupload.net` (o tu FTP Host)
- **Username**: Tu usuario FTP
- **Password**: Tu contraseña FTP
- **Port**: `21`
- Click **"Quickconnect"**

Navega a carpeta: `public_html`

### 6️⃣ Subir Archivos de Sonder (3 min)
1. A la **izquierda**: Busca tu carpeta `SONDER-PROJECT/sonder`
2. **Selecciona**:
   - Todos los `.php`
   - Carpetas: `css/`, `js/`, `includes/`
   - Archivo: `.htaccess`

3. **Arrastra a la derecha** (al servidor)
4. Espera a que terminen de subir

### 7️⃣ Crear Carpeta Uploads (1 min)
En FileZilla, **lado derecho** (servidor):
- Click derecho → **Create folder**
- Nombre: `uploads`
- Enter

### 8️⃣ ¡Listo! (0 min) 🎉
Tu app está en: `https://tudominio.infinityfreeapp.com`

---

## 📋 Verificación

Abre en navegador: `https://tudominio.infinityfreeapp.com`

Deberías ver:
- Página de inicio de**Sonder**
- Botones: "Iniciar Sesión" y "Registrarse"

Si ves error **500**:
- En FileZilla, click derecho en **uploads** → **Change Permissions** → `755`
- Haz lo mismo con carpeta **includes**
- Recarga la página (Ctrl+F5)

---

## 🔗 Tu URL
```
https://tudominio.infinityfreeapp.com
```

Ejemplo: `https://sonder-app.infinityfreeapp.com`

---

## ✅ Próximos Pasos

1. **Abre tu URL**
2. **Registra una cuenta de prueba**
3. **Sube un avatar**
4. **Invita a tus amigos**

Para más detalles: Ver `DEPLOY_INFINITYFREE.md`
