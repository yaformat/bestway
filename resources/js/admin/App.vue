<script setup>
import { useTheme } from 'vuetify'
import ScrollToTop from '@/@core/components/ScrollToTop.vue'
import { useThemeConfig } from '@/@core/composable/useThemeConfig'
import { hexToRgb } from '@/@layouts/utils'

const {
  syncInitialLoaderTheme,
  syncVuetifyThemeWithTheme: syncConfigThemeWithVuetifyTheme,
  isAppRtl,
  handleSkinChanges,
} = useThemeConfig()

const { global } = useTheme()

// ℹ️ Sync current theme with initial loader theme
syncInitialLoaderTheme()
syncConfigThemeWithVuetifyTheme()
handleSkinChanges()

</script>

<template>
  <VLocaleProvider :rtl="isAppRtl">
    <!-- ℹ️ This is required to set the background color of active nav link based on currently active global theme's primary -->
    <VApp :style="`--v-global-theme-primary: ${hexToRgb(global.current.value.colors.primary)}`">
      <RouterView />
      <ScrollToTop />
      <SnackbarSimple />
    </VApp>
  </VLocaleProvider>
</template>


<style>
  body {
    font-family: 'Open Sans', sans-serif;
    padding-bottom: max(16px, env(safe-area-inset-bottom));
    padding-left: env(safe-area-inset-left);
    padding-right: env(safe-area-inset-right);
  }

  .v-application, .text-body-1, .text-body-2, .text-subtitle-1, .text-subtitle-2 {
    color:#333!important;
  }
  .table-row-hover:hover {
    background-color: #f0f0f0; /* Цвет фона при наведении */
  }
  .v-data-table__tr:hover .v-data-table__td {
    background-color: #f0f0f0; /* Цвет фона при наведении */
  }
  
  #nprogress .bar {
    background: rgb(var(--v-theme-primary)) !important;
  }
  #nprogress .spinner-icon {
    border-top-color: rgb(var(--v-theme-primary)) !important;
    border-left-color: rgb(var(--v-theme-primary)) !important;
  }
  #nprogress .bar {
    height: 3px; /* по умолчанию 2px */
  }

  input::-webkit-outer-spin-button,
  input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
  }
  
</style>