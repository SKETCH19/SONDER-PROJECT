



<div class="content-area" id="profile-section" style="display: none;">
    <div class="section-header">
        <h2>Mi Perfil</h2>
    </div>
    
    <div class="profile-wrapper">
        <div class="profile-container">
            <div class="profile-header">
                <div class="avatar-upload">
                    <img 
                        id="profile-avatar" 
                        src="uploads/<?php echo htmlspecialchars($userInfo['profile_pic'] ?: 'default.png'); ?>" 
                        onerror="this.src='https://placehold.co/150x150?text=Foto'"
                        alt="Avatar"
                        class="profile-avatar-img"
                    >
                    <form id="avatar-form" enctype="multipart/form-data" method="POST" action="upload_avatar.php" style="display: none;">
                        <input type="file" name="avatar" id="avatar-input" accept="image/*">
                    </form>
                    <button class="btn btn-secondary" id="change-avatar-btn" style="margin-top: 10px; width: 100%;">Cambiar foto</button>
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
                    <div class="profile-form-grid">
                        <div class="form-group">
                            <label class="form-label">Nombre completo</label>
                            <input type="text" name="full_name" class="form-input" value="<?php echo htmlspecialchars($userInfo['full_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Nombre de usuario</label>
                            <input type="text" name="username" class="form-input" value="<?php echo htmlspecialchars($userInfo['username']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Correo electrónico</label>
                            <input type="email" name="email" class="form-input" value="<?php echo htmlspecialchars($userInfo['email']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">País</label>
                            <select name="country" id="country-select-profile" class="form-select" required>
                                <option value="">Seleccionar país...</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" name="phone" class="form-input" value="<?php echo htmlspecialchars($userInfo['phone']); ?>">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-large">Guardar cambios</button>
                </form>
            </div>
            
            <div class="profile-security">
                <h3>Seguridad</h3>
                <button class="btn btn-secondary btn-large" id="change-password-btn">Cambiar contraseña</button>
            </div>
            
            <div class="profile-danger">
                <h3>Zona de peligro</h3>
                <div class="danger-actions">
                    <button class="btn btn-danger btn-large" id="delete-account-btn">Eliminar cuenta</button>
                    <a href="logout.php" class="btn btn-secondary btn-large">Cerrar sesión</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para cambiar contraseña -->
    <div class="modal" id="password-modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Cambiar Contraseña</h3>
                <button class="modal-close" type="button">&times;</button>
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
                <button class="modal-close" type="button">&times;</button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que quieres eliminar tu cuenta? <strong>Esta acción no se puede deshacer.</strong></p>
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
</div>

