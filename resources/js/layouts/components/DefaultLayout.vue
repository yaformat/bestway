<script setup>
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import navItems from '@/navigation/vertical'
import { useThemeConfig } from '@core/composable/useThemeConfig'
import { useDisplay } from 'vuetify'

// Components
// Components
import Footer from '@/layouts/components/Footer.vue'
import UserProfile from '@/layouts/components/UserProfile.vue'

// @layouts plugin
import { VerticalNavLayout } from '@layouts'

const { appRouteTransition } = useThemeConfig()
const { mobile } = useDisplay()

const route = useRoute()
const router = useRouter()

// Состояние мобильного меню
const mobileMenuOpen = ref(false)

// Получаем название текущей страницы
const currentPageTitle = computed(() => {
  // Ваша логика определения заголовка...
  const findRouteTitle = (items, routeName) => {
    for (const item of items) {
      if (item.to?.name === routeName) {
        return item.title
      }
      if (item.children) {
        const childTitle = findRouteTitle(item.children, routeName)
        if (childTitle) return childTitle
      }
    }
    return null
  }
  
  const foundTitle = findRouteTitle(navItems, route.name)
  
  if (!foundTitle && route.meta?.title) {
    return route.meta.title
  }
  
  if (route.name === 'resource-resourceType') {
    const typeMap = {
      'ingredient': 'Ингредиенты',
      'semi_finished': 'Полуфабрикаты',
      'product': 'Продукция',
      'household': 'Хоз. принадлежности',
      'equipment': 'Оборудование',
      'misc': 'Прочее'
    }
    return typeMap[route.params.resourceType] || 'Ресурсы'
  }
  
  if (route.name && route.name.includes('create')) {
    if (route.name.includes('production')) return 'Создание производства'
    if (route.name.includes('techcard')) return 'Создание техкарты'
    if (route.name.includes('resource')) return 'Создание ресурса'
    if (route.name.includes('supplier')) return 'Создание поставщика'
    return 'Создание'
  }
  
  if (route.name && route.name.includes('edit')) {
    if (route.name.includes('production')) return 'Редактирование производства'
    if (route.name.includes('techcard')) return 'Редактирование техкарты'
    if (route.name.includes('resource')) return 'Редактирование ресурса'
    if (route.name.includes('supplier')) return 'Редактирование поставщика'
    return 'Редактирование'
  }
  
  if (route.name === 'index') {
    return 'Главная'
  }
  
  return foundTitle || 'Страница'
})

const canGoBack = computed(() => {
  return route.name !== 'index' && window.history.length > 1
})

const goBack = () => {
  if (canGoBack.value) {
    router.go(-1)
  }
}

const toggleMobileMenu = () => {
  mobileMenuOpen.value = !mobileMenuOpen.value
}
</script>

<template>
  <VerticalNavLayout :nav-items="navItems">
    <!-- 👉 navbar -->
    <template #navbar="{ toggleVerticalOverlayNavActive }">
      <div class="d-flex h-100 align-center">
        <!-- Back Button -->
        <IconBtn
          v-if="canGoBack"
          class="me-2"
          @click="goBack"
        >
          <VIcon icon="mdi-arrow-left" />
        </IconBtn>

        <!-- Page Title -->
        <h1 class="text-h6 font-weight-medium mb-0 page-title">
          {{ currentPageTitle }}
        </h1>

        <VSpacer />

        <!-- На мобильных: Burger Menu справа -->
        <!-- На десктопе: User Profile -->
        <IconBtn
          v-if="mobile"
          @click="toggleMobileMenu"
        >
          <VIcon icon="mdi-menu" />
        </IconBtn>
        <UserProfile v-else />
      </div>
    </template>

    <!-- 👉 Pages -->
    <RouterView v-slot="{ Component }">
      <Transition
        :name="appRouteTransition"
        mode="out-in"
      >
        <Component :is="Component" />
      </Transition>
    </RouterView>

    <!-- 👉 Footer -->
    <template #footer>
      <Footer />
    </template>

    <!-- 👉 Mobile Menu -->
    <MobileMenu v-model="mobileMenuOpen" />
  </VerticalNavLayout>
</template>

<style lang="scss" scoped>
.page-title {
  color: rgba(var(--v-theme-on-surface), var(--v-high-emphasis-opacity));
  
  @media (max-width: 600px) {
    font-size: 1.1rem;
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
}
</style>
