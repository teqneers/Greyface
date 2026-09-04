import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';
import {fileURLToPath, URL} from 'node:url';
import {defineConfig} from 'vite';
import symfonyPlugin from 'vite-plugin-symfony';

// The PHP application runs in Docker (see docker/compose.yaml) and reads
// public/build/entrypoints.json from the bind-mounted repository. The dev
// server runs on the host: vite-plugin-symfony rewrites entrypoints.json to
// point at it, so the browser loads modules straight from localhost:15173.
// Ports use the 1xxxx prefix like the rest of the stack.
const devPort = Number(process.env.VITE_PORT || 15173);

// files/build.sh sets PUBLIC_PATH when the assets are served under a prefix
// other than /build. Vite calls the same thing `base` and wants a trailing slash.
const base = `${(process.env.PUBLIC_PATH || '/build').replace(/\/$/, '')}/`;

export default defineConfig({
    base,
    plugins: [
        react(),
        tailwindcss(),
        symfonyPlugin({
            // Exposes the dev-server origin to the bundle's /build proxy route.
            viteDevServerHostname: 'localhost',
        }),
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./assets/js', import.meta.url)),
        },
    },
    server: {
        host: 'localhost',
        port: devPort,
        strictPort: true,
        origin: `http://localhost:${devPort}`,
    },
    build: {
        outDir: 'public/build',
        emptyOutDir: true,
        manifest: true,
        sourcemap: false,
        rollupOptions: {
            input: {
                app: './assets/js/app.tsx',
                page: './assets/js/page.ts',
            },
        },
    },
});
