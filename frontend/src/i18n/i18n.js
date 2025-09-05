import i18n from 'i18next'
import { initReactI18next } from 'react-i18next'

// Import your JSON translation files
import enTranslations from './en/translation.json'
import plTranslations from './pl/translation.json'

i18n.use(initReactI18next).init({
  resources: {
    en: { translation: enTranslations },
    pl: { translation: plTranslations },
  },
  lng: 'pl', // default language
  fallbackLng: 'en',
  interpolation: { escapeValue: false },
})

export default i18n
