<template>
  <!-- Используем VDialog на десктопе или если явно отключен bottom sheet -->
  <VDialog 
    v-if="!shouldUseBottomSheet"
    :model-value="isVisible" 
    @update:model-value="updateVisibility" 
    persistent
    max-width="600px"
    :scrim="true"
    scroll-strategy="none"
    transition="dialog-bottom-transition"
  >
    <VCard>
      <VToolbar color="primary">
        <VToolbarTitle>{{ title }}</VToolbarTitle>
        <VToolbarItems>
            <VBtn
                icon
                variant="plain"
                @click="closeDialog"
            >
                <VIcon
                color="white"
                icon="mdi-close"
                />
            </VBtn>
        </VToolbarItems>
      </VToolbar>
      <VCardText>
        <VForm 
          ref="refForm" 
          v-model="isFormValid"
          @submit.prevent="onSubmit"
        >
          <slot></slot>
        </VForm>
      </VCardText>
      <VCardActions>
        <VSpacer />
        <VBtn color="blue darken-1" text @click="closeDialog">Отмена</VBtn>
        <VBtn 
            color="primary" text @click="submitForm">{{ submitButtonText }}</VBtn>
      </VCardActions>
    </VCard>
  </VDialog>

  <!-- IOSBottomSheet на мобильных устройствах -->
  <IOSBottomSheet
    v-else
    v-model="isBottomSheetVisible"
    :title="title"
    :show-close-button="true"
    :drag-to-close="true"
  >
    <!-- Контент формы -->
    <VForm 
      ref="refForm" 
      v-model="isFormValid"
      @submit.prevent="onSubmit"
      class="pa-4"
    >
      <slot></slot>
    </VForm>

    <!-- Кнопки действий -->
    <template #actions>
      <VRow>
        <VCol cols="12" class="d-flex gap-4 justify-end">
          <!-- <VBtn color="secondary" variant="outlined" @click="closeDialog">Отмена</VBtn> -->
          <VBtn 
            color="primary" 
            @click="submitForm"
            class="ios-apply-btn"
          >
            {{ submitButtonText }}
          </VBtn>
        </VCol>
      </VRow>
    </template>
  </IOSBottomSheet>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { useDisplay } from 'vuetify'

const props = defineProps({
  isVisible: Boolean,
  title: String,
  submitButtonText: String,
  onSubmit: Function,
  useBottomSheet: {
    type: Boolean,
    default: false // null означает "автоопределение"
  }
})

const emit = defineEmits(['update:isVisible'])

const isSubmitting = ref(false)
const isFormValid = ref(false)
const refForm = ref(null)
const isMobile = ref(false)

// Используем Vuetify useDisplay для определения размера экрана
const { mobile, xs, sm } = useDisplay()

// Определяем, нужно ли использовать bottom sheet
const shouldUseBottomSheet = computed(() => {
  // Если явно указано значение useBottomSheet, используем его
  if (props.useBottomSheet) {
    return mobile.value || xs.value || sm.value
  }
  
  return false
})

// Вычисляемое свойство для управления видимостью bottom sheet
const isBottomSheetVisible = computed({
  get: () => props.isVisible,
  set: (value) => {
    emit('update:isVisible', value)
  }
})

const updateVisibility = (value) => {
  emit('update:isVisible', value)
}

const closeDialog = () => {
  emit('update:isVisible', false)
}

const submitForm = () => {
  if (refForm.value) {
    refForm.value?.validate().then(({ valid }) => {
      if (valid) {
        isSubmitting.value = true;
        props.onSubmit()
      } else {
        console.error('Form validation failed')
      }
    }).catch(error => {
      console.error('Validation error:', error)
    })
  } else {
    console.error('Form reference is not defined')
  }
}

// Сбрасываем состояние формы при закрытии
watch(() => props.isVisible, (newVal) => {
  if (!newVal) {
    isSubmitting.value = false
    // Если нужно сбросить валидацию формы
    if (refForm.value) {
      refForm.value.resetValidation()
    }
  }
})
</script>

<style scoped>
/* Дополнительные стили для bottom sheet */
.ios-apply-btn {
  min-width: 120px;
}
</style>
