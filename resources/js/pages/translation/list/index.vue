<script setup>
import {useTranslationListStore} from '@/views/translation/useTranslationListStore'
import { VDataTableServer } from 'vuetify/components'
import router from "@/router";
import {paginationMeta} from "@/@fake-db/utils";
const translationListStore = useTranslationListStore()
const searchQuery = ref('')
const dir = ref([])
const totalPage = ref(1)
const totalDir = ref(0)

const options = ref({
  page: 1,
  itemsPerPage: 50,
  sortBy: [],
  groupBy: [],
  search: '',
})

// Headers
const headers = [
  {
    title: 'ID',
    key: 'id',
  },
  {
    title: 'Название',
    key: 'name',
  },
  {
    title: 'Ключ',
    key: 'key_name',
  },
  {
    title: 'Кол-во фраз',
    key: 'keys_count',
  },
  {
    title: 'Действия',
    key: 'actions',
    sortable: false,
  },
]

//Fetch Translation
const fetchDir = () => {
  translationListStore.fetchDirectories({
    q: searchQuery.value,
    options: options.value,
  }).then(response => {
    dir.value = response.data.items
    totalPage.value = 10
    totalDir.value = response.data.total
    options.value.page = response.data.page
    console.log(response.data.items)
  }).catch(error => {
    console.error(error)
  })
}

//watchEffect(fetchDir)
onMounted(fetchDir)

const deleteTranslation = async id => {
  await translationListStore.deleteTranslation(id)
  router.replace('/translation/list');
  // refetch translation
  fetchDir()
}
</script>

<template>
  <section>
    <VCard>
      <VCardText class="d-flex justify-space-between flex-wrap gap-4">
        <VBtn
            class="order-sm-2 order-1"
            :to="{ name: 'translation-add', params: {  } }"
        >
          Создать группу переводов
        </VBtn>
      </VCardText>

      <!-- SECTION datatable -->
      <VDataTableServer
          v-model:items-per-page="options.itemsPerPage"
          v-model:page="options.page"
          :items="dir"
          :items-length="totalDir"
          :headers="headers"

          class="text-no-wrap rounded-0"
          @update:options="options = $event"
      >
        <!-- ID -->
        <template #item.id="{ item }">
          <span class="text-sm">
                {{ item.id }}
          </span>
        </template>
        <template #item.name="{ item }">
            {{ item.name }}
        </template>
        <template #item.key_name="{ item }">
            {{ item.key_name }}
        </template>
        <template #item.keys_count="{ item }">
            {{ item.keys_count }}
        </template>
        <!-- Actions -->
        <template #item.actions="{ item }">
          <VBtn
              :to="{ name: 'translation-edit-id', params: { id: item.id } }"
              icon
              variant="text"
              size="small"
              color="medium-emphasis"
          >
            <VIcon icon="mdi-pencil-outline"/>
          </VBtn>
        </template>

        <template #bottom>
          <VDivider />
          <div class="d-flex gap-x-6 flex-wrap justify-end pa-2">
            <div class="d-flex align-center gap-x-2 text-sm">
              Rows Per Page:
              <VSelect
                  v-model="options.itemsPerPage"
                  variant="plain"
                  class="per-page-select text-high-emphasis"
                  density="compact"
                  :items="[10, 20, 50, 100]"
              />
            </div>
            <div class="d-flex text-sm align-center text-high-emphasis">
              {{ paginationMeta(options, totalDir) }}
            </div>
            <div class="d-flex gap-x-2 align-center">

              <VBtn
                  class="flip-in-rtl"
                  icon="mdi-chevron-left"
                  variant="text"
                  density="comfortable"
                  color="default"
                  :disabled="options.page <= 1"
                  @click="options.page <= 1 ? options.page = 1 : options.page--"
              />
              <VBtn
                  class="flip-in-rtl"
                  icon="mdi-chevron-right"
                  density="comfortable"
                  variant="text"
                  color="default"
                  :disabled="options.page >= Math.ceil(totalDir / options.itemsPerPage)"
                  @click="options.page >= Math.ceil(totalDir / options.itemsPerPage) ? options.page = Math.ceil(totalDir / options.itemsPerPage) : options.page++ "
              />

            </div>
          </div>
        </template>

      </VDataTableServer>
      <!-- SECTION -->
    </VCard>

  </section>
</template>


<style lang="scss">

</style>

