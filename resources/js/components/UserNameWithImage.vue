<template>
  <div class="d-flex align-center">
    <UserAvatar 
      :photo="user.photo" 
      :width="avatarSize" 
      :activity-at="user.activity_at" 
      :show-status="showStatus" 
      :force-online="user.is_online" 
    />
    <div class="text-sm ps-2">
      <UserInfoDialog :id="user.id" :name="displayName" />
    </div>
  </div>
</template>

<script setup>
import { computed, defineProps } from 'vue';

const props = defineProps({
  user: {
    type: Object,
    required: true,
  },
  nameKey: {
    type: String,
    default: 'name',
  },
  avatarSize: {
    type: Number,
    default: 42,
  },
  showStatus: {
    type: Boolean,
    default: true,
  }
});

// Определяем отображаемое имя пользователя
const displayName = computed(() => {
  // Проверяем различные варианты структуры данных
  if (props.user.full_name) {
    return props.user.full_name;
  } else if (props.user[props.nameKey]) {
    return props.user[props.nameKey];
  } else if (props.user.raw && props.user.raw[props.nameKey]) {
    return props.user.raw[props.nameKey];
  }
  
  // Если имя не найдено, возвращаем email или "Пользователь"
  return props.user.email || 'Пользователь';
});
</script>

<style scoped>
.dialog-img {
  width: 100%;
  height: auto;
}
</style>
