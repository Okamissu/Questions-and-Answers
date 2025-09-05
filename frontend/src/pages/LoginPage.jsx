import { useState, useEffect, useCallback } from 'react'
import { useNavigate } from 'react-router-dom'
import { useTranslation } from 'react-i18next'
import { loginUser } from '../api/auth'

export default function LoginPage({ setCurrentUser }) {
  const { t } = useTranslation()
  const navigate = useNavigate()

  const [form, setForm] = useState({ email: '', password: '' })
  const [errors, setErrors] = useState({})
  const [touched, setTouched] = useState({})
  const [isValid, setIsValid] = useState(false)

  // Redirect if already logged in
  useEffect(() => {
    const token = localStorage.getItem('token')
    if (token) navigate('/')
  }, [navigate])

  const handleChange = (e) => {
    setForm({ ...form, [e.target.name]: e.target.value })
    setErrors((prev) => ({ ...prev, [e.target.name]: null })) // clear field error
  }

  const handleBlur = (e) => setTouched({ ...touched, [e.target.name]: true })

  const validate = useCallback(() => {
    const newErrors = {}
    if (!form.email.trim()) newErrors.email = t('requiredField')
    else if (!/\S+@\S+\.\S+/.test(form.email))
      newErrors.email = t('invalidEmail')

    if (!form.password.trim()) newErrors.password = t('requiredField')
    else if (form.password.length < 6)
      newErrors.password = t('contentMinLength', { min: 6 })

    setErrors((prev) => ({ form: prev.form || null, ...newErrors }))
    return Object.keys(newErrors).length === 0
  }, [form, t])

  useEffect(() => setIsValid(validate()), [form, validate])

  const handleSubmit = async (e) => {
    e.preventDefault()
    setTouched({ email: true, password: true })
    setErrors((prev) => ({ ...prev, form: null }))

    if (!validate()) return

    try {
      await loginUser(form, setCurrentUser)
      navigate('/')
    } catch (err) {
      setErrors((prev) => ({
        ...prev,
        form:
          err.response?.status === 401
            ? t('loginFailed') || 'Incorrect email or password'
            : err.response?.data?.error || t('loginFailed') || 'Login failed',
      }))
    }
  }

  const showError = (field) =>
    field === 'form' ? errors.form : errors[field] && touched[field]

  return (
    <div className="max-w-md mx-auto mt-10 p-6 card space-y-4 transition-colors duration-300">
      <h1 className="text-2xl font-bold">{t('login')}</h1>

      {errors.form && (
        <div className="error-text p-2 rounded bg-red-100 dark:bg-red-700 border border-red-400 dark:border-red-500">
          {errors.form}
        </div>
      )}

      <form className="space-y-4" onSubmit={handleSubmit}>
        <div>
          <input
            type="email"
            name="email"
            placeholder={t('email')}
            value={form.email}
            onChange={handleChange}
            onBlur={handleBlur}
            className="input w-full"
          />
          {showError('email') && <p className="error-text">{errors.email}</p>}
        </div>

        <div>
          <input
            type="password"
            name="password"
            placeholder={t('password')}
            value={form.password}
            onChange={handleChange}
            onBlur={handleBlur}
            className="input w-full"
          />
          {showError('password') && (
            <p className="error-text">{errors.password}</p>
          )}
        </div>

        <button
          type="submit"
          disabled={!isValid}
          className={`button ${
            isValid ? 'button-enabled' : 'button-disabled'
          } `}
        >
          {t('login')}
        </button>
      </form>
    </div>
  )
}
