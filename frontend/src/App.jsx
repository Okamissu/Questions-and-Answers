import { BrowserRouter, Routes, Route } from 'react-router-dom'
import { Suspense, useState, useEffect } from 'react'
import Navbar from './components/Navbar'
import ProtectedRoute from './components/ProtectedRoute'
import { useTranslation } from 'react-i18next'

// Pages
import LoginPage from './pages/LoginPage'
import RegisterPage from './pages/RegisterPage'
import DashboardPage from './pages/DashboardPage'

// Questions CRUD
import QuestionsList from './features/questions/QuestionsList'
import QuestionDetail from './features/questions/QuestionDetail'
import QuestionForm from './features/questions/QuestionForm'

// Categories CRUD
import CategoriesList from './features/categories/CategoriesList'
import CategoryForm from './features/categories/CategoryForm'

// Tags CRUD
import TagsList from './features/tags/TagsList'
import TagForm from './features/tags/TagForm'

// Users CRUD
import UsersList from './features/users/UsersList'
import UserForm from './features/users/UserForm'

// Answers CRUD
import AnswersList from './features/answers/AnswersList'
import AnswerForm from './features/answers/AnswerForm'

// API helper
import { getCurrentUser } from './api/auth'

export default function App() {
  const { t } = useTranslation()
  const [currentUser, setCurrentUser] = useState(null)

  useEffect(() => {
    setCurrentUser(getCurrentUser())
  }, [])

  return (
    <Suspense fallback={<p>{t('loading') || 'Loading...'}</p>}>
      <BrowserRouter>
        <Navbar currentUser={currentUser} />
        <Routes>
          {/* Public */}
          <Route path="/login" element={<LoginPage />} />
          <Route path="/register" element={<RegisterPage />} />

          {/* Dashboard */}
          <Route
            path="/dashboard"
            element={
              <ProtectedRoute>
                <DashboardPage currentUser={currentUser} />
              </ProtectedRoute>
            }
          />

          {/* Questions */}
          <Route
            path="/questions"
            element={<QuestionsList currentUser={currentUser} />}
          />
          <Route
            path="/questions/create"
            element={
              <ProtectedRoute>
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
              <ProtectedRoute>
                <QuestionForm currentUser={currentUser} />
              </ProtectedRoute>
            }
          />

          {/* Categories */}
          <Route
            path="/categories"
            element={<CategoriesList currentUser={currentUser} />}
          />
          <Route
            path="/categories/create"
            element={
              <ProtectedRoute adminOnly>
                <CategoryForm currentUser={currentUser} />
              </ProtectedRoute>
            }
          />
          <Route
            path="/categories/:id/edit"
            element={
              <ProtectedRoute adminOnly>
                <CategoryForm currentUser={currentUser} />
              </ProtectedRoute>
            }
          />

          {/* Tags */}
          <Route
            path="/tags"
            element={<TagsList currentUser={currentUser} />}
          />
          <Route
            path="/tags/create"
            element={
              <ProtectedRoute>
                <TagForm currentUser={currentUser} />
              </ProtectedRoute>
            }
          />
          <Route
            path="/tags/:id/edit"
            element={
              <ProtectedRoute>
                <TagForm currentUser={currentUser} />
              </ProtectedRoute>
            }
          />

          {/* Users */}
          <Route
            path="/users"
            element={
              <ProtectedRoute>
                <UsersList currentUser={currentUser} />
              </ProtectedRoute>
            }
          />
          <Route
            path="/users/create"
            element={
              <ProtectedRoute>
                <UserForm currentUser={currentUser} />
              </ProtectedRoute>
            }
          />
          <Route
            path="/users/:id/edit"
            element={
              <ProtectedRoute>
                <UserForm currentUser={currentUser} />
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
      </BrowserRouter>
    </Suspense>
  )
}
