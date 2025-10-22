<script setup>
import router from '@/router'

const route = useRoute()


const tabs = [
    {
        icon: 'mdi-hotel',
        title: 'Отели',
        key: 'hotels',
    },
    {
        icon: 'mdi-palm-tree',
        title: 'Курорты',
        key: 'resorts',
    },
];

const tab = ref(route.hash.replace('#', '') || tabs[0].key) // Инициализация с ключом первой вкладки или хеша

watch(tab, (newTab) => {
  router.replace({ hash: '#' + newTab })
})


</script>

<template>
  <section>
    <VCard title="Отели">
      <StickyTabs :model-value="tab" @update:model-value="newTab => tab = newTab" :tabs="tabs" />

      <VWindow v-model="tab" class="disable-tab-transition" :touch="false">
        <VWindowItem key="hotels" value="hotels">
          <HotelsTable :is-active="tab === 'hotels'" />
        </VWindowItem>

        <VWindowItem key="resorts" value="resorts">
          <ResortsTable :is-active="tab === 'resorts'" />
        </VWindowItem>

      </VWindow>
    </VCard>
  </section>
</template>

<style lang="scss">
</style>

