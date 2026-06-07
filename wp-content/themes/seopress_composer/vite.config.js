import { defineConfig } from 'vite';
import path from 'path';

export default defineConfig({
  base: '',
  root: './',
  publicDir: false, // Disable publicDir to avoid conflicts
  build: {
    outDir: './dist',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: {
        main: path.resolve(__dirname, 'assets/js/main.js'),
      },
      output: {
        entryFileNames: 'assets/[name].js',
        chunkFileNames: 'assets/[name].js',
        assetFileNames: ({ name }) => {
          if (/\.(css)$/.test(name)) {
            return 'assets/main.css';
          }
          return 'assets/[name].[ext]';
        }
      }
    }
  },
  server: {
    port: 5173,
    strictPort: true,
    cors: true,
    origin: 'http://antik-live.test',
    hmr: {
      host: 'localhost',
      protocol: 'ws',
    },
    watch: {
      usePolling: true, // Better for Windows file watching
      interval: 100, // Check every 100ms
      ignored: ['**/node_modules/**', '**/vendor/**', '**/dist/**', '**/.git/**']
    }
  },
  plugins: [
    {
      name: 'php-reload',
      handleHotUpdate({ file, server }) {
        if (file.endsWith('.php')) {
          console.log(`PHP file changed: ${file}`);
          server.ws.send({
            type: 'full-reload',
            path: '*'
          });
          return [];
        }
      },
      configureServer(server) {
        // Watch PHP files explicitly
        const chokidar = require('chokidar');
        const watcher = chokidar.watch([
          '**/*.php',
          'inc/**/*.php',
          'template-parts/**/*.php'
        ], {
          ignored: ['**/node_modules/**', '**/vendor/**'],
          ignoreInitial: true,
          usePolling: true,
          interval: 100
        });

        watcher.on('change', (path) => {
          console.log(`PHP file changed: ${path}`);
          server.ws.send({
            type: 'full-reload',
            path: '*'
          });
        });
      }
    }
  ]
});
