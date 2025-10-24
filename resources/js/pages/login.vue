<script setup>
import { VForm } from 'vuetify/components/VForm'
import { useGenerateImageVariant } from '@/@core/composable/useGenerateImageVariant'
import { useAppAbility } from '@/plugins/casl/useAppAbility'
import axiosIns from '@/plugins/axios'

import authV1LoginMaskDark from '@images/pages/auth-v1-login-mask-dark.png'
import authV1LoginMaskLight from '@images/pages/auth-v1-login-mask-light.png'
import { VNodeRenderer } from '@/@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import {
  emailValidator,
  requiredValidator,
} from '@/@core/utils/validators'

const isPasswordVisible = ref(false)
const authV1ThemeLoginMask = useGenerateImageVariant(authV1LoginMaskLight, authV1LoginMaskDark)
const route = useRoute()
const router = useRouter()
const ability = useAppAbility()
const isSnackbarVisible = ref(false)

const loginErrorMsg = ref()


//TODO 

localStorage.removeItem('userData')
localStorage.removeItem('accessToken')
localStorage.removeItem('refreshToken')
localStorage.removeItem('userAbilities')
//

const errors = ref({
  email: undefined,
  password: undefined,
})

const refVForm = ref()
const email = ref()
const password = ref()
const rememberMe = ref(false)

const login = () => {

  isSnackbarVisible.value = false;

  axiosIns.post('/auth/login', {
    email: email.value,
    password: password.value,
  }).then(response => {
    
    const { accessToken, userData, userAbilities } = response.data

    localStorage.setItem('userAbilities', JSON.stringify(userAbilities))
    ability.update(userAbilities)
    localStorage.setItem('userData', JSON.stringify(userData))
    localStorage.setItem('accessToken', JSON.stringify(accessToken))

    router.replace(route.query.to ? String(route.query.to) : '/')

    initializeData();

  }).catch(e => {

    console.log(e);

    loginErrorMsg.value = e.response.data.message;

    console.log(e.response.data.message);

    isSnackbarVisible.value = !isSnackbarVisible.value

  })
}

const onSubmit = () => {
    refVForm.value?.validate().then(({ valid: isValid }) => {
        if (isValid) login()
    })
}
</script>


<template>
  <div class="auth-wrapper d-flex align-center justify-center pa-4">
    <VCard
      class="auth-card pa-2 pt-7"
      max-width="448"
    >
      <VCardItem class="justify-center">
        <template #prepend>
          <div class="me-n2">
            <VNodeRenderer :nodes="themeConfig.app.logo" />
          </div>
        </template>

        <!--
        <VCardTitle class="text-2xl font-weight-bold text-capitalize">
          {{ themeConfig.app.title }}
        </VCardTitle>
        -->
      </VCardItem>

      <VCardText class="pt-2 text-center">
        <h5 class="text-h5 mb-1">
          Добро пожаловать! 👋🏻
        </h5>
        <p class="mb-0">
          Пройдите авторизацию, чтобы начать работу...
        </p>
      </VCardText>

      <VCardText>
          <VForm
            ref="refVForm"
            @submit.prevent="onSubmit"
          >
            <VRow>
              <!-- email -->
              <VCol cols="12">
                <VTextField
                  v-model="email"
                  label="Email"
                  placeholder=""
                  type="email"
                  autofocus
                  :rules="[requiredValidator, emailValidator]"
                  :error-messages="errors.email"
                />
              </VCol>

              <!-- password -->
              <VCol cols="12">

                <VTextField
                  v-model="password"
                  label="Пароль"
                  placeholder=""
                  type="password"
                  :rules="[requiredValidator]"
                  :error-messages="errors.password"
                />

                <div class="d-flex align-center flex-wrap justify-space-between mt-1 mb-4">
                  <VCheckbox
                    v-model="rememberMe"
                    label="Запомнить меня"
                  />
                </div>

                <VBtn
                  block
                  type="submit"
                >
                  Войти
                </VBtn>
              </VCol>

            </VRow>
          </VForm>
      </VCardText>
    </VCard>
    <VImg
      :src="authV1ThemeLoginMask"
      class="d-none d-md-block auth-footer-mask"
    />

  <!-- Snackbar -->
  <VSnackbar
    v-model="isSnackbarVisible"
    :timeout="2000"
  >
    {{ loginErrorMsg }}
  </VSnackbar>

  </div>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth.scss";
</style>

<route lang="yaml">
meta:
  layout: blank
  action: read
  subject: Auth
  redirectIfLoggedIn: true
</route>
