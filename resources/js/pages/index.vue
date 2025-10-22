<template>
  <div class="dashboard-page">
    <!-- User Profile Section -->
    <VCard class="mb-6">
      <VCardText class="pa-6">
        <div class="d-flex align-center">
          <UserAvatar 
            :photo="user.photo" 
            :activity-at="user.activity_at" 
            :show-status="true" 
            :force-online="user.is_online"
            :width="80"
            class="me-4"
          />
          <div>
            <h2 class="text-h4 font-weight-bold mb-1">
              Добро пожаловать, {{ user.initial_name }}!
            </h2>
            <p class="text-body-1 text-medium-emphasis mb-0">
              <template v-if="user.organization && user.organization.name">
                {{ user.organization.name }}
              </template>
              <template v-else>
                Управляйте вашим рестораном эффективно
              </template>
            </p>
          </div>
        </div>
      </VCardText>
    </VCard>

    <!-- Main Sections -->
    <div class="sections-grid">
      <VRow>
        <VCol
          v-for="section in mainSections"
          :key="section.name"
          cols="12"
          sm="6"
          md="4"
          lg="3"
        >
          <VCard
            class="section-card"
            :class="{ 'section-card--disabled': section.disabled }"
            :to="section.disabled ? undefined : section.to"
            hover
          >
            <VCardText class="pa-6 text-center">
              <div class="section-icon mb-4">
                <VAvatar
                  :color="section.color"
                  size="64"
                  variant="tonal"
                >
                  <VIcon
                    :icon="section.icon"
                    size="32"
                  />
                </VAvatar>
              </div>
              
              <h3 class="text-h6 font-weight-semibold mb-2">
                {{ section.title }}
              </h3>
              
              <p class="text-body-2 text-medium-emphasis mb-0">
                {{ section.description }}
              </p>
              
              <VChip
                v-if="section.badge"
                :color="section.badgeColor"
                size="small"
                class="mt-3"
              >
                {{ section.badge }}
              </VChip>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { useUserProfileStore } from '@/stores/userProfileStore'
import UserAvatar from '@/components/UserAvatar.vue'

const userProfileStore = useUserProfileStore()

const user = ref({})
const userDataLoaded = ref(false)

// Основные разделы приложения (соответствуют вашей навигации)
const mainSections = ref([
  {
    name: 'hotels',
    title: 'Отели',
    description: 'Просмотр и управление отелями',
    icon: 'mdi-hotel',
    color: 'primary',
    to: { path: '/hotels', name: 'hotel' },
    disabled: false,
  },
  {
    name: 'tours',
    title: 'Туры',
    description: 'Организация туров и путешествий',
    icon: 'mdi-palm-tree',
    color: 'success',
    to: { path: '/tours', name: 'tour' },
    disabled: false,
  },
  {
    name: 'excursions',
    title: 'Экскурсии',
    description: 'Экскурсионные программы и маршруты',
    icon: 'mdi-map-marker-outline',
    color: 'info',
    to: { path: '/excursions', name: 'excursion' },
    disabled: false,
  },
  {
    name: 'transfers',
    title: 'Трансферы',
    description: 'Перевозка туристов и гостей',
    icon: 'mdi-car-outline',
    color: 'warning',
    to: { path: '/transfers', name: 'transfer' },
    disabled: false,
  },
  {
    name: 'directions',
    title: 'Направления',
    description: 'Географические направления и регионы',
    icon: 'mdi-earth',
    color: 'secondary',
    to: { path: '/directions', name: 'direction' },
    disabled: false,
  },
])


// Watchers
watch(() => userProfileStore.userDataLoaded, (newVal) => {
  userDataLoaded.value = newVal
})

watch(() => userProfileStore.user, (data) => {
  user.value = data
})

onMounted(() => {
  userProfileStore.fetchUserProfile()
})
</script>

<style scoped>
.dashboard-page {
  padding: 24px;
}

.sections-grid {
  margin-top: 24px;
}

.section-card {
  height: 100%;
  transition: all 0.3s ease;
  cursor: pointer;
}

.section-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.section-card--disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.section-card--disabled:hover {
  transform: none;
  box-shadow: none;
}

.section-icon {
  display: flex;
  justify-content: center;
}

@media (max-width: 600px) {
  .dashboard-page {
    padding: 16px;
  }
  
  .sections-grid .v-col {
    padding: 8px;
  }
}
</style>
