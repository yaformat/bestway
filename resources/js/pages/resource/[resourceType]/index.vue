<script setup>
import ResourceTable from '@/components/tables/ResourceTable.vue'
import ResourceIngredientTable from '@/components/tables/ResourceIngredientTable.vue'
import ResourceCategoriesTable from '@/components/tables/ResourceCategoriesTable.vue'

import router from '@/router'
import { ref, watch } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

const resourceType = ref(route.params.resourceType || 'misc') // Get resourceType from route params with a default
document.title = `РЕСУРСЫ: ${resourceType.value}` // Update document title

const tabs = [
  {
    icon: 'mdi-cube-outline',
    title: 'Список позиций',
    key: 'resource',
  },
  {
    icon: 'mdi-bookmark-outline',
    title: 'Категории',
    key: 'categories',
  },
]

const tab = ref(route.hash.replace('#', '') || tabs[0].key) // Инициализация с ключом первой вкладки или хеша

watch(tab, (newTab) => {
  router.replace({ hash: '#' + newTab })
})

// Watcher for resourceType route parameter
watch(() => route.params.resourceType, (newResourceType) => {
  resourceType.value = newResourceType || 'misc';
  document.title = `РЕСУРСЫ: ${resourceType.value}`; // Update document title on change
});
</script>

<template>
  <section>
    <VCard>
      <VCardTitle>
        <div class="d-flex justify-space-between align-center w-100">
          <span>РЕСУРСЫ: {{ resourceType }}</span>
          <VBtn size="small" class="ml-2" :to="{ name: 'resource-resourceType-add', params: { resourceType: resourceType } }">Создать {{ resourceType }}</VBtn>
        </div>
      </VCardTitle>

      <StickyTabs :model-value="tab" @update:model-value="newTab => tab = newTab" :tabs="tabs" />
      <VWindow v-model="tab" class="disable-tab-transition" :touch="false">
        <VWindowItem key="resource" value="resource">
          <component :is="resourceType === 'ingredient' || resourceType === 'semi_finished' ? ResourceIngredientTable : ResourceTable" :is-active="tab === 'resource'" :resource-type="resourceType" />
        </VWindowItem>

        <VWindowItem key="categories" value="categories">
          <ResourceCategoriesTable :is-active="tab === 'categories'" :resource-type="resourceType" />
        </VWindowItem>
      </VWindow>
    </VCard>
  </section>
</template>
