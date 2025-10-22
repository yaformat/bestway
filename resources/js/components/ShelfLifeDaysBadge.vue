<!-- components/ShelfLifeDaysBadge.vue -->
<template>
  <span v-if="props.days" class="text-sm">{{ props.days }} дн.<span title="Обязательное внесение при закупке" style="color:red;" v-if="props.required"> *</span></span>
</template>

<script setup>
const props = defineProps({
  days: {
    type: Number,
    required: true,
  },
  required: {
    type: Boolean,
    required: false,
  },
})
</script>
