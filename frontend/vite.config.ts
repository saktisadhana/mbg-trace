import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// Project GitHub Pages serves from https://<user>.github.io/mbg-trace/,
// so assets must be requested under that sub-path in production.
export default defineConfig(({ command }) => ({
  base: command === 'build' ? '/mbg-trace/' : '/',
  plugins: [react()],
  server: {
    // Allow access via Cloudflare quick-tunnel hostnames for public demos
    allowedHosts: true,
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
      }
    }
  }
}))
