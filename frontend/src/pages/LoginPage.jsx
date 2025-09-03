import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { useTranslation } from 'react-i18next'
import { loginUser } from '../api/auth'

export default function LoginPage({ setCurrentUser }) {
  const { t } = useTranslation()
  const navigate = useNavigate()

  const [form, setForm] = useState({ email: '', password: '' })
  const [error, setError] = useState(null)

  // Skip login if token exists
  useEffect(() => {
    const token = localStorage.getItem('token')
    if (token) navigate('/') // redirect immediately
  }, [navigate])

  const handleChange = (e) =>
    setForm({ ...form, [e.target.name]: e.target.value })

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError(null)

    try {
      await loginUser(form, setCurrentUser) // <-- update App state immediately
      navigate('/')
    } catch (err) {
      setError(
        err.response?.data?.error || 'Login failed, check your credentials'
      )
    }
  }

  return (
    <div className="max-w-md mx-auto mt-10 p-6 bg-white shadow rounded-xl space-y-4">
      <h1 className="text-2xl font-bold">{t('login') || 'Login'}</h1>

      {error && (
        <div className="bg-red-100 border border-red-400 text-red-700 p-2 rounded">
          {error}
        </div>
      )}

      <form className="space-y-4" onSubmit={handleSubmit}>
        <input
          type="email"
          name="email"
          placeholder={t('email') || 'Email'}
          value={form.email}
          onChange={handleChange}
          className="w-full p-2 border rounded border-gray-300"
          required
        />

        <input
          type="password"
          name="password"
          placeholder={t('password') || 'Password'}
          value={form.password}
          onChange={handleChange}
          className="w-full p-2 border rounded border-gray-300"
          required
        />

        <button
          type="submit"
          className="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700"
        >
          {t('login') || 'Login'}
        </button>
      </form>
    </div>
  )
}
