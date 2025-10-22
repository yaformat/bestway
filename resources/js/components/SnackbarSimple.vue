<template>
  <div>
    <VSnackbar v-model="snackbarStore.visible" :color="snackbarStore.type" :timeout="snackbarStore.timeout" top right>
      {{ snackbarStore.message }}
      <template v-slot:actions>
        <VBtn v-if="snackbarStore.type === 'error'" icon color="white" @click="snackbarStore.hideSnackbar">
          <VIcon>mdi-close</VIcon>
        </VBtn>
        <div v-else class="d-flex align-center">
          <VProgressCircular
            :model-value="(snackbarStore.countdown / (snackbarStore.timeout / 1000)) * 100"
            color="white"
            size="24"
            width="2"
          >
            <span style="font-size:12px;">{{ snackbarStore.countdown }}</span>
          </VProgressCircular>
        </div>
      </template>
    </VSnackbar>
  </div>
</template>

<script>
import { useSnackbarStore } from '@/stores/snackbarStore';
import { VBtn, VIcon, VProgressCircular, VSnackbar } from 'vuetify/components';

export default {
  setup() {
    const snackbarStore = useSnackbarStore();

    return {
      snackbarStore,
    };
  },
};
</script>

<style scoped>
.d-flex {
  display: flex;
}
.align-center {
  align-items: center;
}
</style>
