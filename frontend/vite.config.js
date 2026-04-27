import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

// https://vite.dev/config/
export default defineConfig({
  plugins: [react()],
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
  preview: {
    port: 3000,
    host: true,
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
