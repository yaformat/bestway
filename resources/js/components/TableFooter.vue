<!-- src/components/TableFooter.vue -->
<template>
  <div>
    <VDivider />
    <div class="d-flex gap-x-6 flex-wrap justify-end pa-2">
      <div class="d-flex align-center gap-x-2 text-sm">
        на странице:
        <VSelect v-model="options.itemsPerPage" variant="plain" class="per-page-select text-high-emphasis" density="compact" :items="[10, 20, 50, 100]" />
      </div>
      <div class="d-flex text-sm align-center text-high-emphasis">
        {{ paginationMeta(options, total) }}
      </div>
      <div class="d-flex gap-x-2 align-center">
        <template v-if="showPages">
          <VBtn icon size="small" density="comfortable" color="secondary" :disabled="options.page === 1" @click="goToPage(1)">1</VBtn>
          <span v-if="showLeftEllipsis">...</span>
          <VBtn icon size="small" density="comfortable" color="secondary" v-for="page in pagesToShow" :key="page" :class="{ 'active': options.page === page }" @click="goToPage(page)">
            {{ page }}
          </VBtn>
          <span v-if="showRightEllipsis">...</span>
          <VBtn icon size="small" density="comfortable" color="secondary" :disabled="options.page === totalPages" @click="goToPage(totalPages)">{{ totalPages }}</VBtn>
        </template>
        <template v-else>
          <VBtn class="flip-in-rtl" icon="mdi-chevron-left" variant="text" density="comfortable" color="secondary" :disabled="options.page <= 1" @click="prevPage" />
          <VBtn class="flip-in-rtl" icon="mdi-chevron-right" density="comfortable" variant="text" color="secondary" :disabled="options.page >= totalPages" @click="nextPage" />
        </template>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, watch } from 'vue'
import { usePaginationMeta } from '@/composables/paginationMeta'
import { useWindowSize } from '@vueuse/core'

const props = defineProps({
  options: {
    type: Object,
    required: true
  },
  total: {
    type: Number,
    default: 0,
  }
})

const paginationMeta = usePaginationMeta()

//const { width } = useWindowSize()
//const showPages = computed(() => width.value >= 1024)
const showPages = false

const totalPages = computed(() => {
  if (!props.options || !props.total) return 1
  return Math.ceil(props.total / props.options.itemsPerPage)
})

const pagesToShow = computed(() => {
  const currentPage = props.options.page
  const total = totalPages.value
  const delta = 2
  const range = []

  for (let i = Math.max(2, currentPage - delta); i <= Math.min(total - 1, currentPage + delta); i++) {
    range.push(i)
  }

  return range
})

const showLeftEllipsis = computed(() => {
  return props.options.page > 3
})

const showRightEllipsis = computed(() => {
  return props.options.page < totalPages.value - 2
})

const goToPage = (page) => {
  props.options.page = page
}

const prevPage = () => {
  if (props.options.page > 1) {
    props.options.page--
  }
}

const nextPage = () => {
  if (props.options.page < totalPages.value) {
    props.options.page++
  }
}

// Watch for changes in options to ensure they are updated correctly
watch(() => props.options, (newOptions) => {
  console.log('Options updated:', newOptions)
}, { deep: true })
</script>

<style scoped>
.active {
  font-weight: bold;
}
</style>
