
// Navegación entre secciones
function showSection(sectionName) {
    // Ocultar todas las secciones
    document.querySelectorAll('.chat-area, .content-area').forEach(section => {
        section.style.display = 'none';
    });
    
    // Mostrar la sección seleccionada
    const section = document.getElementById(sectionName + '-section');
    if (section) {
        section.style.display = 'flex';
    }
    
    // Actualizar menú activo
    document.querySelectorAll('.menu-item').forEach(item => {
        item.classList.remove('active');
        if (item.getAttribute('data-section') === sectionName) {
            item.classList.add('active');
        }
    });
    
    // Cerrar menú en móviles
    document.querySelector('.sidebar').classList.remove('open');
}

// Tabs en sección de amigos
function setupFriendTabs() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tabName = this.getAttribute('data-tab');
            
            // Remover active de todos los tabs
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));
            
            // Activar tab clickeado
            this.classList.add('active');
            document.getElementById(tabName).classList.add('active');
        });
    });
}

// Búsqueda
function setupImprovedSearch() {
    const searchBtn = document.getElementById('search-btn-improved');
    const searchInput = document.getElementById('user-search-improved');
    const resultsContainer = document.getElementById('search-results-improved');
    const suggestionTags = document.querySelectorAll('.suggestion-tag');

    function performImprovedSearch(query = '') {
        const searchQuery = query || searchInput.value.trim();
        
        if (!searchQuery) {
            resultsContainer.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon">◈</div>
                    <h3>Explora la Red Sonder</h3>
                    <p>Busca usuarios para encontrar conexiones increíbles</p>
                    <div class="search-stats">
                        <p>Miles de usuarios esperando conocerte</p>
                    </div>
                </div>
            `;
            return;
        }

        // Mostrar loading
        resultsContainer.innerHTML = `
            <div class="search-loading">
                <div class="search-loading-spinner"></div>
                <p>Explorando el universo...</p>
            </div>
        `;

        // Llamada real al endpoint de búsqueda
        fetch('search_users.php?q=' + encodeURIComponent(searchQuery), { credentials: 'same-origin' })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    resultsContainer.innerHTML = '<div class="empty-state"><p>Error en la búsqueda</p></div>';
                    return;
                }

                const users = data.results || [];

                if (users.length === 0) {
                    resultsContainer.innerHTML = `
                        <div class="empty-state">
                            <div class="empty-icon">◈</div>
                            <h3>Sin resultados</h3>
                            <p>No se encontraron usuarios para "${searchQuery}"</p>
                        </div>
                    `;
                    return;
                }

                let resultsHTML = '';
                users.forEach(user => {
                    resultsHTML += `
                        <div class="search-result-card" data-user-id="${user.id}">
                            <img src="${user.profile_pic_url || ('uploads/' + (user.profile_pic || 'default.png'))}" alt="${user.full_name}" class="search-result-avatar-improved" onerror="this.src='https://placehold.co/80'">
                            <div class="search-result-info-improved">
                                <div class="search-result-name-improved">${user.full_name}</div>
                                <div class="search-result-username-improved">@${user.username}</div>
                            </div>
                            <div class="search-result-actions-improved">
                                <button class="search-action-btn add-friend-btn"> <span>+</span> Agregar </button>
                                <button class="search-action-btn view-profile-btn"> Ver </button>
                            </div>
                        </div>
                    `;
                });

                resultsHTML += `<div class="search-stats"><p>Mostrando ${users.length} resultados para "${searchQuery}"</p></div>`;
                resultsContainer.innerHTML = resultsHTML;

                // Agregar event listeners
                document.querySelectorAll('.add-friend-btn').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        const userId = this.closest('.search-result-card').getAttribute('data-user-id');
                        // Llamada AJAX real
                        const form = new FormData();
                        form.append('friend_id', userId);
                        fetch('send_friend_request.php', { method: 'POST', body: form, credentials: 'same-origin' })
                            .then(r => r.json())
                            .then(res => {
                                showNotification(res.message || (res.success ? 'Solicitud enviada' : 'Error'), res.success ? 'success' : 'info');
                            })
                            .catch(() => showNotification('Error al enviar solicitud', 'info'));
                    });
                });

                document.querySelectorAll('.view-profile-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const userId = this.closest('.search-result-card').getAttribute('data-user-id');
                        viewProfile(userId);
                    });
                });
            })
            .catch(err => {
                resultsContainer.innerHTML = '<div class="empty-state"><p>Error en la búsqueda</p></div>';
                console.error(err);
            });
    }

    // Event listeners
    searchBtn.addEventListener('click', () => performImprovedSearch());
    
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            performImprovedSearch();
        }
    });

    // Sugerencias de búsqueda
    suggestionTags.forEach(tag => {
        tag.addEventListener('click', function() {
            const searchTerm = this.getAttribute('data-search');
            searchInput.value = searchTerm;
            performImprovedSearch(searchTerm);
        });
    });

    // Efecto de placeholder dinámico
    const placeholders = [
        "Buscar por usuario, nombre o intereses...",
        "Ejemplo: desarrolladores, diseñadores...",
        "Encuentra personas con tus mismos intereses",
        "Busca por habilidades o pasatiempos..."
    ];
    let placeholderIndex = 0;

    setInterval(() => {
        searchInput.placeholder = placeholders[placeholderIndex];
        placeholderIndex = (placeholderIndex + 1) % placeholders.length;
    }, 3000);
}

// Función de inicio de sesión con Google
function onSignIn(googleUser) {
  var profile = googleUser.getBasicProfile();
  console.log('ID: ' + profile.getId()); // Do not send to your backend! Use an ID token instead.
  console.log('Name: ' + profile.getName());
  console.log('Image URL: ' + profile.getImageUrl());
  console.log('Email: ' + profile.getEmail()); // This is null if the 'email' scope is not present.
}

// Funciones auxiliares para acciones de búsqueda
function sendFriendRequest(userId) {
    // Simular envío de solicitud
    const btn = event.target.closest('.add-friend-btn');
    const originalHTML = btn.innerHTML;
    
    btn.innerHTML = '<span>Enviando...</span>';
    btn.disabled = true;
    
    setTimeout(() => {
        btn.innerHTML = '<span>✓ Enviado</span>';
        btn.style.background = 'linear-gradient(45deg, #51cf66, #40c057)';
        
        // Mostrar notificación
        showNotification('Solicitud de amistad enviada correctamente', 'success');
    }, 1000);
}

function viewProfile(userId) {
    // Redirigir a la vista pública del perfil
    window.location.href = 'view_profile.php?user_id=' + encodeURIComponent(userId);
}

function showNotification(message, type = 'info') {
    // Crear elemento de notificación
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-icon">${type === 'success' ? '✓' : '·'}</span>
            <span class="notification-message">${message}</span>
        </div>
    `;
    
    // Estilos para la notificación
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: var(--space-blue);
        border: 1px solid ${type === 'success' ? 'rgba(81, 207, 102, 0.3)' : 'rgba(0, 212, 255, 0.3)'};
        border-left: 4px solid ${type === 'success' ? '#51cf66' : 'var(--electric-blue)'};
        color: var(--stardust);
        padding: 1rem 1.5rem;
        border-radius: 10px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
        z-index: 10000;
        animation: slideInRight 0.3s ease;
        backdrop-filter: blur(10px);
    `;
    
    document.body.appendChild(notification);
    
    // Remover después de 3 segundos
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Modales
function setupModals() {
    // Modal de cambiar contraseña
    const passwordModal = document.getElementById('password-modal');
    const changePasswordBtn = document.getElementById('change-password-btn');
    const passwordForm = document.getElementById('password-form');
    
    if (changePasswordBtn) {
        changePasswordBtn.addEventListener('click', function() {
            passwordModal.style.display = 'flex';
        });
    }
    
    // Modal de eliminar cuenta
    const deleteModal = document.getElementById('delete-modal');
    const deleteAccountBtn = document.getElementById('delete-account-btn');
    const deleteForm = document.getElementById('delete-form');
    
    if (deleteAccountBtn) {
        deleteAccountBtn.addEventListener('click', function() {
            deleteModal.style.display = 'flex';
        });
    }
    
    // Cerrar modales
    document.querySelectorAll('.modal-close, .modal-cancel').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.modal').forEach(modal => {
                modal.style.display = 'none';
            });
        });
    });
    
    // Cambiar contraseña - AJAX real
    if (passwordForm) {
        passwordForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const currentPassword = this.querySelector('input[name="current_password"]').value;
            const newPassword = this.querySelector('input[name="new_password"]').value;
            const confirmPassword = this.querySelector('input[name="confirm_password"]').value;
            
            if (newPassword.length < 8) {
                alert('La contraseña debe tener al menos 8 caracteres');
                return;
            }
            
            const formData = new FormData();
            formData.append('current_password', currentPassword);
            formData.append('new_password', newPassword);
            formData.append('confirm_password', confirmPassword);
            
            try {
                const response = await fetch('change_password.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Contraseña actualizada correctamente');
                    passwordForm.reset();
                    passwordModal.style.display = 'none';
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });
    }
    
    // Eliminar cuenta - AJAX real
    if (deleteForm) {
        deleteForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (!confirm('¿ESTÁS ABSOLUTAMENTE SEGURO? Esta acción NO se puede deshacer y perderás todos tus datos.')) {
                return;
            }
            
            const password = this.querySelector('input[name="confirm_password"]').value;
            
            const formData = new FormData();
            formData.append('confirm_password', password);
            
            try {
                const response = await fetch('delete_account.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Cuenta eliminada. Serás redirigido a la página de inicio.');
                    window.location.href = data.redirect;
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });
    }
    
    // Cambiar avatar
    const changeAvatarBtn = document.getElementById('change-avatar-btn');
    const avatarInput = document.getElementById('avatar-input');
    const profileAvatar = document.getElementById('profile-avatar');
    
    if (changeAvatarBtn) {
        changeAvatarBtn.addEventListener('click', function() {
            avatarInput.click();
        });
    }
    
    if (avatarInput) {
        avatarInput.addEventListener('change', async function() {
            if (this.files.length === 0) return;
            
            const file = this.files[0];
            
            // Validar tamaño
            if (file.size > 5 * 1024 * 1024) {
                alert('El archivo es demasiado grande (máximo 5MB)');
                return;
            }
            
            // Validar tipo
            if (!['image/jpeg', 'image/png', 'image/gif', 'image/webp'].includes(file.type)) {
                alert('Solo se aceptan imágenes (JPEG, PNG, GIF, WebP)');
                return;
            }
            
            const formData = new FormData();
            formData.append('avatar', file);
            
            try {
                const response = await fetch('upload_avatar.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Actualizar la imagen en la UI
                    const newImageUrl = 'uploads/' + data.filename + '?t=' + new Date().getTime();
                    profileAvatar.src = newImageUrl;
                    alert('Avatar actualizado correctamente');
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });
    }
    
    // Guardar cambios del perfil
    const profileForm = document.getElementById('profile-form');
    if (profileForm) {
        profileForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('update_profile.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Perfil actualizado correctamente');
                    // Opcional: recargar la página para ver los cambios
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });
    }
}

// Acciones de amigos
function setupFriendActions() {
    // Chatear con amigo
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('chat-with-friend') || 
            e.target.classList.contains('chat-featured')) {
            const friendItem = e.target.closest('.friend-item, .featured-item');
            const userId = friendItem.getAttribute('data-user-id');
            openChatWithFriend(userId);
        }
        
        // Bloquear amigo
        if (e.target.classList.contains('block-friend')) {
            const friendItem = e.target.closest('.friend-item');
            const userId = friendItem.getAttribute('data-user-id');
            if (confirm('¿Bloquear a este usuario?')) {
                alert(`Usuario ${userId} bloqueado - En desarrollo`);
            }
        }
        
        // Eliminar amigo
        if (e.target.classList.contains('remove-friend') || 
            e.target.classList.contains('remove-featured')) {
            const item = e.target.closest('.friend-item, .featured-item');
            const userId = item.getAttribute('data-user-id');
            if (confirm('¿Eliminar de amigos/destacados?')) {
                alert(`Usuario ${userId} eliminado - En desarrollo`);
            }
        }
        
        // Aceptar/rechazar solicitudes
        if (e.target.classList.contains('accept-request')) {
            const requestItem = e.target.closest('.request-item');
            const userId = requestItem.getAttribute('data-user-id');
            const form = new FormData();
            form.append('friend_id', userId);
            fetch('accept_friend_request.php', { method: 'POST', body: form, credentials: 'same-origin' })
                .then(r => r.json())
                .then(res => {
                    showNotification(res.message || (res.success ? 'Solicitud aceptada' : 'Error'), res.success ? 'success' : 'info');
                    if (res.success) {
                        requestItem.remove();
                    }
                })
                .catch(() => showNotification('Error al aceptar solicitud', 'info'));
        }
        
        if (e.target.classList.contains('decline-request')) {
            const requestItem = e.target.closest('.request-item');
            const userId = requestItem.getAttribute('data-user-id');
            const form = new FormData();
            form.append('friend_id', userId);
            fetch('reject_friend_request.php', { method: 'POST', body: form, credentials: 'same-origin' })
                .then(r => r.json())
                .then(res => {
                    showNotification(res.message || (res.success ? 'Solicitud rechazada' : 'Error'), res.success ? 'success' : 'info');
                    if (res.success) {
                        requestItem.remove();
                    }
                })
                .catch(() => showNotification('Error al rechazar solicitud', 'info'));
        }
        
        // Desbloquear usuario
        if (e.target.classList.contains('unblock-user')) {
            const blockedItem = e.target.closest('.blocked-item');
            const userId = blockedItem.getAttribute('data-user-id');
            if (confirm('¿Desbloquear a este usuario?')) {
                alert(`Usuario ${userId} desbloqueado - En desarrollo`);
            }
        }
    });
}

// Inicialización cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {
    // Menú toggle
    const menuToggles = document.querySelectorAll('.menu-toggle, .mobile-menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (menuToggles.length > 0 && sidebar) {
        menuToggles.forEach(toggle => {
            toggle.addEventListener('click', function() {
                sidebar.classList.toggle('open');
            });
        });
    }
    
    // Navegación del menú principal
    const menuItems = document.querySelectorAll('.menu-item');
    menuItems.forEach(item => {
        item.addEventListener('click', function() {
            const section = this.getAttribute('data-section');
            showSection(section);
        });
    });
    
    // Configurar funcionalidades
    setupFriendTabs();
    setupImprovedSearch(); // Cambiado de setupSearch() a setupImprovedSearch()
    setupModals();
    setupFriendActions();
    
    // Mostrar sección de mensajes por defecto
    showSection('messages');
    
    // Envío de mensajes (simulación)
    const sendButton = document.querySelector('.send-button');
    const messageInput = document.querySelector('.message-input');
    
    if (sendButton && messageInput) {
        sendButton.addEventListener('click', sendMessage);
        messageInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    }
    
    // Agregar animaciones CSS para notificaciones
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
    `;
    document.head.appendChild(style);
});

// Variable para almacenar el amigo actual
let currentChatFriend = null;

// Función para enviar mensajes (ahora real)
function sendMessage() {
    const messageInput = document.querySelector('.message-input');
    const message = messageInput.value.trim();
    
    if (!message || !currentChatFriend) {
        return;
    }
    
    const formData = new FormData();
    formData.append('receiver_id', currentChatFriend.id);
    formData.append('message', message);
    
    fetch('send_message.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageInput.value = '';
            loadChatMessages(currentChatFriend.id);
        }
    })
    .catch(error => console.error('Error:', error));
}

