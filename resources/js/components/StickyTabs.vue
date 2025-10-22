<template>
  <div ref="tabsContainerRef" :class="['tabs-container', { sticky: isSticky }]">
    <VTabs :model-value="modelValue" @update:model-value="updateModelValue">
      <VTab v-for="tabItem in tabs" :key="tabItem.key" :value="tabItem.key">
        <VIcon start :size="24" :icon="tabItem.icon" />
        <span>{{ tabItem.title }}</span>
      </VTab>
    </VTabs>
    <VDivider />
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  modelValue: {
    type: String,
    required: true,
  },
  tabs: {
    type: Array,
    required: true,
  },
})

const emit = defineEmits(['update:modelValue'])

const isSticky = ref(false)
const tabsContainerRef = ref(null)
const tabsOffsetTop = ref(0)

// Функция throttle для ограничения частоты вызовов
const throttle = (fn, delay) => {
  let lastCall = 0
  return function(...args) {
    const now = new Date().getTime()
    if (now - lastCall < delay) return
    lastCall = now
    return fn(...args)
  }
}

// Обработчик прокрутки с применением throttle
const handleScroll = throttle(() => {
  isSticky.value = window.scrollY > tabsOffsetTop.value
}, 16) // ~60fps (1000ms / 60 ≈ 16ms)

onMounted(() => {
  // Кешируем ссылку на DOM-элемент и его offsetTop
  if (tabsContainerRef.value) {
    tabsOffsetTop.value = tabsContainerRef.value.offsetTop
  }
  
  // Добавляем опцию passive: true для улучшения производительности
  window.addEventListener('scroll', handleScroll, { passive: true })
})

onBeforeUnmount(() => {
  window.removeEventListener('scroll', handleScroll)
})

const updateModelValue = (newValue) => {
  emit('update:modelValue', newValue)
}
</script>

<style lang="scss">
.tabs-container {
  background: rgb(var(--v-theme-background));
  position: relative;
  top: 0;
  z-index: 10;
  transition: top 0.5s ease, box-shadow 0.3s ease;
}

.sticky {
  position: fixed;
  top: 0;
  width: 100%;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  transition: top 0.5s ease, box-shadow 0.3s ease;
}
</style>
