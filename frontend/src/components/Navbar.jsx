import { Link, useNavigate } from 'react-router-dom'
import { useTranslation } from 'react-i18next'
import { useState, useEffect } from 'react'
import { logoutUser } from '../api/auth'

export default function Navbar() {
  const { t, i18n } = useTranslation()
  const navigate = useNavigate()
  const [token, setToken] = useState(localStorage.getItem('token'))

  useEffect(() => {
    const handleStorageChange = () => setToken(localStorage.getItem('token'))
    window.addEventListener('storage', handleStorageChange)
    return () => window.removeEventListener('storage', handleStorageChange)
  }, [])

  const handleLogout = () => {
    logoutUser()
    setToken(null)
    navigate('/login')
  }

  return (
    <nav
      style={{
        display: 'flex',
        gap: '1rem',
        padding: '1rem',
        borderBottom: '1px solid #ccc',
      }}
    >
      <Link to="/questions">{t('questions')}</Link>
      <Link to="/dashboard">{t('dashboard')}</Link>
      {token && <Link to="/users">{t('users')}</Link>}

      <div style={{ marginLeft: 'auto', display: 'flex', gap: '0.5rem' }}>
        <button onClick={() => i18n.changeLanguage('pl')}>PL</button>
        <button onClick={() => i18n.changeLanguage('en')}>EN</button>
        {token ? (
          <button onClick={handleLogout}>{t('logout')}</button>
        ) : (
          <Link to="/login">{t('login')}</Link>
        )}
      </div>
    </nav>
  )
}
