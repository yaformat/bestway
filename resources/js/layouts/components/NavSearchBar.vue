<script setup>
import axiosIns from '@/plugins/axios';
import { ref, watchEffect, computed } from 'vue'
import { useRouter } from 'vue-router'

const isAppSearchBarVisible = ref(false)
const searchQuery = ref('')
const searchResult = ref({})
const router = useRouter()

// Fetch search result API
watchEffect(() => {
  if (searchQuery.value.length >= 3) {
    axiosIns.get('/search', { params: { query: searchQuery.value } }).then(response => {
      searchResult.value = response.data || {}
    })
  } else {
    searchResult.value = {}
  }
})

const redirectToSuggestedOrSearchedPage = selected => {
  router.push(selected.url)
  isAppSearchBarVisible.value = false
  searchQuery.value = ''
}

// Computed property to check if all groups are empty
const allGroupsEmpty = computed(() => {
  return Object.values(searchResult.value).every(group => group.length === 0)
})
</script>

<template>
  <div
    class="d-flex align-center cursor-pointer"
    v-bind="$attrs"
    style="user-select: none;"
    @click="isAppSearchBarVisible = !isAppSearchBarVisible"
  >
    <IconBtn class="me-1">
      <VIcon icon="mdi-magnify" />
    </IconBtn>
    <span v-if="appContentLayoutNav === 'vertical'" class="d-none d-md-flex align-center text-disabled">
      <span class="me-3">Search</span>
      <span class="meta-key">&#8984;K</span>
    </span>
  </div>

  <VDialog
    max-width="600"
    :model-value="isAppSearchBarVisible"
    :height="$vuetify.display.smAndUp ? '550' : '100%'"
    :fullscreen="$vuetify.display.width < 600"
    class="app-bar-search-dialog"
    @update:model-value="val => isAppSearchBarVisible = val"
    @keyup.esc="isAppSearchBarVisible = false"
  >
    <VCard height="100%" width="100%" class="position-relative">
      <VCardText class="pt-1" style="min-block-size: 65px; position: sticky; top: 0; background: white; z-index: 1;">
        <VTextField
          v-model="searchQuery"
          autofocus
          density="comfortable"
          variant="plain"
          class="app-bar-autocomplete-box"
          @keyup.esc="isAppSearchBarVisible = false"
        >
          <template #prepend-inner>
            <div class="d-flex align-center text-high-emphasis me-1">
              <VIcon size="22" class="mt-1" icon="mdi-magnify" />
            </div>
          </template>
          <template #append-inner>
            <div class="d-flex align-center">
              <div class="text-base text-disabled cursor-pointer me-1" @click="isAppSearchBarVisible = false">
                [esc]
              </div>
              <IconBtn size="x-small" @click="isAppSearchBarVisible = false">
                <VIcon icon="mdi-close" />
              </IconBtn>
            </div>
          </template>
        </VTextField>
      </VCardText>

      <VDivider />

      <PerfectScrollbar :options="{ wheelPropagation: false, suppressScrollX: true }" class="h-100">
        <div class="search-results">
          <template v-for="(group, groupName) in searchResult" :key="groupName">
            <h3 v-if="group.length">{{ groupName }}</h3>
            <VList v-show="searchQuery.length && !!group.length" density="compact" class="app-bar-search-list">
              <VListItem v-for="item in group" :key="item.id" link @click="redirectToSuggestedOrSearchedPage(item)">
                <template #prepend>
                  <VImg v-if="item.photo" :src="item.photo.url" class="me-3" :width="50" />
                </template>
                <template #append>
                  <VIcon size="20" icon="mdi-subdirectory-arrow-left" class="enter-icon text-disabled" />
                </template>
                <VListItemTitle>{{ item.name }}</VListItemTitle>
              </VListItem>
            </VList>
          </template>

          <div v-show="allGroupsEmpty && searchQuery.length" class="h-100">
            <VCardText class="h-100">
              <div class="app-bar-search-suggestions d-flex flex-column align-center justify-center text-high-emphasis h-100">
                <VIcon size="75" icon="mdi-file-remove-outline" />
                <div class="d-flex align-center flex-wrap justify-center gap-2 text-h6 my-3">
                  <span>No Result For </span>
                  <span>"{{ searchQuery }}"</span>
                </div>
              </div>
            </VCardText>
          </div>
        </div>
      </PerfectScrollbar>
    </VCard>
  </VDialog>
</template>

<style lang="scss" scoped>
.meta-key {
  border: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-radius: 6px;
  block-size: 1.5625rem;
  line-height: 1.3125rem;
  padding-block: 0.125rem;
  padding-inline: 0.25rem;
}

.search-results {
  overflow-y: auto;
  max-height: calc(100% - 16px); /* Adjust based on the height of the search input */
}
</style>
