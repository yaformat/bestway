<template>
  <div v-if="userDataLoaded" class="user-profile">
      <div class="d-flex  cursor-pointer align-center" @click="toggleMenu">
        <UserAvatar 
          :photo="user.photo" 
          :activity-at="user.activity_at" 
          :show-status="true" 
          :force-online="user.is_online" 
        />
        <div class="user-name d-none d-md-block ms-2">{{ user.initial_name }}</div>
      </div>
      <!-- SECTION Menu -->
      <VMenu
        v-model="menu"
        activator="parent"
        width="230"
        location="bottom end"
        offset="14px"
      >
        <VList>
          <!-- 👉 User Avatar & Name -->
          <VListItem>
            <template #prepend>
              <VListItemAction start>
                <VBadge
                  dot
                  location="bottom right"
                  offset-x="3"
                  offset-y="3"
                  color="success"
                >
                  <UserAvatar :photo="user.photo" :width="42" />
                </VBadge>
              </VListItemAction>
            </template>

            <VListItemTitle class="font-weight-semibold">
              {{ user.initial_name }}
            </VListItemTitle>
            <VListItemSubtitle v-if="user.organization && user.organization.name">{{ user.organization.name }}</VListItemSubtitle>
          </VListItem>

          <VDivider class="my-2" />

          <!-- 👉 account -->
          <VListItem 
            link
            :to="{name: 'profile', params: {}, hash: '#account'}"
            :class="{ 'v-list-item--active': false }"
          >
            <template #prepend>
              <VIcon
                class="me-2"
                icon="tabler-user"
                size="22"
              />
            </template>

            <VListItemTitle>Профиль</VListItemTitle>
          </VListItem>

          <!-- 👉 Settings -->
          <VListItem 
            link
            :to="{name: 'profile', params: {}, hash: '#settings'}"
            :class="{ 'v-list-item--active': false }"
          >
            <template #prepend>
              <VIcon
                class="me-2"
                icon="tabler-settings"
                size="22"
              />
            </template>

            <VListItemTitle>Настройки</VListItemTitle>
          </VListItem>

          <!-- Divider -->
          <VDivider class="my-2" />

          <!-- 👉 Logout -->
          <VListItem to="/login">
            <template #prepend>
              <VIcon
                class="me-2"
                icon="tabler-logout"
                size="22"
              />
            </template>

            <VListItemTitle>Выход</VListItemTitle>
          </VListItem>
        </VList>
      </VMenu>
      <!-- !SECTION -->
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useUserProfileStore } from '@/stores/userProfileStore';

const menu = ref(false);
const userDataLoaded = ref(false);
const user = ref({});
const userProfileStore = useUserProfileStore();

watch(() => userProfileStore.userDataLoaded, (newVal) => {
  userDataLoaded.value = newVal;
});

watch(() => userProfileStore.user, (data) => {
  user.value = data;
});

const toggleMenu = () => {
  return;
  menu.value = !menu.value;
};

onMounted(() => {
  userProfileStore.fetchUserProfile();
});
</script>

<style scoped>
.user-profile {
  position:relative;
}
.user-name {
  display: none;
  max-width:120px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.online-badge {
  position:absolute;
  bottom:10px;
  right:10px;
}
@media (min-width: 768px) {
  .user-name {
    display: inline-block;
  }
}
</style>
