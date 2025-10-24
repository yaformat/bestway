<script setup>
import { useSkins } from '@/@core/composable/useSkins'
import { useThemeConfig } from '@/@core/composable/useThemeConfig'

import axios from '@/plugins/axios' 

// @layouts plugin
import { AppContentLayoutNav } from '@/@layouts/enums'

//const DefaultLayoutWithHorizontalNav = defineAsyncComponent(() => import('./components/DefaultLayoutWithHorizontalNav.vue'))
//const DefaultLayout = defineAsyncComponent(() => import('./components/DefaultLayout.vue'))

// import DefaultLayoutWithHorizontalNav from './components/DefaultLayoutWithHorizontalNav.vue'
// import DefaultLayoutWithVerticalNav from './components/DefaultLayoutWithVerticalNav.vue'

import DefaultLayout from './components/DefaultLayout.vue'

const { width: windowWidth } = useWindowSize()
const { appContentLayoutNav, switchToVerticalNavOnLtOverlayNavBreakpoint } = useThemeConfig()

// Remove below composable usage if you are not using horizontal nav layout in your app
switchToVerticalNavOnLtOverlayNavBreakpoint(windowWidth)

const { layoutAttrs, injectSkinClasses } = useSkins()

injectSkinClasses()

const navItems = ref([]) 
axios.get('/navigation').then(({ data }) => { 
     data = [];
    console.log('get navigation');
    navItems.value = data 
}) 
</script>

<template>
    <DefaultLayout v-bind="layoutAttrs" />
</template>

<style lang="scss">
// As we are using `layouts` plugin we need its styles to be imported
@use "@layouts/styles/default-layout";
</style>
