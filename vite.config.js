import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig(({ command }) => ({
    root: 'resources',
    base: command === 'serve' ? '/' : '/assets/',
    build: {
        outDir: '../public/assets',
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: {
                main: resolve(__dirname, 'resources/js/main.js'),
                app: resolve(__dirname, 'resources/css/app.css')
            }
        }
    },
    server: {
        host: '0.0.0.0',
        strictPort: true,
        port: 5173,
        origin: 'http://127.0.0.1:5173',
        cors: true,
        hmr: {
            host: '127.0.0.1'
        }
    }
}));
