<template>
    <!-- Мобильная сортировка -->
    <IOSBottomSheet
      v-model="mobileState.showModeSelector"
      title="Выберите действие"
      :show-close-button="true"
      max-height="60vh"
      @close="toggleModeSelector"
    >
      <div class="ios-modes-content">
        <VList>
          <VListItem
            prepend-icon="mdi-check-circle"
            title="Выполнение блюд"
            subtitle="Отметить блюда как выполненные"
            @click="setMode(MOBILE_MODES.COMPLETE)"
          />
          
          <VListItem
            prepend-icon="mdi-trashcan-outline"
            title="Удаление блюд/периодов"
            subtitle="Удалить выбранные элементы"
            @click="setMode(MOBILE_MODES.DELETE)"
          />

          <VListItem
            prepend-icon="mdi-sort"
            title="Сортировка блюд"
            subtitle="Изменить порядок блюд"
            @click="setMode(MOBILE_MODES.SORT)"
          />
          
          <VListItem
            prepend-icon="mdi-plus"
            title="Добавить период"
            subtitle="Создать новый период приема пищи"
            @click="addPeriod"
          />
          
          <VListItem
            prepend-icon="mdi-pencil"
            title="Редактировать день"
            subtitle="Изменить параметры периодов и блюд"
            @click="setMode(MOBILE_MODES.EDIT_DAY)"
          />
        </VList>
      </div>
    </IOSBottomSheet>

</template>

<script setup>
import { useMobileMode } from '@/composables/useMobileMode'

const { mobileState, MOBILE_MODES, setMode, toggleModeSelector } = useMobileMode()

const emit = defineEmits(['add-period'])

const addPeriod = () => {
  mobileState.showModeSelector = false
  console.log('Добавление нового периода')
  emit('add-period')
}
</script>
<style scoped>
.ios-modes-content {
  padding: 16px 16px 48px 16px;
}
</style>
