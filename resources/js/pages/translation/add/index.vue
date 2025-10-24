<script setup>
import {
  integerValidator,
  requiredValidator,
} from '@/@core/utils/validators'
import {useTranslationListStore} from '@/views/translation/useTranslationListStore'
import router from '@/router'

const translationListStore = useTranslationListStore()
const route = useRoute()

const isFormValid = ref(false)
const hasError = ref(null);
const locales = [
  'ru',
  'en',
  'ky',
];
const selectedLocale = ref('ru');

const translationData = {
  name: '',
  key_name: '',
  keys: Array.from({length: 10}, () => ({ key_name: '', name: '', values: {} })),
}

const translationDataLocal = ref(structuredClone(translationData))

const addKey = () => {
  translationDataLocal.value.keys.push({ key_name: '', name: '', values: {} });
}

const removeKey = (index) => {
  translationDataLocal.value.keys.splice(index, 1);
}

const onSubmit = () => {
  refForm.value?.validate().then(({valid}) => {
    if (valid) {
      addNewTranslation(translationDataLocal.value)
    }
  })
}

const addNewTranslation = translationData => {

  console.log(translationData);
  
  translationListStore.addTranslation(translationData).then(response => {
    router.replace('/translation/list');
  }).catch(error => {
    hasError.value = error.response.data.message;
    alert(hasError.value);
  })
}

const refForm = ref()
const refInputEl = ref()

</script>

<template>
<!-- 👉 Form -->
<VForm
  class="mt-6"
  ref="refForm"
  v-model="isFormValid"
  @submit.prevent="onSubmit"
>
  <VRow>
    <VCol cols="12">
      <VCard title="Создание группы переводов">
        <VCardText>
            <VRow>
              <!-- Name -->
              <VCol
                  md="6"
                  cols="12"
              >
                <VTextField
                    v-model="translationDataLocal.name"
                    placeholder="Название группы"
                    label="Название группы"
                    :rules="[requiredValidator]"
                />
              </VCol>

              <!-- Name -->
              <VCol
                  md="6"
                  cols="12"
              >
                <VTextField
                    class="key-input"
                    v-model="translationDataLocal.key_name"
                    placeholder="Системный ключ"
                    label="Системный ключ"
                    :rules="[requiredValidator]"
                />
              </VCol>


            </VRow>
          
        </VCardText>
        </VCard>
        </VCol>
        </VRow>
        
    <VCard class="sticky-language-tabs">

      <VCardText>
    <VRow class="align-center">
      <VCol
          md="7"
          cols="12"
          class="d-flex flex-wrap gap-2"
      >
        <h3>Список переменных</h3>
      </VCol>
      <VCol
          md="5"
          cols="12"
          class="d-flex flex-wrap gap-2"
      >
        <VBtnToggle v-model="selectedLocale" mandatory color="primary">
          <VBtn v-for="locale in locales" :key="locale" :value="locale">
            {{ locale.toUpperCase() }}
          </VBtn>
        </VBtnToggle>
      </VCol>
    </VRow>
        </VCardText>
    </VCard>

  <VRow>
    <VCol cols="12">
      <VCard>
        <VCardText>

            <VRow class="align-center" v-for="(key, index) in translationDataLocal.keys" :key="index">
              
              <VCol md="11" cols="11">
                <VDivider></VDivider>
              </VCol>

              <VCol md="1" cols="1">
                <IconBtn @click="removeKey(index)">
                    <VIcon
                    :size="20"
                    icon="mdi-trashcan-outline"
                    />
                </IconBtn>
              </VCol>

              <VCol                  
                  md="6"
                  cols="12"
              >

                <VTextField
                    class="key-input"
                    v-model="key.key_name"
                    placeholder="Языковая переменная"
                    label="Языковая переменная"
                />

                <VTextField
                    class="mt-4"
                    v-model="key.name"
                    placeholder="Описание"
                    label="Описание"
                />
                
              </VCol>

              <VCol                  
                  md="6"
                  cols="12"
              >

                <div v-for="locale in locales" :key="locale">
                  <VTextarea
                    v-show="selectedLocale === locale"
                    v-model="key.values[locale]"
                    :label="`Перевод (${locale.toUpperCase()})`"
                  />
                </div>
                
              </VCol>
              

            </VRow>

            <VDivider class="my-5" />

            <VRow>
              <VCol
                  cols="12"
                  class="d-flex flex-wrap gap-4 justify-center"
              >
                <VBtn color="primary" @click="addKey">Добавить еще переменную</VBtn>
              </VCol>
            </VRow>

            <VDivider class="my-5" />   

            <VRow>
              <VCol
                  cols="12"
                  class="d-flex flex-wrap gap-4"
              >
                <VBtn
                    type="submit"
                >
                  Создать группу переводов
                </VBtn>
                <VBtn
                    color="secondary"
                    variant="outlined"
                    :to="{ name: 'translation-list', params: {  } }"
                >
                  Отмена
                </VBtn>
              </VCol>
            </VRow>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
  </VForm>
</template>

<style>
  .sticky-language-tabs {
    margin:10px 0!important;
    position:sticky!important;
    top:0;
    z-index:999!important;
    left:0;
    width:100%;
  }
  .key-input {font-weight:bold;}
</style>