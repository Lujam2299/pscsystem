import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

/*export default defineConfig({
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
            host: '192.168.1.93', // ← Usa TU IP local aquí (importante para WebSocket de HMR)
        },
        cors: {
            //origin: ['http://192.168.1.3:8001'], // ← Permite solicitudes desde tu IP + puerto del servidor Laravel, reactivar después
            origin: ['http://192.168.1.93:8001'],
            credentials: true,
        },
    },
});*/
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    /*define: {
        'import.meta.env.VITE_REVERB_APP_KEY': JSON.stringify(process.env.REVERB_APP_KEY),
        'import.meta.env.VITE_REVERB_HOST': JSON.stringify(process.env.REVERB_HOST),
        'import.meta.env.VITE_REVERB_PORT': JSON.stringify(process.env.REVERB_PORT),
        'import.meta.env.VITE_REVERB_SCHEME': JSON.stringify(process.env.REVERB_SCHEME),
    },*/
    // Comenta toda la sección server temporalmente

    server: {
        host: '0.0.0.0',
        port: 5173,
        hmr: {
            host: 'localhost',
        },
        cors: {
            origin: ['http://localhost:8000'],
            credentials: true,
        },
    },

});
