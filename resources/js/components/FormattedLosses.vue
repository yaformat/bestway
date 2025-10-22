<script setup>
const props = defineProps({
  losses: {
    type: Object,
    required: true,
  }
})

const losses = computed(() => {
  return props.losses.filter(losse => losse.value !== 0)
})
</script>

<template>

    <span v-for="loss in losses" :key="loss.id" class="losse-item">
      {{ loss.name }}: <span>{{ loss.value }}</span>
    </span>

</template>

<style>
.losse-item {
  font-size: 12px;
  margin-right:5px;
  display:inline-block;
  white-space:nowrap;
}
.losse-item span {font-weight:500;}
</style>