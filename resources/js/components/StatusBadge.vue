<!-- components/StatusBadge.vue -->
<template>
  <span
    class="text-sm"
    :style="{
      backgroundColor: status.bgcolor,
      color: status.textcolor,
      borderColor: status.bordercolor,
      borderWidth: '0px',
      borderStyle: 'solid',
      padding: '4px 8px',
      borderRadius: '4px',
    }"
  >
    {{ status.name }}
  </span>
</template>

<script setup>
const props = defineProps({
  status: {
    type: Object,
    required: true,
  },
})
</script>
