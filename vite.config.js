import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0', // ← Escucha en todas las interfaces de red
        port: 5173,
        hmr: {
            host: '192.168.1.3', // ← Usa TU IP local aquí (importante para WebSocket de HMR)
        },
        cors: {
            origin: ['http://192.168.1.3:8001'], // ← Permite solicitudes desde tu IP + puerto del servidor Laravel
            credentials: true,
        },
    },
});
