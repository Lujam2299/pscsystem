import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Aseguramos Pusher globalmente
window.Pusher = Pusher;

// Configuración de Echo
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY || 'xljsvzjxtm2snpwbmlug',
    wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
    wsPort: Number(import.meta.env.VITE_REVERB_PORT || 9000),
    wssPort: Number(import.meta.env.VITE_REVERB_PORT || 443),
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME || window.location.protocol.replace(':', '')) === 'https',
    enabledTransports: ['ws', 'wss'],
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
    window.dispatchEvent(new CustomEvent('echo-state-change', { detail: { state: 'connected' } }));
});

window.Echo.connector.pusher.connection.bind('error', (err) => {
    console.error('❌ Echo: Error de conexión', err);
    window.EchoConnectionState = 'error';
    window.dispatchEvent(new CustomEvent('echo-state-change', { detail: { state: 'error' } }));
});

window.Echo.connector.pusher.connection.bind('state_change', (states) => {
    console.log('🔁 Estado de conexión:', states);
    window.EchoConnectionState = states.current;
    window.dispatchEvent(new CustomEvent('echo-state-change', { detail: { state: states.current } }));
});
