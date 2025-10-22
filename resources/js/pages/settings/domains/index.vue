<script setup>
import router from '@/router'

const route = useRoute()

</script>

<template>
  <section>
    <VCard title="Домены">
      <DomainsTable :is-active="true" />
    </VCard>
  </section>
</template>

<style lang="scss">
</style>

