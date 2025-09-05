import { useState, useEffect, useCallback } from 'react'
import { useNavigate } from 'react-router-dom'
import { useTranslation } from 'react-i18next'
import { api } from '../api/api'

export default function RegisterPage() {
  const { t } = useTranslation()
  const navigate = useNavigate()

  const [form, setForm] = useState({
    nickname: '',
    email: '',
    plainPassword: '',
  })
  const [errors, setErrors] = useState({})
  const [touched, setTouched] = useState({})
  const [isValid, setIsValid] = useState(false)

  const handleChange = (e) =>
    setForm({ ...form, [e.target.name]: e.target.value })
  const handleBlur = (e) => setTouched({ ...touched, [e.target.name]: true })

  const validate = useCallback(() => {
    const newErrors = {}
    if (!form.nickname.trim()) newErrors.nickname = t('requiredField')
    else if (form.nickname.trim().length < 3)
      newErrors.nickname = t('contentMinLength', { min: 3 })

    if (!form.email.trim()) newErrors.email = t('requiredField')
    else if (!/\S+@\S+\.\S+/.test(form.email))
      newErrors.email = t('invalidEmail')

    if (!form.plainPassword.trim()) newErrors.plainPassword = t('requiredField')
    else if (form.plainPassword.length < 6)
      newErrors.plainPassword = t('contentMinLength', { min: 6 })

    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }, [form, t])

  useEffect(() => setIsValid(validate()), [form, validate])

  const handleSubmit = async (e) => {
    e.preventDefault()
    setTouched({ nickname: true, email: true, plainPassword: true })
    if (!validate()) return

    try {
      await api.post('/users', form)
      navigate('/login')
    } catch (err) {
      if (err.response?.data?.errors) {
        const apiErrors = {}
        err.response.data.errors.forEach(
          (e) => (apiErrors[e.field || 'form'] = e.message)
        )
        setErrors(apiErrors)
      } else if (err.response?.data?.error) {
        setErrors({ form: err.response.data.error })
      } else {
        setErrors({ form: 'Unknown error' })
      }
    }
  }

  const showError = (field) => errors[field] && touched[field]

  return (
    <div className="max-w-md mx-auto mt-10 p-6 card space-y-4 transition-colors duration-300">
      <h1 className="text-2xl font-bold">{t('register')}</h1>

      {showError('form') && (
        <div className="error-text p-2 rounded bg-red-100 dark:bg-red-700 border border-red-400 dark:border-red-500">
          {errors.form}
        </div>
      )}

      <form className="space-y-4" onSubmit={handleSubmit}>
        <div>
          <input
            type="text"
            name="nickname"
            placeholder={t('nickname')}
            value={form.nickname}
            onChange={handleChange}
            onBlur={handleBlur}
            className="input w-full"
          />
          {showError('nickname') && (
            <p className="error-text">{errors.nickname}</p>
          )}
        </div>

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
            name="plainPassword"
            placeholder={t('password')}
            value={form.plainPassword}
            onChange={handleChange}
            onBlur={handleBlur}
            className="input w-full"
          />
          {showError('plainPassword') && (
            <p className="error-text">{errors.plainPassword}</p>
          )}
        </div>

        <button
          type="submit"
          disabled={!isValid}
          className={`button ${
            isValid ? 'button-enabled' : 'button-disabled'
          }`}
        >
          {t('register')}
        </button>
      </form>
    </div>
  )
}