// Función para agregar mensajes al chat
function addMessageToChat(message, isSent) {
    const messagesContainer = document.querySelector('.messages-container');
    if (messagesContainer) {
        // Ocultar mensaje de bienvenida si existe
        const welcomeMessage = messagesContainer.querySelector('.welcome-message');
        if (welcomeMessage) {
            welcomeMessage.style.display = 'none';
        }
        
        // Mostrar input de mensajes si está oculto
        const messageInputContainer = document.querySelector('.message-input-container');
        if (messageInputContainer) {
            messageInputContainer.style.display = 'flex';
        }
        
        const messageElement = document.createElement('div');
        messageElement.className = `message ${isSent ? 'sent' : 'received'}`;
        
        const now = new Date();
        const timeString = `${now.getHours()}:${now.getMinutes().toString().padStart(2, '0')}`;
        
        messageElement.innerHTML = `
            <div class="message-bubble">
                <div class="message-text">${message}</div>
                <div class="message-time">${timeString}</div>
            </div>
        `;
        
        messagesContainer.appendChild(messageElement);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
}

// Cargar mensajes de una conversación
async function loadChatMessages(friendId) {
    try {
        const response = await fetch(`get_messages.php?friend_id=${friendId}`);
        const data = await response.json();
        
        if (!data.success) {
            console.error('Error al cargar mensajes:', data.error);
            return;
        }
        
        const messagesContainer = document.querySelector('.messages-container');
        messagesContainer.innerHTML = '';
        
        if (data.messages.length === 0) {
            messagesContainer.innerHTML = `
                <div class="welcome-message">
                    <h3>Inicia una conversación</h3>
                    <p>Este es el comienzo de tu conversación con ${currentChatFriend.full_name}</p>
                </div>
            `;
        } else {
            data.messages.forEach(msg => {
                addMessageToChatUI(msg.message, msg.sender_id, msg.created_at);
            });
        }
        
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    } catch (error) {
        console.error('Error:', error);
    }
}

// Agregar mensaje a la UI
function addMessageToChatUI(message, senderId, createdAt) {
    const messagesContainer = document.querySelector('.messages-container');
    
    const welcomeMessage = messagesContainer.querySelector('.welcome-message');
    if (welcomeMessage) {
        welcomeMessage.remove();
    }
    
    const messageElement = document.createElement('div');
    const currentUserId = parseInt(document.body.getAttribute('data-current-user-id'));
    const isSent = senderId === currentUserId;
    
    messageElement.className = `message ${isSent ? 'sent' : 'received'}`;
    
    const date = new Date(createdAt);
    const timeString = `${date.getHours()}:${date.getMinutes().toString().padStart(2, '0')}`;
    
    messageElement.innerHTML = `
        <div class="message-bubble">
            <div class="message-text">${message}</div>
            <div class="message-time">${timeString}</div>
        </div>
    `;
    
    messagesContainer.appendChild(messageElement);
}

// Abrir chat con un amigo
async function openChatWithFriend(friendId) {
    try {
        const friendResponse = await fetch(`get_friend_info.php?friend_id=${friendId}`);
        const friendData = await friendResponse.json();
        
        if (!friendData.success) {
            alert('Error al cargar la información del usuario');
            return;
        }
        
        currentChatFriend = friendData.friend;
        
        const chatHeader = document.querySelector('.chat-header');
        chatHeader.innerHTML = `
            <div class="chat-user">
                <img src="uploads/${currentChatFriend.profile_pic}" 
                     alt="${currentChatFriend.username}" 
                     class="chat-user-avatar"
                     onerror="this.src='https://placehold.co/40'">
                <div class="chat-user-info">
                    <h3>${currentChatFriend.full_name}</h3>
                    <p>@${currentChatFriend.username}</p>
                </div>
            </div>
            <div class="chat-actions"></div>
        `;
        
        loadChatMessages(friendId);
        showSection('messages');
        document.querySelector('.message-input-container').style.display = 'flex';
    } catch (error) {
        console.error('Error:', error);
        alert('Error al abrir el chat');
    }
}

// Configurar funcionalidad de chat
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        const friendItems = document.querySelectorAll('.friend-item');
        friendItems.forEach(item => {
            item.style.cursor = 'pointer';
            item.addEventListener('click', function() {
                const friendId = this.getAttribute('data-user-id');
                openChatWithFriend(friendId);
            });
        });
    }, 100);
});