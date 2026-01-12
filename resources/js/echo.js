// resources/js/echo.js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Aseguramos Pusher globalmente
window.Pusher = Pusher;

// Configuración de Echo
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: 'xljsvzjxtm2snpwbmlug', // Tu clave correcta
    wsHost: '192.168.1.3',      // Tu IP local
    wsPort: 9000,               // Puerto Reverb
    forceTLS: false,
    enabledTransports: ['ws'],
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
        }
    }
});

// Variables globales para seguimiento
window.EchoConnectionState = 'disconnected';
window.currentEchoChannel = null;

// Eventos de conexión WebSocket
window.Echo.connector.pusher.connection.bind('connected', () => {
    console.log('✅ Echo: Conexión WebSocket establecida');
    console.log('🔑 Socket ID:', window.Echo.socketId());
    window.EchoConnectionState = 'connected';

    // Si hay un canal pendiente de suscribirse, hacerlo ahora
    if (window.pendingChannelSubscription) {
        window.subscribeToPendingChannel();
    }
});

window.Echo.connector.pusher.connection.bind('error', (err) => {
    console.error('❌ Echo: Error de conexión', err);
    window.EchoConnectionState = 'error';
});

window.Echo.connector.pusher.connection.bind('state_change', (states) => {
    console.log('🔁 Estado de conexión:', states);
    window.EchoConnectionState = states.current;
});

// Variable para manejar suscripciones pendientes
window.pendingChannelSubscription = null;

// Función para suscribirse a canal pendiente
window.subscribeToPendingChannel = function() {
    if (window.pendingChannelSubscription && window.EchoConnectionState === 'connected') {
        const { conversationId, componentId } = window.pendingChannelSubscription;
        window.setupChatListeners(conversationId, componentId);
        window.pendingChannelSubscription = null;
    }
};

// Función global para escuchar conversaciones
window.setupChatListeners = function(conversationId, componentId = null) {
    if (!conversationId) {
        console.error('❌ Conversation ID is required');
        return;
    }

    if (window.EchoConnectionState !== 'connected') {
        console.warn('⚠️ Echo no está conectado aún, guardando suscripción pendiente');
        window.pendingChannelSubscription = { conversationId, componentId };
        return;
    }

    console.log(`🔔 Suscribiéndose a: conversacion.${conversationId}`);

    // Desuscribirse del canal anterior si existe
    if (window.currentEchoChannel) {
        console.log('📤 Desuscribiéndose del canal anterior:', window.currentEchoChannel);
        window.Echo.leave(window.currentEchoChannel);
    }

    // Guardar el nombre del canal actual
    window.currentEchoChannel = `conversacion.${conversationId}`;

    // Seleccionar canal privado
    const channel = window.Echo.private(window.currentEchoChannel);

    // Escuchar el evento específico
    channel.listen('.MensajeEnviado', (e) => {
        console.log('📨 Mensaje recibido del servidor:', e);

        // Si es un componente Livewire específico, notificarlo
        if (componentId) {
            // Notificar al componente Livewire
            if (window.Livewire) {
                try {
                    window.Livewire.dispatch('messageReceived', {
                        message: e.message,
                        conversationId: conversationId
                    });
                } catch (error) {
                    console.warn('⚠️ Error al notificar componente Livewire:', error);
                }
            }
        }

        // También disparar evento personalizado para otros listeners
        document.dispatchEvent(new CustomEvent('messageReceived', {
            detail: { message: e.message, conversationId: conversationId }
        }));
    });

    channel.error((error) => {
        console.error('❌ Error en canal WebSocket:', error);
    });

    console.log('✅ Canal WebSocket configurado correctamente');
    return channel;
};

document.addEventListener('DOMContentLoaded', function() {
    const conversationId = 20; // Ajusta según tu conversación actual

    if (conversationId) {
        // Escuchar canal público temporal
        const channel = window.Echo.channel(`public-conversacion.${conversationId}`);

        channel.listen('.MensajeEnviado', (e) => {
            console.log('📨 Mensaje recibido del servidor:', e);

            // Actualizar la UI del chat
            handleIncomingMessage(e, conversationId);
        });

        console.log(`🔔 Suscribiéndose a: public-conversacion.${conversationId}`);
    }
});
