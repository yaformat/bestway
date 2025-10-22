<template>
  <VNavigationDrawer
    v-model="isOpen"
    location="right"
    temporary
    width="280"
    class="mobile-menu"
  >
    <!-- Header с профилем пользователя -->
    <div class="mobile-menu-header">
      <div v-if="userDataLoaded" class="user-profile-section">
        <div class="d-flex align-center pa-4">
          <UserAvatar 
            :photo="user.photo" 
            :activity-at="user.activity_at" 
            :show-status="true" 
            :force-online="user.is_online"
            :width="60"
            class="me-3"
          />
          <div class="flex-grow-1">
            <div class="text-h6 font-weight-semibold">
              {{ user.initial_name }}
            </div>
            <div v-if="user.organization && user.organization.name" class="text-body-2 text-medium-emphasis">
              {{ user.organization.name }}
            </div>
          </div>
        </div>
      </div>
      
      <VDivider />
    </div>

    <!-- Menu Content with Slide Animation -->
    <div class="menu-content">
      <div class="menu-container" :style="{ transform: `translateX(${translateX}px)` }">
        <!-- Main Menu -->
        <div class="menu-level main-menu">
          <VList nav>
            <!-- Основные разделы из навигации -->
            <template v-for="item in mainNavItems" :key="item.title">
              <!-- Простые элементы -->
              <VListItem
                v-if="!item.children"
                :to="item.to"
                :class="{ 'v-list-item--active': isActiveRoute(item) }"
                @click="closeMenu"
              >
                <template #prepend>
                  <VIcon :icon="item.icon.icon" />
                </template>
                <VListItemTitle>{{ item.title }}</VListItemTitle>
              </VListItem>

              <!-- Элементы с подменю -->
              <VListItem
                v-else
                :class="{ 
                  'v-list-item--active': hasActiveChild(item),
                  'submenu-parent': true 
                }"
                @click="openSubmenu(item)"
              >
                <template #prepend>
                  <VIcon :icon="item.icon.icon" />
                </template>
                <VListItemTitle>{{ item.title }}</VListItemTitle>
                <template #append>
                  <VIcon icon="mdi-chevron-right" />
                </template>
              </VListItem>
            </template>

            <VDivider class="my-2" />

            <!-- Профиль и настройки -->
            <VListItem
              :to="{ name: 'profile', hash: '#account' }"
              @click="closeMenu"
            >
              <template #prepend>
                <VIcon icon="mdi-account" />
              </template>
              <VListItemTitle>Профиль</VListItemTitle>
            </VListItem>

            <VListItem
              :to="{ name: 'profile', hash: '#settings' }"
              @click="closeMenu"
            >
              <template #prepend>
                <VIcon icon="mdi-cog" />
              </template>
              <VListItemTitle>Настройки</VListItemTitle>
            </VListItem>

            <VDivider class="my-2" />

            <!-- Выход -->
            <VListItem
              to="/login"
              @click="closeMenu"
            >
              <template #prepend>
                <VIcon icon="mdi-logout" />
              </template>
              <VListItemTitle>Выход</VListItemTitle>
            </VListItem>
          </VList>
        </div>

        <!-- Submenu -->
        <div class="menu-level submenu">
          <!-- Back Button Header -->
          <div class="submenu-header">
            <VListItem @click="goBack" class="back-button">
              <template #prepend>
                <VIcon icon="mdi-arrow-left" />
              </template>
              <VListItemTitle class="font-weight-semibold">
                {{ currentSubmenu?.title || 'Назад' }}
              </VListItemTitle>
            </VListItem>
            <VDivider />
          </div>

          <!-- Submenu Items -->
          <VList nav>
            <VListItem
              v-for="child in currentSubmenu?.children || []"
              :key="child.title"
              :to="child.to"        
              :class="{ 'v-list-item--active': isActiveRoute(child) }"
              @click="closeMenu"
            >
              <VListItemTitle>{{ child.title }}</VListItemTitle>
            </VListItem>
          </VList>
        </div>
      </div>
    </div>
  </VNavigationDrawer>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useUserProfileStore } from '@/stores/userProfileStore'
import UserAvatar from '@/components/UserAvatar.vue'
import navItems from '@/navigation/vertical'

const route = useRoute()

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue'])

const userProfileStore = useUserProfileStore()


// Ручная проверка активности маршрута
const isActiveRoute = (item) => {
  if (!item.to) return false

  // console.log('router', route.path + ' + name: '+route.name);
  // console.log('link', item.to.path);
  
  // if (typeof item.to === 'string') {
  //   return route.path === item.to
  // }
  
  if (typeof item.to === 'object') {
    let isActive = false
    
    if (item.to.path) {
      isActive = route.path.startsWith(item.to.path) ?? false;
    } else if (item.to.name && route.name === item.to.name) {
      isActive = true
    }
    
    return isActive
  }
  
  return false
}

