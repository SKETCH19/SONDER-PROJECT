# Sonder - Plataforma de Mensajería Significativa

Sonder es una aplicación web de mensajería que permite a los usuarios conectarse, hacer amigos y comunicarse de manera significativa.

## Características

- 🔐 **Registro y Autenticación Segura** - Contraseñas hasheadas con ARGON2ID
- 👥 **Sistema de Amigos** - Solicitudes de amistad, acepto, rechazo y bloqueo
- 💬 **Mensajería** - Envío de mensajes en tiempo real entre amigos
- 👤 **Perfil Personalizable** - Actualiza tu información, avatar y detalles de perfil
- 🛡️ **Seguridad** - Protección contra XSS, SQL Injection y CSRF
- 📊 **Auditoría** - Sistema de logs para registrar todas las acciones
- 🌍 **Soporte Multiidioma** - Interfaz en español

## Requisitos

- PHP 7.4 o superior
- SQLite3 (incluido en PHP por defecto)
- Navegador web moderno
- Servidor web (Apache, Nginx, etc.)

## Instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/SKETCH19/SONDER-PROJECT.git
cd SONDER-PROJECT/sonder
```

### 2. Configurar permisos de directorios

```bash
chmod 755 uploads/
chmod 644 sonder.db  # Si el archivo existe
```

### 3. Configurar el servidor web

#### Opción A: Usar PHP Built-in Server (desarrollo)

```bash
php -S localhost:8000
```

Luego accede a `http://localhost:8000` en tu navegador.

#### Opción B: Apache

1. Asegúrate de que `mod_rewrite` está habilitado
2. Apunta tu DocumentRoot al directorio `sonder/`
3. Reinicia Apache

#### Opción C: Nginx

Configura tu archivo nginx.conf:

