// src/composables/paginationMeta.js
import { computed } from 'vue'

export function usePaginationMeta() {
  return function paginationMeta(options, total) {
    const start = computed(() => {
      if (!options || !total) return 0
      return (options.page - 1) * options.itemsPerPage + 1
    })
    const end = computed(() => {
      if (!options || !total) return 0
      return Math.min(options.page * options.itemsPerPage, total)
    })
    return `${start.value}-${end.value} из ${total || 0}`
  }
}
