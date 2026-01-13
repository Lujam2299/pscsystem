import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Aseguramos Pusher globalmente
window.Pusher = Pusher;

// Configuración de Echo
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: 'xljsvzjxtm2snpwbmlug',
    wsHost: '192.168.1.3',
    wsPort: 9000,
    forceTLS: false,
    enabledTransports: ['ws'],
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
        }
    }
});

// Variables globales
window.EchoConnectionState = 'disconnected';
window.currentEchoChannel = null;

// Eventos de conexión
window.Echo.connector.pusher.connection.bind('connected', () => {
    console.log('✅ Echo: Conexión WebSocket establecida');
    console.log('🔑 Socket ID:', window.Echo.socketId());
    window.EchoConnectionState = 'connected';
});

window.Echo.connector.pusher.connection.bind('error', (err) => {
    console.error('❌ Echo: Error de conexión', err);
    window.EchoConnectionState = 'error';
});

window.Echo.connector.pusher.connection.bind('state_change', (states) => {
    console.log('🔁 Estado de conexión:', states);
    window.EchoConnectionState = states.current;
});
