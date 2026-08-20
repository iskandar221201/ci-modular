/* global process */
import { fileURLToPath, URL } from 'node:url'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { visualizer } from 'rollup-plugin-visualizer'

export default defineConfig({
  plugins: [
    vue(),
    process.env.ANALYZE === 'true'
      ? visualizer({ open: false, gzipSize: true, filename: 'stats.html' })
      : null,
  ].filter(Boolean),
  base: '/dist/',
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  build: {
    outDir: '../public/dist',
    emptyOutDir: true,
    sourcemap: 'hidden',
  },
  server: {
    port: 5173,
    proxy: {
      '/api': 'http://localhost:8080',
    },
  },
})
