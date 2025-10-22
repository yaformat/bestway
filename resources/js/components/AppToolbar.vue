<template>
  <VAppBar
    :elevation="scrolled ? 2 : 0"
    :color="scrolled ? 'surface' : 'transparent'"
    class="app-toolbar"
    :class="{ 'scrolled': scrolled }"
    height="64"
    flat
  >
    <div class="toolbar-content">
      <!-- Левая часть -->
      <div class="toolbar-left">
        <!-- Кнопка назад -->
        <VBtn
          v-if="toolbarState.showBackButton"
          icon
          variant="text"
          size="small"
          class="back-btn"
          @click="handleBackClick"
        >
          <VIcon size="20">mdi-arrow-left</VIcon>
        </VBtn>

        <!-- Заголовок -->
        <div class="toolbar-title">
          <h1 class="title-text">{{ toolbarState.title || defaultTitle }}</h1>
          <p v-if="toolbarState.subtitle" class="subtitle-text">
            {{ toolbarState.subtitle }}
          </p>
        </div>
      </div>

      <!-- Правая часть -->
      <div class="toolbar-right">
        <!-- Кастомные кнопки -->
        <template v-if="toolbarState.customButtons?.length">
          <VBtn
            v-for="(button, index) in toolbarState.customButtons"
            :key="index"
            :variant="button.variant || 'text'"
            :color="button.color || 'primary'"
            :size="button.size || 'small'"
            :icon="button.icon"
            class="toolbar-btn"
            @click="button.action"
          >
            <VIcon v-if="button.icon" :size="button.iconSize || 20">
              {{ button.icon }}
            </VIcon>
            <span v-if="button.text && !isMobile">{{ button.text }}</span>
          </VBtn>
        </template>

        <!-- Стандартные действия -->
        <template v-else>
          <!-- Поиск -->
          <VBtn
            v-if="toolbarState.showSearch"
            icon
            variant="text"
            size="small"
            class="toolbar-btn"
            @click="toggleSearch"
          >
            <VIcon size="20">mdi-magnify</VIcon>
          </VBtn>

          <!-- Уведомления -->
          <VBtn
            v-if="toolbarState.showNotifications"
            icon
            variant="text"
            size="small"
            class="toolbar-btn"
            @click="openNotifications"
          >
            <VBadge
              v-if="notificationCount > 0"
              :content="notificationCount"
              color="error"
              offset-x="2"
              offset-y="2"
            >
              <VIcon size="20">mdi-bell-outline</VIcon>
            </VBadge>
            <VIcon v-else size="20">mdi-bell-outline</VIcon>
          </VBtn>

          <!-- Меню действий -->
          <VMenu v-if="toolbarState.showMenu" offset-y>
            <template #activator="{ props }">
              <VBtn
                icon
                variant="text"
                size="small"
                class="toolbar-btn"
                v-bind="props"
              >
                <VIcon size="20">mdi-dots-vertical</VIcon>
              </VBtn>
            </template>
            
            <VList density="compact" min-width="200">
              <VListItem
                v-for="(item, index) in toolbarState.menuItems"
                :key="index"
                :prepend-icon="item.icon"
                :title="item.title"
                @click="item.action"
              />
            </VList>
          </VMenu>

          <!-- Профиль пользователя (только на десктопе) -->
          <VMenu v-if="!isMobile" offset-y>
            <template #activator="{ props }">
              <VBtn
                variant="text"
                class="user-btn"
                v-bind="props"
              >
                <VAvatar size="32" class="me-2">
                  <VImg
                    v-if="user?.avatar"
                    :src="user.avatar"
                    :alt="user.name"
                  />
                  <VIcon v-else>mdi-account</VIcon>
                </VAvatar>
                <span class="user-name">{{ user?.name || 'Пользователь' }}</span>
                <VIcon size="16" class="ms-1">mdi-chevron-down</VIcon>
              </VBtn>
            </template>
            
            <VList density="compact" min-width="200">
              <VListItem prepend-icon="mdi-account" title="Профиль" @click="openProfile" />
              <VListItem prepend-icon="mdi-cog" title="Настройки" @click="openSettings" />
              <VDivider />
              <VListItem prepend-icon="mdi-logout" title="Выйти" @click="logout" />
            </VList>
          </VMenu>

          <!-- Бургер меню (только на мобильном) -->
          <VBtn
            v-if="isMobile && toolbarState.showBurger"
            icon
            variant="text"
            size="small"
            class="toolbar-btn"
            @click="toggleMobileMenu"
          >
            <VIcon size="20">mdi-menu</VIcon>
          </VBtn>
        </template>
      </div>
    </div>

    <!-- Поисковая строка -->
    <VExpandTransition>
      <div v-if="showSearchBar" class="search-bar">
        <VTextField
          v-model="searchQuery"
          placeholder="Поиск..."
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          density="compact"
          hide-details
          clearable
          autofocus
          @keyup.enter="performSearch"
          @click:clear="clearSearch"
        >
          <template #append>
            <VBtn
              icon
              variant="text"
              size="small"
              @click="toggleSearch"
            >
              <VIcon size="16">mdi-close</VIcon>
            </VBtn>
          </template>
        </VTextField>
      </div>
    </VExpandTransition>

    <!-- Прогресс бар -->
    <VProgressLinear
      v-if="toolbarState.loading"
      indeterminate
      color="primary"
      height="2"
      class="progress-bar"
    />
  </VAppBar>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useDisplay } from 'vuetify'
