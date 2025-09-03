import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import { Suspense, useState, useEffect } from 'react'
import Navbar from './components/Navbar'
import ProtectedRoute from './components/ProtectedRoute'
import { useTranslation } from 'react-i18next'

// Pages
import LoginPage from './pages/LoginPage'
import RegisterPage from './pages/RegisterPage'

// Questions CRUD
import QuestionsList from './features/questions/QuestionList'
import QuestionDetail from './features/questions/QuestionDetail'
import QuestionForm from './features/questions/QuestionForm'

// Categories CRUD (admin only)
import CategoriesList from './features/categories/CategoryList'
import CategoryForm from './features/categories/CategoryForm'

// Tags CRUD (admin only)
import TagsList from './features/tags/TagsList'
import TagForm from './features/tags/TagForm'

// Users CRUD (admin only)
import UsersList from './features/users/UserList'
import UserForm from './features/users/UserForm'

// Profile (self-service)
import ProfileForm from './features/users/ProfileForm'

// Answers
import AnswersList from './features/answers/AnswersList'
import AnswerForm from './features/answers/AnswerForm'

// API helper
import { usersApi } from './api/users'

export default function App() {
  const { t } = useTranslation()
  const [currentUser, setCurrentUser] = useState(undefined)

  useEffect(() => {
    const updateUser = async () => {
      try {
        const user = await usersApi.me()
        setCurrentUser(user || null)
      } catch {
        setCurrentUser(null)
      }
    }

    // initial load
    updateUser()

    // listen to localStorage changes (cross-tab)
    const handleStorage = (e) => {
      if (e.key === 'token') updateUser()
    }
    window.addEventListener('storage', handleStorage)

    return () => window.removeEventListener('storage', handleStorage)
  }, [])

  if (currentUser === undefined) return <p>{t('loading') || 'Loading...'}</p>

  return (
    <Suspense fallback={<p>{t('loading') || 'Loading...'}</p>}>
      <BrowserRouter>
        <Navbar currentUser={currentUser} setCurrentUser={setCurrentUser} />
        <main>
          <Routes>
            <Route path="/" element={<Navigate to="/questions" replace />} />

            {/* Public auth */}
            <Route
              path="/login"
              element={<LoginPage setCurrentUser={setCurrentUser} />}
            />
            <Route path="/register" element={<RegisterPage />} />

            {/* Questions */}
            <Route
              path="/questions"
              element={<QuestionsList currentUser={currentUser} />}
            />
            <Route
              path="/questions/create"
              element={
                <ProtectedRoute currentUser={currentUser}>
                  <QuestionForm currentUser={currentUser} />
                </ProtectedRoute>
              }
            />
            <Route
              path="/questions/:id"
              element={<QuestionDetail currentUser={currentUser} />}
            />
            <Route
              path="/questions/:id/edit"
              element={
                <ProtectedRoute currentUser={currentUser}>
                  <QuestionForm currentUser={currentUser} />
                </ProtectedRoute>
              }
            />

            {/* Categories (admin only) */}
            <Route
              path="/categories"
              element={
                <ProtectedRoute currentUser={currentUser} adminOnly>
                  <CategoriesList currentUser={currentUser} />
                </ProtectedRoute>
              }
            />
            <Route
              path="/categories/create"
              element={
                <ProtectedRoute currentUser={currentUser} adminOnly>
                  <CategoryForm currentUser={currentUser} />
                </ProtectedRoute>
              }
            />
            <Route
              path="/categories/:id/edit"
              element={
                <ProtectedRoute currentUser={currentUser} adminOnly>
                  <CategoryForm currentUser={currentUser} />
                </ProtectedRoute>
              }
            />

            {/* Tags (admin only) */}
            <Route
              path="/tags"
              element={
                <ProtectedRoute currentUser={currentUser} adminOnly>
                  <TagsList currentUser={currentUser} />
                </ProtectedRoute>
              }
            />
            <Route
              path="/tags/create"
              element={
                <ProtectedRoute currentUser={currentUser} adminOnly>
                  <TagForm currentUser={currentUser} />
                </ProtectedRoute>
              }
            />
            <Route
              path="/tags/:id/edit"
              element={
                <ProtectedRoute currentUser={currentUser} adminOnly>
                  <TagForm currentUser={currentUser} />
                </ProtectedRoute>
              }
            />

            {/* Users (admin only) */}
            <Route
              path="/users"
              element={
                <ProtectedRoute currentUser={currentUser} adminOnly>
                  <UsersList currentUser={currentUser} />
                </ProtectedRoute>
              }
            />
            <Route
              path="/users/create"
              element={
                <ProtectedRoute currentUser={currentUser} adminOnly>
                  <UserForm currentUser={currentUser} />
                </ProtectedRoute>
              }
            />
            <Route
              path="/users/:id/edit"
              element={
                <ProtectedRoute currentUser={currentUser} adminOnly>
                  <UserForm currentUser={currentUser} />
                </ProtectedRoute>
              }
            />

            {/* Profile */}
            <Route
              path="/profile"
              element={
                <ProtectedRoute currentUser={currentUser}>
                  <ProfileForm
                    currentUser={currentUser}
                    setCurrentUser={setCurrentUser}
                    onUpdate={async () => {
                      const user = await usersApi.me()
                      setCurrentUser(user)
                    }}
                  />
                </ProtectedRoute>
              }
            />

            {/* Answers */}
            <Route
              path="/questions/:question/answers"
              element={<AnswersList currentUser={currentUser} />}
            />
            <Route
              path="/questions/:question/answers/create"
              element={<AnswerForm currentUser={currentUser} />}
            />
          </Routes>
        </main>
      </BrowserRouter>
    </Suspense>
  )
}
