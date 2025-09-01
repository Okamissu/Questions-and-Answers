import i18n from 'i18next'
import { initReactI18next } from 'react-i18next'

i18n.use(initReactI18next).init({
  resources: {
    en: {
      translation: {
        // Common
        loading: 'Loading...',
        addNew: 'Add new',
        edit: 'Edit',
        delete: 'Delete',
        update: 'Update',
        create: 'Create',
        logout: 'Logout',
        login: 'Login',
        register: 'Register',
        dashboard: 'Dashboard',

        // Questions
        questions: 'Questions',
        title: 'Title',
        content: 'Content',
        bestAnswer: 'Best Answer',

        // Categories
        categories: 'Categories',
        category: 'Category',
        name: 'Name',

        // Tags
        tags: 'Tags',

        // Users
        users: 'Users',
        email: 'Email',
        username: 'Username',
        role: 'Role',
        password: 'Password',
        confirmPassword: 'Confirm Password',

        // Answers
        answers: 'Answers',
        addAnswer: 'Add Answer',
        authorEmail: 'Author Email',
        authorName: 'Author Name',
        answerContent: 'Answer Content',
        markAsBest: 'Mark as Best',

        //
        allTags: 'All Tags',
        search: 'Search...',
        newest: 'Newest',
        oldest: 'Oldest',
        allCategories: 'All Categories',
        areSure: 'Are you sure?',
      },
    },
    pl: {
      translation: {
        // Common
        loading: 'Ładowanie...',
        addNew: 'Dodaj nowe',
        edit: 'Edytuj',
        delete: 'Usuń',
        update: 'Aktualizuj',
        create: 'Utwórz',
        logout: 'Wyloguj',
        login: 'Zaloguj się',
        register: 'Rejestracja',
        dashboard: 'Panel',

        // Questions
        questions: 'Pytania',
        title: 'Tytuł',
        content: 'Treść',
        bestAnswer: 'Najlepsza odpowiedź',

        // Categories
        categories: 'Kategorie',
        category: 'Kategoria',
        name: 'Nazwa',

        // Tags
        tags: 'Tagi',

        // Users
        users: 'Użytkownicy',
        email: 'E-mail',
        username: 'Nick',
        role: 'Rola',
        password: 'Hasło',
        confirmPassword: 'Potwierdź hasło',

        // Answers
        answers: 'Odpowiedzi',
        addAnswer: 'Dodaj odpowiedź',
        authorEmail: 'Email autora',
        authorName: 'Nick autora',
        answerContent: 'Treść odpowiedzi',
        markAsBest: 'Oznacz jako najlepszą',

        //
        llTags: 'Wszystkie tagi',
        search: 'Szukaj...',
        newest: 'Najnowsze',
        oldest: 'Najstarsze',
        allCategories: 'Wszystkie kategorie',
        areSure: 'Na pewno?',
      },
    },
  },
  lng: 'pl',
  fallbackLng: 'en',
  interpolation: { escapeValue: false },
})

export default i18n
