<!-- components/FormattedDate.vue -->
<template>
  <span class="text-sm">{{ formattedDate }}</span>
</template>

<script setup>
import { computed } from 'vue'
import { format } from 'date-fns'

const props = defineProps({
  date: {
    type: String,
    required: true,
  },
})

const formattedDate = computed(() => {
  return props.date ? format(new Date(props.date), 'dd.MM.yy HH:mm') : '--'
})
</script>
