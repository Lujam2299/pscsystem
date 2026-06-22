// resources/js/app.js
import './bootstrap';
import './echo';

const MOVEMENT_INACTIVITY_MINUTES = 30;
const notifiedMovementStarts = new Set();

async function notifyMovementStart(event) {
    const position = event?.position;

    if (!position?.user_id || !position?.recorded_at) {
        return;
    }

    const role = position.user?.rol?.toLowerCase() || '';
    if (!role.includes('escolta')) {
        return;
    }

    const notificationKey = `${position.user_id}:${position.recorded_at}`;
    if (notifiedMovementStarts.has(notificationKey)) {
        return;
    }

    try {
        const response = await fetch(`/api/realtime-position/user/${encodeURIComponent(position.user_id)}/recent`, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            return;
        }

        const data = await response.json();
        const positions = data.positions || [];
        const currentPosition = positions[0];
        const previousPosition = positions[1];

        if (!currentPosition) {
            return;
        }

        const currentTime = new Date(currentPosition.recorded_at).getTime();
        const previousTime = previousPosition
            ? new Date(previousPosition.recorded_at).getTime()
            : null;

        const inactivityMinutes = previousTime === null
            ? Infinity
            : (currentTime - previousTime) / 60000;

        if (inactivityMinutes <= MOVEMENT_INACTIVITY_MINUTES) {
            return;
        }

        notifiedMovementStarts.add(notificationKey);

        if (typeof window.Swal !== 'undefined') {
            window.Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'info',
                title: `${position.user?.name || 'Un custodio'} está en movimiento`,
                text: 'Ha iniciado el seguimiento de ubicación.',
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
            });
        }
    } catch (error) {
        console.error('No fue posible verificar el inicio de movimiento:', error);
    }
}

// Inicializar listeners de chat si estamos en una vista de conversación
document.addEventListener('DOMContentLoaded', function() {
    // Buscar ID de conversación en múltiples lugares posibles
    if (typeof window.Echo !== 'undefined') {
        window.Echo.channel('realtime-positions.all')
            .listen('.NuevaUbicacionRealtime', notifyMovementStart);
    }

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
