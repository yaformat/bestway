// composables/useUrlSync.js
import { ref, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

export function useUrlSync(defaultOptions) {
  const route = useRoute()
  const router = useRouter()

  const options = ref({
    ...defaultOptions,
  })

  const initialized = ref(false)

  const initializeOptions = () => {
    Object.keys(route.query).forEach(key => {
      if (options.value.hasOwnProperty(key)) {
        options.value[key] = route.query[key]
      }
    })
    initialized.value = true
  }

  const updateRoute = () => {
    if (initialized.value) {
      router.replace({
        query: {
          ...route.query,
          ...options.value,
        }
      })
    }
  }

  watch(options, updateRoute, { deep: true })

  onMounted(() => {
    initializeOptions()
  })

  return {
    options,
    initialized,
  }
}
