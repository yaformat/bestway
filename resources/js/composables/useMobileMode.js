// composables/useMobileMode.js
import { reactive } from 'vue'

export const MOBILE_MODES = {
  VIEW: 'view',
  SORT: 'sort',
  DELETE: 'delete',
  COMPLETE: 'complete',
  EDIT_DAY: 'edit_day'
}

const mobileState = reactive({
  currentMode: MOBILE_MODES.VIEW,
  showModeSelector: false,
  selectedDishes: new Set(),
  selectedPeriods: new Set()
})

export const useMobileMode = () => {
  const setMode = (mode) => {
    mobileState.currentMode = mode
    mobileState.showModeSelector = false
    // Сбрасываем выделения при смене режима
    mobileState.selectedDishes.clear()
    mobileState.selectedPeriods.clear()
  }

  const toggleModeSelector = () => {
    mobileState.showModeSelector = !mobileState.showModeSelector
  }

  const toggleDishSelection = (dishId) => {
    if (mobileState.selectedDishes.has(dishId)) {
      mobileState.selectedDishes.delete(dishId)
    } else {
      mobileState.selectedDishes.add(dishId)
    }
  }

  const togglePeriodSelection = (periodId, incompleteDishes = []) => {
    if (mobileState.selectedPeriods.has(periodId)) {
      // Убираем период и все его блюда из выделения
      mobileState.selectedPeriods.delete(periodId)
      incompleteDishes.forEach(dish => {
        mobileState.selectedDishes.delete(dish.id)
      })
    } else {
      // Добавляем период и все его невыполненные блюда
      mobileState.selectedPeriods.add(periodId)
      incompleteDishes.forEach(dish => {
        mobileState.selectedDishes.add(dish.id)
      })
    }
  }

  const isDishSelected = (dishId) => {
    return mobileState.selectedDishes.has(dishId)
  }

  const isPeriodSelected = (periodId) => {
    return mobileState.selectedPeriods.has(periodId)
  }

  const resetSelections = () => {
    mobileState.selectedDishes.clear()
    mobileState.selectedPeriods.clear()
  }

  const exitMode = () => {
    setMode(MOBILE_MODES.VIEW)
  }

  return {
    mobileState,
    MOBILE_MODES,
    setMode,
    toggleModeSelector,
    toggleDishSelection,
    togglePeriodSelection,
    isDishSelected,
    isPeriodSelected,
    resetSelections,
    exitMode
  }
}