import { useToolbar } from '@/composables/useToolbar'

const { mobile } = useDisplay()
const { toolbarState } = useToolbar()

// Реактивные данные
const scrolled = ref(false)
const showSearchBar = ref(false)
const searchQuery = ref('')
const notificationCount = ref(3) // Пример

// Вычисляемые свойства
const isMobile = computed(() => mobile.value)
const defaultTitle = computed(() => 'Приложение')

// Пример данных пользователя
const user = ref({
  name: 'Иван Иванов',
  avatar: null
})

// Методы
const handleBackClick = () => {
  if (toolbarState.backAction) {
    toolbarState.backAction()
  } else if (toolbarState.backRoute) {
    // router.push(toolbarState.backRoute)
  } else {
    // router.go(-1)
  }
}

const toggleSearch = () => {
  showSearchBar.value = !showSearchBar.value
  if (!showSearchBar.value) {
    searchQuery.value = ''
  }
}

const performSearch = () => {
  if (searchQuery.value.trim()) {
    console.log('Поиск:', searchQuery.value)
    // Логика поиска
  }
}

const clearSearch = () => {
  searchQuery.value = ''
}

const openNotifications = () => {
  console.log('Открыть уведомления')
}

const toggleMobileMenu = () => {
  console.log('Переключить мобильное меню')
}

const openProfile = () => {
  console.log('Открыть профиль')
}

const openSettings = () => {
  console.log('Открыть настройки')
}

const logout = () => {
  console.log('Выйти')
}

// Обработка скролла
const handleScroll = () => {
  scrolled.value = window.scrollY > 10
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})
</script>

<style scoped>
.app-toolbar {
  backdrop-filter: blur(10px);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border-bottom: 1px solid rgba(var(--v-border-color), 0.12);
}

.app-toolbar.scrolled {
  backdrop-filter: blur(20px);
  border-bottom-color: rgba(var(--v-border-color), 0.2);
}

.toolbar-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  height: 100%;
  padding: 0 16px;
}

.toolbar-left {
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1;
  min-width: 0;
}

.toolbar-right {
  display: flex;
  align-items: center;
  gap: 4px;
}

.back-btn {
  flex-shrink: 0;
}

.toolbar-title {
  min-width: 0;
  flex: 1;
}

.title-text {
  font-size: 1.25rem;
  font-weight: 500;
  line-height: 1.2;
  margin: 0;
  color: rgba(var(--v-theme-on-surface), var(--v-high-emphasis-opacity));
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.subtitle-text {
  font-size: 0.875rem;
  color: rgba(var(--v-theme-on-surface), var(--v-medium-emphasis-opacity));
  margin: 0;
  line-height: 1.2;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.toolbar-btn {
  flex-shrink: 0;
}

.user-btn {
  height: 40px !important;
  border-radius: 20px !important;
  padding: 0 12px !important;
  text-transform: none !important;
}

.user-name {
  font-size: 0.875rem;
  font-weight: 500;
  max-width: 120px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.search-bar {
  position: absolute;
  top: 64px;
  left: 0;
  right: 0;
  padding: 12px 16px;
  background: rgba(var(--v-theme-surface), 0.95);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(var(--v-border-color), 0.12);
}

.progress-bar {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
}

/* Мобильные стили */
@media (max-width: 600px) {
  .toolbar-content {
    padding: 0 12px;
  }
  
  .title-text {
    font-size: 1.125rem;
    max-width: 200px;
  }
  
  .toolbar-right {
    gap: 2px;
  }
  
  .search-bar {
    padding: 8px 12px;
  }
}

/* Темная тема */
.v-theme--dark .app-toolbar {
  background: rgba(var(--v-theme-surface), 0.8) !important;
}

.v-theme--dark .search-bar {
  background: rgba(var(--v-theme-surface), 0.9);
}
</style>
