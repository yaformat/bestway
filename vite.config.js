import { fileURLToPath } from 'node:url'
import vue from '@vitejs/plugin-vue'
import vueJsx from '@vitejs/plugin-vue-jsx'
import AutoImport from 'unplugin-auto-import/vite'
import Components from 'unplugin-vue-components/vite'
import { defineConfig } from 'vite'
import Pages from 'vite-plugin-pages'
import Layouts from 'vite-plugin-vue-layouts'
import vuetify from 'vite-plugin-vuetify'
import { VitePWA } from 'vite-plugin-pwa'

import DefineOptions from 'unplugin-vue-define-options/vite'
import laravel from 'laravel-vite-plugin'

const baseUrl = process.env.VITE_BASE_URL || '/admin/'

export default defineConfig({
  base: baseUrl,
  plugins: [
    VitePWA({
      registerType: 'autoUpdate',
      filename: 'service-worker.js', 
      includeAssets: ['favicon.ico', 'robots.txt', 'apple-touch-icon.png'],
      manifest: {
        name: 'BestWay',
        short_name: 'BestWay',
        start_url: baseUrl,
        display: 'standalone',
        background_color: '#ffffff',
        theme_color: '#4f46e5', // основной цвет темы (Vuetify primary?)
        icons: [
          {
            src: 'icons/192x192.png',
            sizes: '192x192',
            type: 'image/png',
          },
          {
            src: 'icons/512x512.png',
            sizes: '512x512',
            type: 'image/png',
          },
        ],
      },
      workbox: {
        maximumFileSizeToCacheInBytes: 5 * 1024 * 1024, // увеличено до 5 МБ
        // Указываем, что нужно кэшировать
        globPatterns: ['**/*.{js,css,ico,png,svg,jpg}'], // Убираем html из списка
        // Не используем navigateFallback, так как у нас нет index.html
        navigateFallback: null,
        // Не используем importScripts
        inlineWorkboxRuntime: true,
        // Дополнительные настройки для навигационных запросов
        runtimeCaching: [
          {
            urlPattern: ({ url }) =>
              url.origin === 'https://BestWay.com' &&
              url.pathname.startsWith('/storage/img/'),
            handler: 'CacheFirst',
            options: {
              cacheName: 'images-cache-versioned',
              expiration: {
                maxEntries: 250,
                maxAgeSeconds: 60 * 60 * 24 * 30, // 30 дней
              },
              cacheableResponse: {
                statuses: [0, 200],
              },
              // don't include matchOptions.ignoreSearch — query string WILL be used
            },
          },
          {
            urlPattern: /\.(?:png|jpg|jpeg|svg|gif|webp)$/,
            handler: 'CacheFirst',
            options: {
              cacheName: 'images-cache',
              expiration: {
                maxEntries: 60,
                maxAgeSeconds: 60 * 60 * 24 * 30 // 30 дней
              }
            }
          },
          {
            urlPattern: /\.(?:js|css)$/,
            handler: 'StaleWhileRevalidate',
            options: {
              cacheName: 'static-resources'
            }
          },
          {
            // Кэширование навигационных запросов
            urlPattern: ({ request }) => request.mode === 'navigate',
            handler: 'NetworkFirst',
            options: {
              cacheName: 'navigations-cache',
              networkTimeoutSeconds: 3
            }
          }
        ],
        // Отключаем генерацию NavigationRoute для index.html
        navigateFallbackDenylist: [/.*/], // Отключаем все навигационные фолбэки
      },


      devOptions: {
        enabled: true, // Включить в dev-режиме (можно отключить)
      },
    }),

    laravel({
      input: ['resources/js/main.js'],
      refresh: true,
    }),

    vue({
      template: {
          transformAssetUrls: {
              base: null,
              includeAbsolute: false,
          },
      },
    }),
    vueJsx(),
    vuetify({
      styles: {
        configFile: 'resources/styles/variables/_vuetify.scss',
      },
    }),
    Pages({
      dirs: ['./resources/js/pages'],
      importMode: 'sync',
    }),
    Layouts({
      layoutsDirs: './resources/js/layouts/',
    }),
    Components({
      dirs: ['resources/js/@core/components', 'resources/js/views/demos', 'resources/js/components'],
      dts: true,
    }),
    AutoImport({
      eslintrc: {
        enabled: true,
        filepath: './.eslintrc-auto-import.json',
      },
      imports: ['vue', 'vue-router', '@vueuse/core', '@vueuse/math', 'pinia'],
      vueTemplate: true,
    }),
    DefineOptions(),
  ],
  define: { 'process.env': {} },
  resolve: {
    alias: {
      '@core-scss': fileURLToPath(new URL('./resources/styles/@core', import.meta.url)),
      '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
      '@themeConfig': fileURLToPath(new URL('./themeConfig.js', import.meta.url)),
      '@core': fileURLToPath(new URL('./resources/js/@core', import.meta.url)),
      '@layouts': fileURLToPath(new URL('./resources/js/@layouts', import.meta.url)),
      '@images': fileURLToPath(new URL('./resources/images/', import.meta.url)),
      '@styles': fileURLToPath(new URL('./resources/styles/', import.meta.url)),
      '@configured-variables': fileURLToPath(new URL('./resources/styles/variables/_template.scss', import.meta.url)),
      '@axios': fileURLToPath(new URL('./resources/js/plugins/axios', import.meta.url)),
      '@validators': fileURLToPath(new URL('./resources/js/@core/utils/validators', import.meta.url)),
      'apexcharts': fileURLToPath(new URL('node_modules/apexcharts-clevision', import.meta.url)),
    },
  },
  build: {
    manifest: 'manifest.json',
    outDir: 'public/build',
    rollupOptions: {
      output: {
        inlineDynamicImports: true, // inline dynamic imports
        entryFileNames: `bundle-[hash].js`, // итоговое имя файла
      },
    },
    assetsInlineLimit: 100000000, // встраивает все ассеты в js
    //cssCodeSplit: false, // всё CSS внутрь JS
  },
  optimizeDeps: {
    exclude: ['vuetify'],
    entries: [
      './resources/js/**/*.vue',
    ],
  },
  server: {
    host: '0.0.0.0',
    hmr: {
        host: 'localhost'
    },
  }
})
