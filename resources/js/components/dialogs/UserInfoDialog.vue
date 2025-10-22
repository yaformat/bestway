<template>
  <span class="user-name-trigger" @click="openDialog">{{ name }}</span>
  <VDialog 
    v-model="showModal"
    scroll-strategy="none"
    max-width="800px" 
    class="user-dialog"
  >
    <VCard>
      <VToolbar color="primary" class="dialog-toolbar">
        <VToolbarTitle>Информация о пользователе</VToolbarTitle>
        <VSpacer />
        <VToolbarItems>
          <!-- <VBtn variant="text" @click="showModal = false">OK</VBtn> -->
          <VBtn icon variant="plain" @click="showModal = false">
            <VIcon color="white" icon="mdi-close" />
          </VBtn>
        </VToolbarItems>
      </VToolbar>
      <VCardText>
        <div v-if="loading" class="loader-container">
          <VProgressCircular indeterminate color="primary" />
        </div>
        <div v-else class="dialog-content d-flex">
          <div class="details">
            <img v-if="photoUrl" v-lazy="photoUrl" class="dialog-img" />
            <div v-if="details">
              <p>Имя: {{ details.full_name }}</p>
              <p>Email: {{ details.email }}</p>
              <p>Организация: {{ details.organization?.name }}</p>
              <p>Язык: {{ details.locale }}</p>
              <p>Дата активности: <FormattedDate :date="details.activity_at" /></p>
            </div>
          </div>
        </div>
      </VCardText>
      <VCardActions>
        <!-- <VBtn color="primary" @click="showModal = false">Закрыть</VBtn> -->
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useUserStore } from '@/stores/userStore';

const props = defineProps({
  id: {
    type: Number,
    required: true,
  },
  name: {
    type: String,
    required: true,
  },
  allowOpen: {
    type: Boolean,
    default: true,
  }
});

const showModal = ref(false);
const details = ref(null);
const loading = ref(true);
const store = useUserStore();

const openDialog = async () => {
  showModal.value = true;
  details.value = await store.fetchSingle(props.id);
  loading.value = false;
};

const photoUrl = computed(() => {
  return details.value ? details.value.photo?.url : '';
});
</script>

<style scoped>
.dialog-toolbar {
  position: sticky;
  top: 0;
  z-index: 1;
}

.user-dialog .dialog-content {
  max-height: 60vh;
  overflow-y: auto;
}

.dialog-img {
  max-width: 100%;
  height: auto;
}

.user-name-trigger {
  cursor:pointer;
  border-bottom:1px solid transparent;
}
.user-name-trigger:hover {
  border-color:inherit;
  transition:all 0.3s;
}
.details {
  display: flex;
  flex-direction: column;
  align-items: center;
}
</style>