```nginx
server {
    listen 80;
    server_name localhost;
    root /path/to/sonder;
    index index.php;

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 4. La base de datos se crea automáticamente

Al acceder a la aplicación por primera vez, se crearán automáticamente:
- Las tablas necesarias (users, messages, friends, audit_logs)
- Los índices para optimizar consultas

## Publicar demo en GitHub Pages (estático)

GitHub Pages no ejecuta PHP, así que esta opción publica una demo visual usando la carpeta `docs/`.

1. Haz commit y push de los cambios (incluyendo `docs/`) en tu rama `develop`.
2. En GitHub, ve a **Settings → Pages**.
3. En **Source**, selecciona **Deploy from a branch**.
4. Elige la rama `develop` y la carpeta `/docs`.
5. Guarda y espera el deploy.

Tu sitio quedará disponible en:
https://sketch19.github.io/SONDER-PROJECT/

## Uso

### 1. Crear una cuenta

- Accede a la página de inicio
- Haz clic en "Registrarse"
- Completa el formulario con:
  - Nombre completo
  - Email único
  - Nombre de usuario (3-20 caracteres, letras/números/guión bajo)
  - Contraseña (mínimo 8 caracteres)
  - Fecha de nacimiento (debe ser mayor de 18 años)
  - País
  - Número de teléfono

### 2. Iniciar sesión

- Haz clic en "Iniciar Sesión"
- Usa tu usuario/email y contraseña
- Serás redirigido a tu dashboard

### 3. Gestionar amigos

- Usa la barra de búsqueda para encontrar usuarios
- Haz clic en "Agregar" para enviar una solicitud de amistad
- Acepta o rechaza solicitudes pendientes
- Bloquea usuarios si es necesario

### 4. Enviar mensajes

- Haz clic en un amigo de tu lista
- Escribe tu mensaje en el campo de texto
- Presiona Enter o haz clic en Enviar
- Los mensajes se marcan automáticamente como leídos

### 5. Actualizar perfil

- Haz clic en tu avatar o nombre
- Actualiza tu información
- Sube un nuevo avatar
- Cambia tu contraseña

## Seguridad

### Medidas implementadas

1. **Hashing de Contraseñas** - Usa ARGON2ID para máxima seguridad
2. **SQL Injection Prevention** - PDO Prepared Statements en todas las consultas
3. **XSS Protection** - Todos los outputs se escapan con htmlspecialchars()
4. **CSRF Tokens** - Generación y validación de tokens CSRF
5. **Session Security** - Cookies HTTPOnly y Secure
6. **Input Validation** - Validación estricta de emails, usernames, teléfonos
7. **Age Verification** - Verificación de edad mínima (18 años)
8. **Audit Logs** - Registro de todas las acciones importantes

### Mejores prácticas recomendadas

Para producción, se recomienda:

1. **HTTPS obligatorio** - Usar SSL/TLS certificates
2. **Rate Limiting** - Implementar límites de tasa de solicitudes
3. **Backups regulares** - Hacer backup de la base de datos
4. **Monitoreo** - Monitorear logs de auditoría
5. **Actualizaciones** - Mantener PHP y dependencias actualizadas
6. **WAF** - Usar un Web Application Firewall
7. **CORS** - Configurar CORS apropiadamente si se usa desde otros dominios

## Estructura de archivos

```
sonder/
├── includes/
│   ├── config.php          # Configuración de BD y funciones de seguridad
│   ├── auth.php            # Funciones de autenticación
│   ├── functions.php       # Funciones de negocio
│   └── profile.php         # Funciones de perfil (si existe)
├── js/
│   ├── script.js           # JavaScript principal
│   └── profile.js          # JavaScript de perfil (si existe)
├── css/
│   └── style.css           # Estilos CSS
├── uploads/                # Directorio para avatares (debe existir y tener permisos 755)
├── index.php               # Página de inicio
├── login.php               # Página de inicio de sesión
├── register.php            # Página de registro
├── dashboard.php           # Panel principal
├── send_message.php        # API para enviar mensajes
├── get_messages.php        # API para obtener mensajes
├── get_friend_info.php     # API para obtener info de amigos
├── update_profile.php      # API para actualizar perfil
├── upload_avatar.php       # API para subir avatar
├── change_password.php     # API para cambiar contraseña
├── delete_account.php      # API para eliminar cuenta
├── logout.php              # Cierre de sesión
├── sonder.db               # Base de datos SQLite (se crea automáticamente)
└── README.md               # Este archivo
```

## API Endpoints

### Autenticación

- `POST /login.php` - Iniciar sesión
- `POST /register.php` - Registrar usuario
- `GET /logout.php` - Cerrar sesión

### Mensajes

- `POST /send_message.php` - Enviar mensaje
- `GET /get_messages.php?friend_id=ID` - Obtener mensajes

### Amigos

- `GET /get_friend_info.php?friend_id=ID` - Obtener info de amigo

### Perfil

- `POST /update_profile.php` - Actualizar información de perfil
- `POST /upload_avatar.php` - Subir nuevo avatar
- `POST /change_password.php` - Cambiar contraseña
- `POST /delete_account.php` - Eliminar cuenta

## Resolución de problemas

### Error: "No se pudo conectar a la base de datos"

- Verifica que el directorio tenga permisos de escritura (`chmod 755`)
- Asegúrate de que PHP tiene permisos para crear archivos

### Error: "El archivo es demasiado grande"

- Los avatares tienen límite de 5MB
- Comprime tu imagen antes de subirla

### Error: "Token CSRF inválido"

- Los tokens caducan con la sesión
- Intenta nuevamente la operación
- Limpia las cookies de tu navegador

### Error: "Usuario no autorizado"

- Tu sesión puede haber expirado
- Inicia sesión nuevamente

## Registros (Logs)

Los registros de auditoría se encuentran en la tabla `audit_logs` de la BD. Registran:

- Registros y inicios de sesión
- Cambios de perfil
- Envío de mensajes
- Gestión de amigos (solicitudes, aceptaciones, bloqueos)
- Cambios de contraseña
- Eliminación de cuentas
- Errores importantes

Para ver los logs, accede a la base de datos SQLite con:

```bash
sqlite3 sonder.db
SELECT * FROM audit_logs ORDER BY created_at DESC;
```

## Contribuir

Si encuentra bugs o tiene sugerencias de mejora, por favor:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## Roadmap

- [ ] Soporte para llamadas de voz
- [ ] Compartir archivos
- [ ] Grupos de chat
- [ ] Notificaciones en tiempo real (WebSocket)
- [ ] Búsqueda avanzada
- [ ] Temas personalizables
- [ ] Verificación de dos factores (2FA)
- [ ] Recuperación de contraseña por email

## Licencia

Este proyecto está licenciado bajo la licencia MIT. Ver el archivo `LICENSE` para más detalles.

## Autor

**SKETCH19**

- GitHub: [@SKETCH19](https://github.com/SKETCH19)

## Soporte

Para obtener soporte, abre un issue en el repositorio de GitHub.

## Cambios recientes

### v1.1 - Mejoras de Seguridad

- ✅ Validación mejorada de inputs
- ✅ Protección contra XSS
- ✅ Sistema de auditoría
- ✅ Tokens CSRF
- ✅ Mejoras de performance (índices BD)
- ✅ Validación de edad mínima
- ✅ Bloqueo de usuarios mejorado
- ✅ Escapado de mensajes

---

**Última actualización:** 9 de Febrero de 2026

¡Gracias por usar Sonder! 💫
