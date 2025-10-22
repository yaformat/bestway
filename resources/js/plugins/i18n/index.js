import axiosIns from '@/plugins/axios'
import { createI18n } from 'vue-i18n'

const selectedLocale = localStorage.getItem('locale') ?? 'ru';

// Instantiate Vue I18n without the messages
export const i18n = createI18n({
  legacy: false,
  locale: selectedLocale,
  fallbackLocale: selectedLocale,
});

// The function which fetches translations with the given language
export async function fetchTranslations() {
  try {
    const response = await axiosIns.get(`/translations/${selectedLocale}`);
    // Set the locale and add the messages to i18n
    i18n.global.locale = selectedLocale;
    i18n.global.fallbackLocale = selectedLocale;
    i18n.global.setLocaleMessage(selectedLocale, response.data);
    
  } catch (error) {
    console.error(`Could not load translations due to: ${error}`);
  }
}