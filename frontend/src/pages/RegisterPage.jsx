import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useTranslation } from 'react-i18next'
import { api } from '../api/api' // your axios instance

export default function RegisterPage() {
  const { t } = useTranslation()
  const navigate = useNavigate()

  const [form, setForm] = useState({
    nickname: '',
    email: '',
    plainPassword: '',
  })

  const [errors, setErrors] = useState([])

  const handleChange = (e) =>
    setForm({ ...form, [e.target.name]: e.target.value })

  const handleSubmit = async (e) => {
    e.preventDefault()
    setErrors([])

    try {
      await api.post('/users', form)
      navigate('/login') // redirect to login after successful registration
    } catch (err) {
      if (err.response?.data?.errors) {
        setErrors(err.response.data.errors)
      } else if (err.response?.data?.error) {
        setErrors([{ message: err.response.data.error }])
      } else {
        setErrors([{ message: 'Unknown error' }])
      }
    }
  }

  return (
    <div className="max-w-md mx-auto mt-10 p-6 bg-white shadow rounded-xl space-y-4">
      <h1 className="text-2xl font-bold">{t('register') || 'Register'}</h1>

      {errors.length > 0 && (
        <ul className="bg-red-100 border border-red-400 text-red-700 p-2 rounded space-y-1">
          {errors.map((err, idx) => (
            <li key={idx}>
              {err.field ? `${err.field}: ` : ''}
              {err.message}
            </li>
          ))}
        </ul>
      )}

      <form className="space-y-4" onSubmit={handleSubmit}>
        <input
          type="text"
          name="nickname"
          placeholder={t('nickname') || 'Nickname'}
          value={form.nickname}
          onChange={handleChange}
          className="w-full p-2 border rounded border-gray-300"
          required
        />

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
          name="plainPassword"
          placeholder={t('password') || 'Password'}
          value={form.plainPassword}
          onChange={handleChange}
          className="w-full p-2 border rounded border-gray-300"
          required
        />

        <button
          type="submit"
          className="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700"
        >
          {t('register') || 'Register'}
        </button>
      </form>
    </div>
  )
}
