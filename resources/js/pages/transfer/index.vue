<script setup>
import router from '@/router'

const route = useRoute()

</script>

<template>
  <section>
    <VCard title="Трансферы">
      <TransfersTable :is-active="true" />
    </VCard>
  </section>
</template>

<style lang="scss">
</style>

