<template>
  <div>
    <!-- Хлебные крошки и кнопка назад -->
    <div class="d-flex align-center mb-2">
      <VBtn v-if="stack.length" icon @click="goBack" variant="text">
        <VIcon>mdi-arrow-left</VIcon>
      </VBtn>
      <span class="text-subtitle-1 ml-2">{{ currentPath }}</span>
    </div>

    <!-- Список текущих категорий -->
    <VList density="compact" nav>
      <VListItem
        v-for="category in currentCategories"
        :key="category.id"
        @click="handleCategoryClick(category)"
        class="cursor-pointer"
      >
        <VListItemTitle>{{ category.name }}</VListItemTitle>

        <template #append v-if="hasChildren(category)">
          <VIcon>mdi-chevron-right</VIcon>
        </template>
      </VListItem>
    </VList>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useGlobalDataStore } from '@/stores/globalDataStore'

const props = defineProps({
  type: String, // строка, например "ingredient", "package"
})

const emit = defineEmits(['categorySelected'])

const globalStore = useGlobalDataStore()

const stack = ref([])

const allCategories = computed(() =>
  globalStore.resource_categories.filter(cat => cat.type === props.type)
)

const currentParentId = computed(() =>
  stack.value.length ? stack.value[stack.value.length - 1].id : null
)

const parentIdsSet = computed(() => new Set(allCategories.value.map(cat => cat.id)))

const currentCategories = computed(() => {
  if (stack.value.length === 0) {
    return allCategories.value.filter(cat => !parentIdsSet.value.has(cat.parent_id))
  } else {
    const currentId = stack.value[stack.value.length - 1].id
    return allCategories.value.filter(cat => cat.parent_id === currentId)
  }
})

const currentPath = computed(() =>
  stack.value.length ? stack.value.map(c => c.name).join(' > ') : 'Выберите категорию'
)

function hasChildren(category) {
  return allCategories.value.some(cat => cat.parent_id === category.id)
}

function handleCategoryClick(category) {
  if (hasChildren(category)) {
    stack.value.push(category)
  } else {
    emit('categorySelected', category.id)
  }
}

function goBack() {
  stack.value.pop()
}

// Сброс при смене типа
watch(() => props.type, () => {
  stack.value = []
  console.log(allCategories.value)
})
</script>

<style scoped>
.cursor-pointer {
  cursor: pointer;
}
</style>
