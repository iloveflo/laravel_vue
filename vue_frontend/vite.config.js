import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'

// https://vite.dev/config/
export default defineConfig({
  base: '/frontend/',
  plugins: [
    vue(),
    vueDevTools(),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    },
  },
  build: {
    outDir: 'dist', // Thư mục output khi build
  },
  
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost:8000', // địa chỉ Laravel
        changeOrigin: true,
        secure: false,
      }
    }
  },
})
