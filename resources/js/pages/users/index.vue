<script setup>
import UsersTable from '@/components/tables/UsersTable.vue'

import router from '@/router'

const route = useRoute()

</script>

<template>
  <section>
    <VCard title="Пользователи">
      <UsersTable />
    </VCard>
  </section>
</template>

<style lang="scss">
</style>

