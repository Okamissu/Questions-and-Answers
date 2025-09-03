import { Link, useNavigate, useLocation } from 'react-router-dom'
import { useTranslation } from 'react-i18next'
import { logoutAndRedirect } from '../api/auth'

export default function Navbar({ currentUser, setCurrentUser }) {
  const { t, i18n } = useTranslation()
  const navigate = useNavigate()
  const location = useLocation()

  const isActive = (path) => location.pathname.startsWith(path)
  const isAdmin = currentUser?.roles?.includes('ROLE_ADMIN')

  return (
    <nav className="flex items-center gap-4 p-4 border-b border-gray-300 bg-white shadow">
      <div className="flex items-center gap-4">
        <Link
          to="/questions"
          className={`hover:underline ${
            isActive('/questions') ? 'font-bold' : ''
          }`}
        >
          {t('questions') || 'Questions'}
        </Link>

        {currentUser && (
          <Link
            to="/profile"
            className={`hover:underline ${
              isActive('/profile') ? 'font-bold' : ''
            }`}
          >
            {t('profile') || 'Profile'}
          </Link>
        )}

        {isAdmin && (
          <>
            <Link
              to="/users"
              className={`hover:underline ${
                isActive('/users') ? 'font-bold' : ''
              }`}
            >
              {t('users') || 'Users'}
            </Link>
            <Link
              to="/categories"
              className={`hover:underline ${
                isActive('/categories') ? 'font-bold' : ''
              }`}
            >
              {t('categories') || 'Categories'}
            </Link>
            <Link
              to="/tags"
              className={`hover:underline ${
                isActive('/tags') ? 'font-bold' : ''
              }`}
            >
              {t('tags') || 'Tags'}
            </Link>
            <Link
              to="/dashboard"
              className={`hover:underline ${
                isActive('/dashboard') ? 'font-bold' : ''
              }`}
            >
              {t('dashboard') || 'Dashboard'}
            </Link>
          </>
        )}
      </div>

      <div className="ml-auto flex items-center gap-2">
        <button
          className="px-2 py-1 border rounded hover:bg-gray-100"
          onClick={() => i18n.changeLanguage('pl')}
        >
          PL
        </button>
        <button
          className="px-2 py-1 border rounded hover:bg-gray-100"
          onClick={() => i18n.changeLanguage('en')}
        >
          EN
        </button>

        {currentUser ? (
          <button
            className="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600"
            onClick={() => logoutAndRedirect(setCurrentUser, navigate)}
          >
            {t('logout') || 'Logout'}
          </button>
        ) : (
          <>
            <Link
              className="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600"
              to="/login"
            >
              {t('login') || 'Login'}
            </Link>
            <Link
              className="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600"
              to="/register"
            >
              {t('register') || 'Register'}
            </Link>
          </>
        )}
      </div>
    </nav>
  )
}
