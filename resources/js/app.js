// resources/js/app.js
import './bootstrap';
import './echo';

// Inicializar listeners de chat si estamos en una vista de conversación
document.addEventListener('DOMContentLoaded', function() {
    // Buscar ID de conversación en múltiples lugares posibles
    const conversationElement = document.querySelector('[data-conversation-id]') ||
                              document.querySelector('#conversation-id') ||
                              document.querySelector('[data-chat]');

    const conversationId = conversationElement?.dataset.conversationId ||
                          conversationElement?.dataset.chat ||
                          document.querySelector('#conversation-id')?.textContent?.trim();

    // Si encontramos un ID de conversación, iniciamos la escucha
    if (conversationId && typeof window.setupChatListeners === 'function') {
        // Convertir a número si es necesario
        const numericId = parseInt(conversationId, 10);
        if (!isNaN(numericId)) {
            window.setupChatListeners(numericId);
        }
    }
});

// Opcional: Si necesitas acceder globalmente
window.LivewireEcho = {
    setup: (conversationId, componentId) => {
        if (window.setupChatListeners) {
            window.setupChatListeners(conversationId, componentId);
        }
    }
};

