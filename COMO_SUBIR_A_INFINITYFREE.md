# 🚀 SUBIR SONDER A INFINITYFREE EN 3 PASOS

## ✅ EL ZIP YA ESTÁ LISTO

He creado un archivo `sonder-deploy.zip` que contiene TODO lo que necesitas. Solo descárgalo y súbelo a InfinityFree.

---

## 📥 PASO 1: DESCARGAR EL ZIP

El archivo `sonder-deploy.zip` está en la raíz del repositorio GitHub:

**Opción A: Desde GitHub (Recomendado)**
1. Ve a: https://github.com/SKETCH19/SONDER-PROJECT
2. Busca el archivo: `sonder-deploy.zip`
3. Click en el botón **"Download"** (o click derecho → "Save as")

**Opción B: Desde aquí**
- Descárgalo directamente (si tienes acceso)

**Tamaño:** ~43 KB (muy pequeño)

---

## 📤 PASO 2: SUBIR A INFINITYFREE (Opción A: File Manager Web)

1. **Crea cuenta en InfinityFree**:
   - Ve a: https://www.infinityfreeapp.com
   - Regístrate (GRATIS, sin tarjeta de crédito)
   - Crea un sitio web (ej: `sonder-app`)

2. **Abre el File Manager**:
   - En tu Dashboard, click en **"File Manager"**
   - Navega a carpeta: `public_html`

3. **Sube el ZIP**:
   - Click en botón **"Upload"**
   - Selecciona `sonder-deploy.zip`
   - Espera a que termine

4. **Descomprime el ZIP**:
   - Haz clic derecho en `sonder-deploy.zip`
   - Click en **"Extract"** o **"Unzip"**
   - Select all files
   - Click **"Extract"**

5. **Sube los archivos a la raíz**:
   - Abre la carpeta `sonder_deploy`
   - **Selecciona TODO** (Ctrl+A)
   - **Arrastra a carpeta principal** (`public_html`)
   - Confirma que quieres sobrescribir/mover

6. **Elimina la carpeta innecesaria**:
   - Borra la carpeta `sonder_deploy` (ya no la necesitas)
   - Borra el archivo `sonder-deploy.zip`

---

## 📤 PASO 3: SUBIR A INFINITYFREE (Opción B: FTP - Más Rápido)

### Si prefieres FTP (más profesional y rápido):

1. **Obtén credenciales FTP**:
   - En InfinityFree, ve a tu sitio
   - Busca **"FTP Information"**
   - Copia: Host, Username, Password, Port 21

2. **Descarga FileZilla** (si no lo tienes):
   - https://filezilla-project.org/
   - Descarga **FileZilla Client**

3. **Conecta FTP en FileZilla**:
   - Host: tu FTP Host
   - Username: tu usuario FTP
   - Password: tu contraseña FTP
   - Port: 21
   - Click **"Quickconnect"**

4. **Navega a `public_html`** (lado derecho)

5. **Sube el ZIP**:
   - Lado izquierdo: Encuentra `sonder-deploy.zip` en tu computadora
   - Arrastra a la derecha (a servidor)

6. **Descomprime en el servidor**:
   - Lado derecho: Click derecho en `sonder-deploy.zip`
   - **"Extract"** (si FileZilla lo permite) O
   - Usa el "File Manager" web de InfinityFree para descomprimir

7. **Sube los archivos de sonder_deploy a la raíz public_html**:
   - Abre carpeta `sonder_deploy`
   - Selecciona TODO
   - Arrastra a `public_html`

---

## ✅ VERIFICACIÓN

1. **Abre tu navegador**:
   ```
   https://tudominio.infinityfreeapp.com
   ```

2. **Deberías ver**:
   - Página de inicio de Sonder
   - Logo de Sonder
   - Botones: "Iniciar Sesión" y "Registrarse"

3. **Si ves error 500**:
   - En InfinityFree File Manager, click derecho en carpeta `uploads`
   - Click **"Change Permissions"**
   - Establece a: `755`
   - Haz lo mismo con carpeta `includes`
   - Recarga la página

---

## 🎉 ¡LISTO!

Tu app Sonder está en línea:
```
https://tudominio.infinityfreeapp.com
```

Comparte este enlace con tus amigos para que se registren.

---

## 📝 NOTA IMPORTANTE

- **La base de datos se crea automáticamente** cuando accedes por primera vez
- **Los uploads se guardan** en la carpeta `uploads/` que ya está creada
- **Los logs se guardan** en la carpeta `logs/` 

No necesitas configurar MySQL ni nada, ¡funciona automáticamente con SQLite!

---

## ❓ PROBLEMAS?

### "404 Not Found"
- Asegurate de que los archivos .php están directamente en `public_html`
- No deben estar en una subcarpeta

### "500 Internal Server Error"
- Establece permisos de `uploads/` a `755`
- Establece permisos de `includes/` a `755`

### "Cannot create database"
- Asegúrate de que `uploads/` existe y tiene permisos `755`

---

## 📚 GUÍAS COMPLETAS

Para más detalles:
- [DEPLOY_INFINITYFREE.md](../DEPLOY_INFINITYFREE.md) - Guía completa
- [README.md](../README.md) - Documentación general
