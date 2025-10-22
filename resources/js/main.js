/* eslint-disable import/order */
import '@/@iconify/icons-bundle'
import App from '@/App.vue'
import ability from '@/plugins/casl/ability'
import layoutsPlugin from '@/plugins/layouts'
import VueLazyLoad from 'vue3-lazyload'
import vuetify from '@/plugins/vuetify'
//import { loadFonts } from '@/plugins/webfontloader'
import router from '@/router'
import { abilitiesPlugin } from '@casl/vue'
import '@core-scss/template/index.scss'
import '@styles/styles.scss'
import '@styles/fonts.scss'
import { createPinia } from 'pinia'
import { createApp } from 'vue'
import { i18n, fetchTranslations } from '@/plugins/i18n'
import { registerServiceWorker } from './registerSW';

// Регистрируем Service Worker
registerServiceWorker();

//loadFonts() // Подгружаем шрифты

fetchTranslations() // Переводы

// Create vue app
const app = createApp(App)

// Use plugins
app.use(vuetify)
const pinia = createPinia();
app.use(pinia);
app.use(router)

// Инициализация данных
app.use(layoutsPlugin)
app.use(VueLazyLoad, {
  error: 'data:image/svg+xml;base64,PHN2ZyBpZD0iTGF5ZXJfMSIgZGF0YS1uYW1lPSJMYXllciAxIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxOTkxLjI3IDE5MjEuMzMiPjxkZWZzPjxzdHlsZT4uY2xzLTF7ZmlsbDojZmZmO2ZpbGwtb3BhY2l0eTowfS5jbHMtMntmaWxsOnNpbHZlcn08L3N0eWxlPjwvZGVmcz48dGl0bGU+cGxhY2Vob2xkZXIgaWNvbjwvdGl0bGU+PHBhdGggY2xhc3M9ImNscy0xIiBkPSJNMCAwaDE5OTEuMjd2MTkyMS4zM0gweiIvPjxwYXRoIGNsYXNzPSJjbHMtMiIgZD0iTTEyODguNTIgNzg3LjUxbC0zNC4yLTE5OS45LTcwMC4xNSAxMjIuOTMgMTAyLjYxIDU4NC43MSA2OS40OS0xMS43NnY1MC4yNGg3MTAuODNWNzg3LjUxem0tNTYyLjI1IDQzNy4xOGwtMjIuNDUgNC4zMS04My4zOC00NzEuNCA1ODYuODQtMTAzLjY5IDIzLjUyIDEzMy42Mkg3MjYuMjd6bTY1My4xMSA1MS4zMUg3ODRWODQ1LjIyaDU5NS40eiIvPjxwYXRoIGNsYXNzPSJjbHMtMiIgZD0iTTgyMi40NiAxMjMwLjA4aDUxOC40NHYtMTcwLjIybC0xMDktMTIzLjZhMjggMjggMCAwIDAtNDQuNzQgMy41OGwtOTMuOSAxNDguODJhMTguMjcgMTguMjcgMCAwIDEtMjcuNzMgMy43OGwtNjEuMDItNTUuNDRhMjUuMjggMjUuMjggMCAwIDAtMzcuMDcgMy4zN3oiLz48Y2lyY2xlIGNsYXNzPSJjbHMtMiIgY3g9Ijg5Mi4xNiIgY3k9Ijk0OC4yIiByPSI0MC44MSIvPjwvc3ZnPg==', // Замените на путь к вашему изображению-заглушке
  //loading: 'path/to/loading/image.gif', // Замените на путь к вашему изображению-загрузке (опционально) 
})
app.use(i18n)

app.use(abilitiesPlugin, ability, {
  useGlobalProperties: true,
})

//Инициализация данных после монтирования приложения
import { useGlobalDataStore } from './stores/globalDataStore'
const globalDataStore = useGlobalDataStore()
globalDataStore.fetchData()


// Mount vue app
app.mount('#app')