// Проверка активности подменю (есть ли активные дочерние элементы)
const hasActiveChild = (item) => {
  if (!item.children || item.children.length === 0) return false
  
  return item.children.some(child => {
    if (isActiveRoute(child)) return true
    // Рекурсивная проверка для вложенных подменю
    if (child.children) {
      return hasActiveChild(child)
    }
    return false
  })
}

// Проверка должно ли подменю быть открыто
const shouldSubmenuBeOpen = (item) => {
  return hasActiveChild(item)
}

const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const user = ref({})
const userDataLoaded = ref(false)

// Menu navigation state
const currentLevel = ref('main')
const currentSubmenu = ref(null)
const translateX = ref(0)
const isAnimating = ref(false)

// Фильтруем основные элементы навигации (исключаем служебные)
const mainNavItems = computed(() => {
  return navItems.filter(item => 
    !['Пользователи', 'Организации', 'Настройки', 'Переводы'].includes(item.title)
  )
})

const openSubmenu = (item) => {
  if (isAnimating.value) return
  
  currentSubmenu.value = item
  currentLevel.value = 'submenu'
  isAnimating.value = true
  
  // Анимация сдвига влево
  translateX.value = -280
  
  // Завершаем анимацию
  setTimeout(() => {
    isAnimating.value = false
  }, 250)
}

const goBack = () => {
  if (isAnimating.value) return
  
  isAnimating.value = true
  currentLevel.value = 'main'
  
  // Анимация сдвига вправо
  translateX.value = 0
  
  // Завершаем анимацию и очищаем submenu
  setTimeout(() => {
    currentSubmenu.value = null
    isAnimating.value = false
  }, 250)
}

const closeMenu = () => {
  isOpen.value = false
  // Reset menu state when closing
  setTimeout(() => {
    currentLevel.value = 'main'
    currentSubmenu.value = null
    translateX.value = 0
    isAnimating.value = false
  }, 300)
}

// Watchers
watch(() => userProfileStore.userDataLoaded, (newVal) => {
  userDataLoaded.value = newVal
})

watch(() => userProfileStore.user, (data) => {
  user.value = data
})

// Reset menu state when opening
watch(isOpen, (newValue) => {
  if (newValue) {
    currentLevel.value = 'main'
    currentSubmenu.value = null
    translateX.value = 0
    isAnimating.value = false
  }
})

// Обновляем состояние подменю при изменении маршрута
watch(() => route.path, () => {
  // Проверяем все элементы навигации
  mainNavItems.value.forEach(item => {
    if (item.children && shouldSubmenuBeOpen(item)) {
      // Если есть активный дочерний элемент, открываем подменю
      openSubmenu(item)
    }
  })
}, { immediate: true })

onMounted(() => {
  mainNavItems.value.forEach(item => {
    if (item.children && shouldSubmenuBeOpen(item)) {
      openSubmenu(item)
    }
  })

  userProfileStore.fetchUserProfile()
})
</script>

<style scoped>
.mobile-menu {
  z-index: 2000;
}

.mobile-menu-header {
  background: rgba(var(--v-theme-surface), 1);
  border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
}

.user-profile-section {
  background: linear-gradient(135deg, rgba(var(--v-theme-primary), 0.1), rgba(var(--v-theme-secondary), 0.05));
}

.menu-content {
  height: calc(100% - 120px);
  overflow: hidden;
  position: relative;
  background: rgba(var(--v-theme-surface), 1);
}

.menu-container {
  display: flex;
  width: 560px; /* 280px * 2 для двух меню */
  height: 100%;
  transition: transform 0.25s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  will-change: transform;
}

.menu-level {
  width: 280px;
  height: 100%;
  overflow-y: auto;
  flex-shrink: 0;
  background: rgba(var(--v-theme-surface), 1);
}

.main-menu {
  /* Основное меню */
}

.submenu {
  /* Подменю */
}

.submenu-header {
  position: sticky;
  top: 0;
  background: rgba(var(--v-theme-surface), 1);
  z-index: 10;
}

.back-button {
  background: rgba(var(--v-theme-primary), 0.05);
  margin: 0!important;
  border-radius: 0!important;
}

.back-button:hover {
  background: rgba(var(--v-theme-primary), 0.1);
}

:deep(.v-list-item) {
  border-radius: 8px;
  margin: 2px 8px;
}

/* Стили для активных элементов */
:deep(.v-list-item--active) {
  background: rgba(var(--v-theme-primary), 0.1) !important;
  color: rgb(var(--v-theme-primary)) !important;
}

:deep(.v-list-item--active .v-list-item__prepend .v-icon) {
  color: rgb(var(--v-theme-primary)) !important;
}

/* Стили для родительского элемента с активным дочерним */
.submenu-parent.v-list-item--active {
  background: rgba(var(--v-theme-primary), 0.05) !important;
}

/* Улучшенная производительность анимации */
.menu-container {
  backface-visibility: hidden;
  -webkit-backface-visibility: hidden;
  transform-style: preserve-3d;
}
</style>
