
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
                    <div class="empty-icon">🌌</div>
                    <h3>Explora el Universo Sonder</h3>
                    <p>Busca usuarios para encontrar conexiones increíbles</p>
                    <div class="search-stats">
                        <p>+5,000 usuarios esperando conocerte</p>
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

        // Simular búsqueda con delay
        setTimeout(() => {
            // Resultados de ejemploS
            const sampleUsers = [
                {
                    id: 1,
                    name: "Alexandra Vega",
                    username: "alexvega",
                    bio: "Desarrolladora full-stack 💻 | Amante del espacio 🌌 | Fotografía astronómica",
                    avatar: "https://images.unsplash.com/photo-1494790108755-2616b612b786?w=150&h=150&fit=crop&crop=face"
                },
                {
                    id: 2,
                    name: "Marco Rodríguez",
                    username: "marcord",
                    bio: "Diseñador UI/UX 🎨 | Música electrónica 🎵 | Viajero intergaláctico 🌠",
                    avatar: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&h=150&fit=crop&crop=face"
                },
                {
                    id: 3,
                    name: "Sofia Chen",
                    username: "sofchen",
                    bio: "Científica de datos 📊 | Escritora ✍️ | Exploradora de realidades alternas",
                    avatar: "https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150&h=150&fit=crop&crop=face"
                }
            ];

            let resultsHTML = '';
            
            sampleUsers.forEach(user => {
                resultsHTML += `
                    <div class="search-result-card" data-user-id="${user.id}">
                        <img src="${user.avatar}" alt="${user.name}" class="search-result-avatar-improved">
                        <div class="search-result-info-improved">
                            <div class="search-result-name-improved">${user.name}</div>
                            <div class="search-result-username-improved">@${user.username}</div>
                            <div class="search-result-bio">${user.bio}</div>
                        </div>
                        <div class="search-result-actions-improved">
                            <button class="search-action-btn add-friend-btn">
                                <span>+</span> Agregar
                            </button>
                            <button class="search-action-btn view-profile-btn">
                                👁️ Ver
                            </button>
                        </div>
                    </div>
                `;
            });

            resultsHTML += `
                <div class="search-stats">
                    <p>Mostrando ${sampleUsers.length} resultados para "${searchQuery}"</p>
                </div>
            `;

            resultsContainer.innerHTML = resultsHTML;
            
            // Agregar event listeners a los botones después de crear el HTML
            document.querySelectorAll('.add-friend-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const userId = this.closest('.search-result-card').getAttribute('data-user-id');
                    sendFriendRequest(userId);
                });
            });
            
            document.querySelectorAll('.view-profile-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const userId = this.closest('.search-result-card').getAttribute('data-user-id');
                    viewProfile(userId);
                });
            });
        }, 1500);
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


// Funciones auxiliares para acciones de búsqueda
function sendFriendRequest(userId) {
    // Simular envío de solicitud
    const btn = event.target.closest('.add-friend-btn');
    const originalHTML = btn.innerHTML;
    
    btn.innerHTML = '<span>⏳</span> Enviando...';
    btn.disabled = true;
    
    setTimeout(() => {
        btn.innerHTML = '<span>✓</span> Enviado';
        btn.style.background = 'linear-gradient(45deg, #51cf66, #40c057)';
        
        // Mostrar notificación
        showNotification('Solicitud de amistad enviada correctamente', 'success');
    }, 1000);
}

function viewProfile(userId) {
    showNotification(`Perfil de usuario ${userId} - Funcionalidad en desarrollo`, 'info');
}

function showNotification(message, type = 'info') {
    // Crear elemento de notificación
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-icon">${type === 'success' ? '✓' : 'ℹ️'}</span>
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
    
    // Enviar formularios de modales
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Funcionalidad de cambiar contraseña - En desarrollo');
            passwordModal.style.display = 'none';
        });
    }
    
    if (deleteForm) {
        deleteForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const password = this.querySelector('input[name="confirm_password"]').value;
            if (password) {
                if (confirm('¿ESTÁS ABSOLUTAMENTE SEGURO? Esta acción no se puede deshacer.')) {
                    alert('Funcionalidad de eliminar cuenta - En desarrollo');
                    deleteModal.style.display = 'none';
                }
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
            alert(`Iniciar chat con usuario ${userId} - En desarrollo`);
            showSection('messages');
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
            alert(`Solicitud de ${userId} aceptada - En desarrollo`);
        }
        
        if (e.target.classList.contains('decline-request')) {
            const requestItem = e.target.closest('.request-item');
            const userId = requestItem.getAttribute('data-user-id');
            alert(`Solicitud de ${userId} rechazada - En desarrollo`);
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
    const menuToggle = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
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

// Función para enviar mensajes
function sendMessage() {
    const messageInput = document.querySelector('.message-input');
    const message = messageInput.value.trim();
    
    if (message) {
        addMessageToChat(message, true);
        messageInput.value = '';
        
        // Simular respuesta después de 1 segundo
        setTimeout(() => {
            const responses = [
                "¡Hola! ¿Cómo estás?",
                "Interesante, cuéntame más",
                "Estoy de acuerdo contigo",
                "¿En qué puedo ayudarte?"
            ];
            const randomResponse = responses[Math.floor(Math.random() * responses.length)];
            addMessageToChat(randomResponse, false);
        }, 1000);
    }
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