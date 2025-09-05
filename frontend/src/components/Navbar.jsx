import { Link, useNavigate, useLocation } from 'react-router-dom'
import { useTranslation } from 'react-i18next'
import { logoutAndRedirect } from '../api/auth'
import { useState, useEffect } from 'react'

export default function Navbar({ currentUser, setCurrentUser }) {
  const { t, i18n } = useTranslation()
  const navigate = useNavigate()
  const location = useLocation()
  const [isDark, setIsDark] = useState(false)

  const isActive = (path) => location.pathname.startsWith(path)
  const isAdmin = currentUser?.roles?.includes('ROLE_ADMIN')

  // Load theme from localStorage
  useEffect(() => {
    const savedTheme = localStorage.getItem('theme')
    if (savedTheme === 'dark') {
      document.documentElement.classList.add('dark')
      setIsDark(true)
    } else {
      document.documentElement.classList.remove('dark')
      setIsDark(false)
    }
  }, [])

  const toggleDarkMode = () => {
    document.documentElement.classList.toggle('dark')
    const dark = document.documentElement.classList.contains('dark')
    setIsDark(dark)
    localStorage.setItem('theme', dark ? 'dark' : 'light')
  }

  const navLinkClass = (active) =>
    `px-3 py-1 rounded text-sm font-medium transition-colors duration-300 ${
      active
        ? 'font-bold text-blue-600 dark:text-blue-400'
        : 'text-gray-900 dark:text-gray-100'
    }`

  const buttonClass =
    'px-3 py-1 rounded text-sm transition-colors duration-300 flex items-center justify-center'

  return (
    <nav className="flex items-center justify-between p-4 shadow-md bg-white dark:bg-gray-900 text-black dark:text-white transition-colors duration-300">
      {/* Left links */}
      <div className="flex items-center gap-3">
        <Link to="/questions" className={navLinkClass(isActive('/questions'))}>
          {t('questions') || 'Questions'}
        </Link>

        {currentUser && (
          <Link to="/profile" className={navLinkClass(isActive('/profile'))}>
            {t('profile') || 'Profile'}
          </Link>
        )}

        {isAdmin && (
          <>
            <Link to="/users" className={navLinkClass(isActive('/users'))}>
              {t('users') || 'Users'}
            </Link>
            <Link
              to="/categories"
              className={navLinkClass(isActive('/categories'))}
            >
              {t('categories') || 'Categories'}
            </Link>
            <Link to="/tags" className={navLinkClass(isActive('/tags'))}>
              {t('tags') || 'Tags'}
            </Link>
          </>
        )}
      </div>

      {/* Right buttons */}
      <div className="flex items-center gap-2">
        {/* Language buttons */}
        <button
          className={`${buttonClass} bg-gray-200 dark:bg-gray-700 dark:text-gray-100 hover:bg-gray-300 dark:hover:bg-gray-600`}
          onClick={() => i18n.changeLanguage('pl')}
        >
          PL
        </button>
        <button
          className={`${buttonClass} bg-gray-200 dark:bg-gray-700 dark:text-gray-100 hover:bg-gray-300 dark:hover:bg-gray-600`}
          onClick={() => i18n.changeLanguage('en')}
        >
          EN
        </button>

        {/* Dark/Light toggle */}
        <button
          className={`${buttonClass} bg-gray-200 dark:bg-gray-700 dark:text-gray-100 hover:bg-gray-300 dark:hover:bg-gray-600`}
          onClick={toggleDarkMode}
          title="Toggle dark mode"
        >
          {isDark ? '🌙' : '🌞'}
        </button>

        {/* Auth buttons */}
        {currentUser ? (
          <button
            className={`${buttonClass} bg-red-500 hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-700 text-white`}
            onClick={() => logoutAndRedirect(setCurrentUser, navigate)}
          >
            {t('logout') || 'Logout'}
          </button>
        ) : (
          <>
            <Link
              className={`${buttonClass} bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 text-white`}
              to="/login"
            >
              {t('login') || 'Login'}
            </Link>
            <Link
              className={`${buttonClass} bg-green-500 hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-700 text-white`}
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
