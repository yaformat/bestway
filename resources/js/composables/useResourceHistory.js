// composables/useResourceHistory.js
import { ref, nextTick } from 'vue';

export function useResourceHistory(actionType, getActionId, getStockId) {
  const selectedResourceForHistory = ref(null);
  const resourceDialog = ref(null);

  const openResourceHistory = (item) => {
    console.log('Открываем историю для ресурса:', item);
    console.log('actionType:', actionType);
    console.log('actionId:', getActionId());
    console.log('stockId:', getStockId());
    
    selectedResourceForHistory.value = {
      id: item.id,
      name: item.name,
      actionType: actionType,
      actionId: getActionId(),
      stockId: getStockId()
    };
    
    nextTick(() => {
      if (resourceDialog.value) {
        console.log('Вызываем openDialog');
        resourceDialog.value.openDialog();
      } else {
        console.error('resourceDialog.value не найден');
      }
    });
  };

  return {
    selectedResourceForHistory,
    resourceDialog,
    openResourceHistory
  };
}
