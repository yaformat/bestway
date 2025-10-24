<template>
  <section>
    <VCard title="Ваш профиль">
        <StickyTabs :model-value="tab" @update:model-value="newTab => tab = newTab" :tabs="tabs" />

        <VWindow v-model="tab" class="disable-tab-transition" :touch="false">
            <VWindowItem key="account" value="account">
            <div class="personal-info">
                <VRow>
                <VCol cols="12" md="4" class="text-center">
                    <ImageUploader 
                        v-model:photoId="user.photo_id" 
                        v-model:imageUrl="user.imagePreview" 
                        v-model:photoMarkedForDeletion="user.photoMarkedForDeletion" 
                        :photo="user.photo" 
                        aspectRatio="1:1"
                    />
                </VCol>
                <VCol cols="12" md="8">
                    <VCardText>
                    <VRow>
                        <VCol cols="12" md="6">
                        <VTextField v-model="user.first_name" label="Имя" />
                        </VCol>
                        <VCol cols="12" md="6">
                        <VTextField v-model="user.last_name" label="Фамилия" />
                        </VCol>
                        <VCol cols="12" md="6">
                        <VTextField v-model="user.middle_name" label="Отчество" />
                        </VCol>
                        <VCol cols="12" md="6">
                        <VTextField v-model="user.email" label="Email" />
                        </VCol>
                        <VCol cols="12" md="6">
                        <VTextField v-model="user.phone" label="Телефон" />
                        </VCol>
                        <VCol cols="12" md="6">
                        <VTextField v-if="user.organization && user.organization.name" v-model="user.organization.name" label="Организация" readonly />
                        </VCol>
                    </VRow>
                    <VBtn class="mt-4" @click="updateProfile">Сохранить</VBtn>
                    </VCardText>
                </VCol>
                </VRow>
            </div>
            </VWindowItem>

            <VWindowItem key="settings" value="settings">
            <div class="settings">
                <VCardText>
                <VRow>
                    <VCol cols="12" md="6">
                        <VSwitch v-model="settings.notifications" label="Уведомления" />
                        <VDivider class="my-5" />
                        <VTextField v-model="settings.language" label="Язык" />
                        <VDivider class="my-5" />
                        <VTextField v-model="settings.timezone" label="Часовой пояс" />
                    </VCol>
                    <VCol cols="12" md="6">
                        <!-- 👉 Theme -->
                        <h6 class="mt-3 text-base font-weight-regular">
                            Тема
                        </h6>
                        <VRadioGroup
                            v-model="theme"
                            inline
                        >
                            <VRadio
                            v-for="themeOption in ['system', 'light', 'dark']"
                            :key="themeOption"
                            :label="themeOption"
                            :value="themeOption"
                            class="text-capitalize"
                            />
                        </VRadioGroup>

                        <VDivider class="my-5" />

                        <!-- 👉 Semi Dark Menu -->
                        <div
                            class="align-center justify-space-between"
                            :class="vuetifyTheme.global.name.value === 'light' && appContentLayoutNav === AppContentLayoutNav.Vertical ? 'd-flex' : 'd-none'"
                        >
                            <VLabel
                            for="customizer-menu-semi-dark"
                            class="text-high-emphasis"
                            >
                            Темное меню
                            </VLabel>
                            <div>
                            <VSwitch
                                id="customizer-menu-semi-dark"
                                v-model="isVerticalNavSemiDark"
                                class="ms-2"
                            />
                            </div>
                        </div>

                        <VDivider class="my-5" />
                        <!-- 👉 Primary color -->
                        <h6 class="mt-3 text-base font-weight-regular">
                        Акцентирующий цвет
                        </h6>
                        <div class="d-flex gap-x-4 mt-2">
                            <div
                                v-for="(color, index) in colors"
                                :key="color"
                                style=" border-radius: 0.5rem; block-size: 2.5rem;inline-size: 2.5rem; transition: all 0.25s ease;"
                                :style="{ backgroundColor: getBoxColor(initialThemeColors[color], index) }"
                                class="cursor-pointer d-flex align-center justify-center"
                                :class="{ 'elevation-4': vuetifyTheme.current.value.colors.primary === getBoxColor(initialThemeColors[color], index) }"
                                @click="setPrimaryColor(getBoxColor(initialThemeColors[color], index))"
                            >
                                <VFadeTransition>
                                <VIcon
                                    v-show="vuetifyTheme.current.value.colors.primary === (getBoxColor(initialThemeColors[color], index))"
                                    icon="mdi-check"
                                    color="white"
                                />
                                </VFadeTransition>
                            </div>
                        </div>

                    </VCol>
                </VRow>
                </VCardText>
            </div>
            </VWindowItem>
        </VWindow>
    </VCard>
  </section>
</template>

<script setup>
import { ref } from 'vue';
import { useRoute } from 'vue-router';
import router from '@/router'
import { useUserProfileStore } from '@/stores/userProfileStore';

import { useTheme } from 'vuetify'
import { staticPrimaryColor } from '@/plugins/vuetify/theme'
import { useThemeConfig } from '@/@core/composable/useThemeConfig'
import { AppContentLayoutNav } from '@/@layouts/enums'
import { themeConfig } from '@themeConfig'


const route = useRoute();

const tabs = [
    {
        icon: 'mdi-account',
        title: 'Персональная информация',
        key: 'account',
    },
    {
        icon: 'mdi-cog',
        title: 'Настройки',
        key: 'settings',
    },
];

const tab = ref(route.hash.replace('#', '') || tabs[0].key) // Инициализация с ключом первой вкладки или хеша

watch(tab, (newTab) => {
  router.replace({ hash: '#' + newTab })
})

const userProfileStore = useUserProfileStore();

const user = ref({});
const settings = ref({
    notifications: true,
    darkMode: false,
    language: 'Русский',
    timezone: 'GMT+6'
});

const fetchProfile = () => {
    userProfileStore.fetchUserProfile().then(response => {
        console.log(response);
        user.value = response;
    }).catch(error => {
        console.log(error)
    })
}
const updateProfile = () => {

    console.log('update profile');
    console.log(user.value);

    userProfileStore.updateUserProfile(user.value).then(response => {
        router.replace('/');
    }).catch(error => {
        console.error(error)
    })

}

onMounted(() => {
    fetchProfile();
});


// Theme
const { theme, isVerticalNavSemiDark, appContentLayoutNav } = useThemeConfig()

// 👉 Primary Color
const vuetifyTheme = useTheme()

// const vuetifyThemesName = Object.keys(vuetifyTheme.themes.value)
const initialThemeColors = JSON.parse(JSON.stringify(vuetifyTheme.current.value.colors))

const colors = [
  'primary',
  'secondary',
  'success',
  'info',
  'warning',
  'error',
]

const setPrimaryColor = color => {
  const currentThemeName = vuetifyTheme.name.value

  vuetifyTheme.themes.value[currentThemeName].colors.primary = color
  localStorage.setItem(`${ themeConfig.app.title }-${ currentThemeName }ThemePrimaryColor`, color)
  localStorage.setItem(`${ themeConfig.app.title }-initial-loader-color`, color)
}

const getBoxColor = (color, index) => index ? color : staticPrimaryColor

</script>

<style scoped>
.personal-info {
padding: 20px;
}

.settings {
padding: 20px;
}

.text-center {
text-align: center;
}
</style>