<script>
    // Lista completa de 195 países (nombres, igual que en register.php)
    const countriesList = [
        'Afganistán', 'Albania', 'Alemania', 'Andorra', 'Angola', 'Anguila', 'Antártida', 'Antigua y Barbuda',
        'Arabia Saudí', 'Argelia', 'Argentina', 'Armenia', 'Aruba', 'Australia', 'Austria', 'Azerbaiyán',
        'Bahamas', 'Bahrein', 'Bangladesh', 'Barbados', 'Bélgica', 'Belice', 'Benin', 'Bermudas', 'Bielorrusia',
        'Birmania', 'Bolivia', 'Bosnia y Herzegovina', 'Botsuana', 'Brasil', 'Brunei', 'Bulgaria', 'Burkina Faso',
        'Burundi', 'Bután', 'Cabo Verde', 'Camboya', 'Camerún', 'Canadá', 'Catar', 'Chad', 'Chile', 'China',
        'Chipre', 'Ciudad del Vaticano', 'Colombia', 'Comoras', 'Congo', 'Corea del Norte', 'Corea del Sur',
        'Costa de Marfil', 'Costa Rica', 'Croacia', 'Cuba', 'Curazao', 'Dinamarca', 'Dominica', 'Djibutí',
        'Ecuador', 'Egipto', 'El Salvador', 'Emiratos Árabes Unidos', 'España', 'Estados Unidos', 'Estonia',
        'Etiopía', 'Filipinas', 'Finlandia', 'Fiyi', 'Francia', 'Gabón', 'Gambia', 'Gana', 'Georgia',
        'Gibraltar', 'Grecia', 'Groenlandia', 'Guadalupe', 'Guam', 'Guatemala', 'Guayana Francesa', 'Guyana',
        'Haití', 'Holanda', 'Honduras', 'Hong Kong', 'Hungría', 'India', 'Indonesia', 'Irak', 'Irán',
        'Irlanda', 'Irlanda del Norte', 'Isla de Man', 'Islandia', 'Islas Åland', 'Islas Ascensión',
        'Islas Caimán', 'Islas Canarias', 'Islas Cocos', 'Islas Cook', 'Islas Feroe', 'Islas Hébridas',
        'Islas Malvinas', 'Islas Marianas', 'Islas Norfolk', 'Islas Palau', 'Islas Pitcairn', 'Islas Salomón',
        'Islas Seychelles', 'Islas Turks y Caicos', 'Islas Vírgenes Británicas', 'Islas Vírgenes de EE. UU.',
        'Italia', 'Jamaica', 'Japón', 'Jersey', 'Jordania', 'Kazajistán', 'Kenia', 'Kirguistán', 'Kiribati',
        'Kuwait', 'Laos', 'Lesoto', 'Letonia', 'Líbano', 'Liberia', 'Libia', 'Liechtenstein', 'Lituania',
        'Luxemburgo', 'Macao', 'Macedonia del Norte', 'Madagascar', 'Malasia', 'Malawi', 'Maldivas', 'Mali',
        'Malta', 'Marruecos', 'Martinica', 'Mauricio', 'Mauritania', 'Mayotte', 'México', 'Micronesia',
        'Moldavia', 'Mónaco', 'Mongolia', 'Montenegro', 'Montserrat', 'Mozambique', 'Namibia', 'Nauru',
        'Nepal', 'Nicaragua', 'Níger', 'Nigeria', 'Niue', 'Noruega', 'Nueva Caledonia', 'Nueva Zelanda',
        'Omán', 'Países Bajos', 'Panamá', 'Papúa Nueva Guinea', 'Paquistán', 'Paraguay', 'Perú',
        'Polinesia Francesa', 'Polonia', 'Puerto Rico', 'Qatar', 'República Centroafricana', 'República Checa',
        'República Democrática del Congo', 'República Dominicana', 'Reunión', 'Ruanda', 'Rumania', 'Rusia',
        'Saba', 'Sahara Occidental', 'Samoa', 'Samoa Americana', 'San Bartolomé', 'San Cristóbal y Nieves',
        'San Eustaquio', 'San Marino', 'San Martín', 'San Pedro y Miquelón', 'San Vicente y las Granadinas',
        'Santa Elena', 'Santa Lucía', 'Santo Tomé y Príncipe', 'Senegal', 'Serbia', 'Seychelles',
        'Sierra Leona', 'Singapur', 'Sint Maarten', 'Siria', 'Somalia', 'Sri Lanka', 'Suazilandia',
        'Sudáfrica', 'Sudán', 'Sudán del Sur', 'Suecia', 'Suiza', 'Surinam', 'Svalbard y Jan Mayen',
        'Tailandia', 'Taiwán', 'Tanzania', 'Tayikistán', 'Territorio Británico del Océano Índico',
        'Terranova y Labrador', 'Territorios Franceses del Sur', 'Timor Oriental', 'Togo', 'Tokelau',
        'Tonga', 'Trinidad y Tobago', 'Túnez', 'Turkmenistán', 'Turquía', 'Turks y Caicos', 'Tuvalu',
        'Ucrania', 'Uganda', 'Uruguay', 'Uzbekistán', 'Vanuatu', 'Venezuela', 'Vietnam', 'Wallis y Futuna',
        'Yemen', 'Zambia', 'Zimbabue'
    ];

    // Función para llenar los países
    function populateCountriesProfile() {
        const countrySelect = document.getElementById('country-select-profile');
        if (!countrySelect) return;
        
        // Obtener el país actualmente seleccionado
        const currentCountry = '<?php echo htmlspecialchars($userInfo['country']); ?>';
        
        // Ordenar países alfabéticamente
        const sortedCountries = countriesList.sort();
        
        sortedCountries.forEach(country => {
            const option = document.createElement('option');
            option.value = country;
            option.textContent = country;
            option.selected = country === currentCountry;
            countrySelect.appendChild(option);
        });
    }

    // Ejecutar cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', populateCountriesProfile);
</script>
