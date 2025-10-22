<script setup>
import SuppliersTable from '@/components/tables/SuppliersTable.vue'
import SupplierLocationsTable from '@/components/tables/SupplierLocationsTable.vue'

import router from '@/router'

const route = useRoute()

const tabs = [
  {
    icon: 'mdi-truck',
    title: 'Поставщики',
    key: 'suppliers',
  },
  {
    icon: 'mdi-map',
    title: 'Локации',
    key: 'supplier_locations',
  },
]

const tab = ref(route.hash.replace('#', '') || tabs[0].key) // Инициализация с ключом первой вкладки или хеша

watch(tab, (newTab) => {
  router.replace({ hash: '#' + newTab })
})
</script>

<template>
  <section>
    <VCard title="Поставщики">
      <StickyTabs :model-value="tab" @update:model-value="newTab => tab = newTab" :tabs="tabs" />

      <VWindow v-model="tab" class="disable-tab-transition" :touch="false">
        <VWindowItem key="suppliers" value="suppliers">
          <SuppliersTable :is-active="tab === 'suppliers'" />
        </VWindowItem>

        <VWindowItem key="supplier_locations" value="supplier_locations">
          <SupplierLocationsTable :is-active="tab === 'supplier_locations'" />
        </VWindowItem>

      </VWindow>
    </VCard>
  </section>
</template>

<style lang="scss">
</style>

