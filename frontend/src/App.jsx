import {
  BrowserRouter,
  Routes,
  Route,
  Navigate,
  useParams,
} from 'react-router-dom'
import { Suspense, useState, useEffect } from 'react'
import { useTranslation } from 'react-i18next'

import Navbar from './components/Navbar'
import ProtectedRoute from './components/ProtectedRoute'

import { isTokenExpired, logoutUser } from './api/auth'
import { usersApi } from './api/users'

// Pages
import LoginPage from './pages/LoginPage'
import RegisterPage from './pages/RegisterPage'

// Questions
import QuestionsList from './features/questions/QuestionList'
import QuestionDetail from './features/questions/QuestionDetail'
import QuestionForm from './features/questions/QuestionForm'

// Categories (admin)
import CategoriesList from './features/categories/CategoryList'
import CategoryForm from './features/categories/CategoryForm'

// Tags (admin)
import TagsList from './features/tags/TagsList'
import TagForm from './features/tags/TagForm'

// Users (admin)
import UsersList from './features/users/UserList'
import UserForm from './features/users/UserForm'

// Profile (self)
import ProfileForm from './features/users/ProfileForm'

// Answers
import AnswersList from './features/answers/AnswersList'
import AnswerForm from './features/answers/AnswerForm'

// --- Wrappers ---
function CategoryFormWrapper() {
  const { id } = useParams()
  return <CategoryForm id={id} />
}

function QuestionFormWrapper() {
  const { id } = useParams()
  return <QuestionForm id={id} />
}

function TagFormWrapper() {
  const { id } = useParams()
  return <TagForm id={id} />
}

function UserFormWrapper({ currentUser, setCurrentUser, currentUserRoles }) {
  const { id } = useParams()
  const [user, setUser] = useState(null)

  useEffect(() => {
    const fetchUser = async () => {
      try {
        const data = await usersApi.get(id)
        setUser(data)
      } catch (err) {
        console.error(err)
      }
    }
    if (id) fetchUser()
  }, [id])

  if (!user) return <p>Loading...</p>

  return (
    <UserForm
      user={user} // user being edited
      currentUser={currentUser} // logged-in user
      setCurrentUser={setCurrentUser} // for logout if needed
      currentUserRoles={currentUserRoles}
      onSaved={() => {}}
    />
  )
}

// --- App ---
export default function App() {
  const { t } = useTranslation()
  const [currentUser, setCurrentUser] = useState(undefined)

  useEffect(() => {
    const updateUser = async () => {
      const token = localStorage.getItem('token')
      if (!token || isTokenExpired(token)) {
        logoutUser(setCurrentUser)
        return
      }
      try {
        const user = await usersApi.me()
        setCurrentUser(user || null)
      } catch {
        logoutUser(setCurrentUser)
      }
    }

    updateUser()
    const handleStorage = (e) => e.key === 'token' && updateUser()
    window.addEventListener('storage', handleStorage)
    return () => window.removeEventListener('storage', handleStorage)
  }, [])

  if (currentUser === undefined)
    return (
      <p className="text-gray-700 dark:text-gray-300">
        {t('loading') || 'Loading...'}
      </p>
    )

  return (
    <Suspense
      fallback={
        <p className="text-gray-700 dark:text-gray-300">
          {t('loading') || 'Loading...'}
        </p>
      }
    >
      <div className="min-h-screen bg-white text-black dark:bg-gray-900 dark:text-white transition-colors duration-300">
        <BrowserRouter>
          <Navbar currentUser={currentUser} setCurrentUser={setCurrentUser} />
          <main className="p-4">
            <Routes>
              <Route path="/" element={<Navigate to="/questions" replace />} />

              {/* Auth */}
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
                    <QuestionForm />
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
                    <QuestionFormWrapper />
                  </ProtectedRoute>
                }
              />

              {/* Categories (admin) */}
              <Route
                path="/categories"
                element={
                  <ProtectedRoute currentUser={currentUser} adminOnly>
                    <CategoriesList />
                  </ProtectedRoute>
                }
              />
              <Route
                path="/categories/create"
                element={
                  <ProtectedRoute currentUser={currentUser} adminOnly>
                    <CategoryForm />
                  </ProtectedRoute>
                }
              />
              <Route
                path="/categories/:id/edit"
                element={
                  <ProtectedRoute currentUser={currentUser} adminOnly>
                    <CategoryFormWrapper />
                  </ProtectedRoute>
                }
              />

              {/* Tags (admin) */}
              <Route
                path="/tags"
                element={
                  <ProtectedRoute currentUser={currentUser} adminOnly>
                    <TagsList />
                  </ProtectedRoute>
                }
              />
              <Route
                path="/tags/create"
                element={
                  <ProtectedRoute currentUser={currentUser} adminOnly>
                    <TagForm />
                  </ProtectedRoute>
                }
              />
              <Route
                path="/tags/:id/edit"
                element={
                  <ProtectedRoute currentUser={currentUser} adminOnly>
                    <TagFormWrapper />
                  </ProtectedRoute>
                }
              />

              {/* Users (admin) */}
              <Route
                path="/users"
                element={
                  <ProtectedRoute currentUser={currentUser} adminOnly>
                    <UsersList currentUserRoles={currentUser?.roles || []} />
                  </ProtectedRoute>
                }
              />
              <Route
                path="/users/create"
                element={
                  <ProtectedRoute currentUser={currentUser} adminOnly>
                    <UserForm
                      currentUserRoles={currentUser?.roles || []}
                      onSaved={() => {}}
                    />
                  </ProtectedRoute>
                }
              />
              <Route
                path="/users/:id/edit"
                element={
                  <ProtectedRoute currentUser={currentUser} adminOnly>
                    <UserFormWrapper
                      currentUser={currentUser}
                      setCurrentUser={setCurrentUser}
                      currentUserRoles={currentUser?.roles || []}
                    />
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
      </div>
    </Suspense>
  )
}
