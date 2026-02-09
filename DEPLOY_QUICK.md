# DEPLOY RÁPIDO - PythonAnywhere (5 minutos)

## ⚡ Pasos Rápidos

### 1️⃣ Crear Cuenta (2 min)
- Ve a: https://www.pythonanywhere.com
- Regístrate con tu email
- Selecciona plan **"Beginner"** (GRATIS)

### 2️⃣ Clonar Repositorio (1 min)
Abre la consola Bash en PythonAnywhere y ejecuta:
```bash
git clone https://github.com/SKETCH19/SONDER-PROJECT.git
cd SONDER-PROJECT/sonder
chmod 755 uploads
```

### 3️⃣ Crear Web App (1 min)
- Dashboard → "Web" → "Add a new web app"
- Selecciona: **PHP** → **7.4**

### 4️⃣ Configurar Directorio Raíz (1 min)
En la sección "Source code" de tu Web app:
```
/home/tu_usuario/SONDER-PROJECT/sonder
```
Luego haz clic en **"Reload"**

### 5️⃣ ¡Listo! 🎉
Tu app está en: `https://tu_usuario.pythonanywhere.com`

---

## 📋 Verificación

Si ves errores, ejecuta esto en Bash:
```bash
# Crear logs directory
mkdir -p ~/SONDER-PROJECT/logs

# Dar permisos correctos
cd ~/SONDER-PROJECT/sonder
chmod 755 .
chmod 755 uploads
chmod 755 includes

# Verificar que la BD se puede crear
ls -la sonder.db  # Si no existe, se creará al visitar la app
```

---

## 🔗 URL Final
Tu aplicación estará en:
```
https://TU_USUARIO.pythonanywhere.com
```

Ejemplo: `https://sketch19.pythonanywhere.com`

---

## ✅ Próximos Pasos

1. **Visita tu app**: Abre en navegador
2. **Registra una cuenta**: Prueba la funcionalidad
3. **Comparte con amigos**: Dale tu URL

Si algo falla, revisa `DEPLOY_PYTHONANYWHERE.md` para solución de problemas completa.
