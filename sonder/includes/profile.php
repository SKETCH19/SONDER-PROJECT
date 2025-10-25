<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<div class="content-area" id="profile-section" style="display: none;">
            <div class="section-header">
                <h2>👤 Mi Perfil</h2>
            </div>
            
            <div class="profile-container">
                <div class="profile-header">
                    <div class="avatar-upload">
                        <img src="uploads/<?php echo $userInfo['profile_pic']; ?>" alt="Avatar" class="profile-avatar" id="profile-avatar" onerror="this.src='https://placehold.co/100'">
                        <input type="file" id="avatar-input" accept="image/*" style="display: none;">
                        <button class="btn btn-secondary" onclick="document.getElementById('avatar-input').click()">Cambiar foto</button>
                    </div>
                    <div class="profile-info">
                        <h2 id="profile-display-name"><?php echo htmlspecialchars($userInfo['full_name']); ?></h2>
                        <p id="profile-username">@<?php echo htmlspecialchars($userInfo['username']); ?></p>
                        <p class="profile-join-date">Miembro desde <?php echo date('F Y', strtotime($userInfo['created_at'])); ?></p>
                    </div>
                </div>
                
                <div class="profile-form">
                    <h3>Información Personal</h3>
                    <form id="profile-form">
                        <div class="form-group">
                            <label class="form-label">Nombre completo</label>
                            <input type="text" name="full_name" class="form-input" value="<?php echo htmlspecialchars($userInfo['full_name']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Nombre de usuario</label>
                            <input type="text" name="username" class="form-input" value="<?php echo htmlspecialchars($userInfo['username']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Correo electrónico</label>
                            <input type="email" name="email" class="form-input" value="<?php echo htmlspecialchars($userInfo['email']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">País</label>
                            <select name="country" class="form-select">
                                <option value="ES" <?php echo $userInfo['country'] == 'ES' ? 'selected' : ''; ?>>España</option>
                                <option value="MX" <?php echo $userInfo['country'] == 'MX' ? 'selected' : ''; ?>>México</option>
                                <option value="AR" <?php echo $userInfo['country'] == 'AR' ? 'selected' : ''; ?>>Argentina</option>
                                <option value="CO" <?php echo $userInfo['country'] == 'CO' ? 'selected' : ''; ?>>Colombia</option>
                                <option value="US" <?php echo $userInfo['country'] == 'US' ? 'selected' : ''; ?>>Estados Unidos</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" name="phone" class="form-input" value="<?php echo htmlspecialchars($userInfo['phone']); ?>">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </form>
                </div>
                
                <div class="profile-security">
                    <h3>Seguridad</h3>
                    <button class="btn btn-secondary" id="change-password-btn">Cambiar contraseña</button>
                </div>
                
                <div class="profile-danger">
                    <h3>Zona de peligro</h3>
                    <button class="btn btn-danger" id="delete-account-btn">Eliminar cuenta</button>
                    <a href="logout.php" class="btn btn-secondary">Cerrar sesión</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal para cambiar contraseña -->
    <div class="modal" id="password-modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Cambiar Contraseña</h3>
                <button class="modal-close">&times;</button>
            </div>
            <form id="password-form">
                <div class="form-group">
                    <label class="form-label">Contraseña actual</label>
                    <input type="password" name="current_password" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Nueva contraseña</label>
                    <input type="password" name="new_password" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirmar nueva contraseña</label>
                    <input type="password" name="confirm_password" class="form-input" required>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn btn-primary">Cambiar contraseña</button>
                    <button type="button" class="btn btn-secondary modal-cancel">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal para eliminar cuenta -->
    <div class="modal" id="delete-modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Eliminar Cuenta</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que quieres eliminar tu cuenta? Esta acción no se puede deshacer.</p>
                <p>Todos tus mensajes, amigos y datos se perderán permanentemente.</p>
                <form id="delete-form">
                    <div class="form-group">
                        <label class="form-label">Ingresa tu contraseña para confirmar</label>
                        <input type="password" name="confirm_password" class="form-input" required>
                    </div>
                    <div class="modal-actions">
                        <button type="submit" class="btn btn-danger">Eliminar cuenta</button>
                        <button type="button" class="btn btn-secondary modal-cancel">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
