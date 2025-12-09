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
        strictPort: true,
        port: 5173,
        origin: 'http://localhost:5173',
        cors: true,
        hmr: {
            host: 'localhost'
        }
    }
}));
