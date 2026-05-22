import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

// Supprime l'attribut crossorigin des balises script/link du build
function removeCrossOrigin() {
  return {
    name: 'remove-crossorigin',
    transformIndexHtml(html) {
      return html.replace(/ crossorigin/g, '');
    },
  };
}

export default defineConfig({
  plugins: [react(), removeCrossOrigin()],
  server: {
    port: 3000,
    host: true,
    allowedHosts: true,
    watch: {
      usePolling: true,
    },
    proxy: {
      '/api': {
        target: 'http://backend:8000',
        changeOrigin: true,
      },
      '/auth/google/redirect': {
        target: 'http://backend:8000',
        changeOrigin: true,
      },
      '/auth/google/callback': {
        target: 'http://backend:8000',
        changeOrigin: true,
      },
    },
  },
  build: {
    modulePreload: false,
  },
  preview: {
    port: 3000,
    host: true,
    headers: {
      'Access-Control-Allow-Origin': '*',
      'Access-Control-Allow-Methods': 'GET, OPTIONS',
      'Access-Control-Allow-Headers': '*',
    },
    proxy: {
      '/api': {
        target: 'http://backend:8000',
        changeOrigin: true,
      },
      '/auth/google/redirect': {
        target: 'http://backend:8000',
        changeOrigin: true,
      },
      '/auth/google/callback': {
        target: 'http://backend:8000',
        changeOrigin: true,
      },
    },
  },
});
