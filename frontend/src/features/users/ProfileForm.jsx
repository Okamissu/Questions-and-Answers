import { useState, useEffect } from 'react'
import { useTranslation } from 'react-i18next'
import { usersApi } from '../../api/users'
import { logoutUser } from '../../api/auth'
import { useNavigate } from 'react-router-dom'

export default function ProfileForm({ currentUser, onUpdate, setCurrentUser }) {
  const { t } = useTranslation()
  const navigate = useNavigate()

  const [form, setForm] = useState({ email: '', password: '', nickname: '' })
  const [errors, setErrors] = useState({})
  const [touched, setTouched] = useState({})
  const [loading, setLoading] = useState(true)
  const [triedRefresh, setTriedRefresh] = useState(false)

  // Populate form when currentUser is available
  useEffect(() => {
    if (!currentUser?.id) {
      if (onUpdate && !triedRefresh) {
        setTriedRefresh(true)
        onUpdate().finally(() => setLoading(false))
        return
      }
      setLoading(true)
      return
    }

    setForm({
      email: currentUser.email || '',
      password: '',
      nickname: currentUser.nickname || '',
    })
    setLoading(false)
  }, [currentUser, onUpdate, triedRefresh])

  const handleChange = (e) => {
    const { name, value } = e.target
    setForm((prev) => ({ ...prev, [name]: value }))
    setErrors((prev) => ({ ...prev, [name]: null })) // clear error on change
  }

  const handleBlur = (e) => {
    const { name } = e.target
    setTouched((prev) => ({ ...prev, [name]: true }))
    validateField(name)
  }

  const validateField = (field) => {
    const value = form[field]
    let error = null

    if (field === 'email') {
      if (!value.trim()) error = t('requiredField')
      else if (!/\S+@\S+\.\S+/.test(value)) error = t('invalidEmail')
    }

    if (field === 'nickname') {
      if (!value.trim()) error = t('requiredField')
      else if (value.length < 3) error = t('contentMinLength', { min: 3 })
    }

    if (field === 'password' && value) {
      if (value.length < 6) error = t('contentMinLength', { min: 6 })
    }

    setErrors((prev) => ({ ...prev, [field]: error }))
    return !error
  }

  const validateForm = () => {
    return ['email', 'nickname', 'password'].every(validateField)
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    setTouched({ email: true, password: true, nickname: true })

    if (!validateForm()) return

    try {
      const payload = { email: form.email, nickname: form.nickname }
      if (form.password) payload.plainPassword = form.password

      await usersApi.update(currentUser.id, payload)

      alert(
        t('profileUpdatedLogout') ||
          'Profile updated successfully! You will be logged out to apply changes.'
      )

      logoutUser(setCurrentUser)
      navigate('/login')
    } catch (err) {
      console.error(err)
      alert(err?.response?.data?.error || 'Something went wrong')
    }
  }

  if (loading)
    return (
      <p className="text-gray-500 dark:text-gray-400">
        {t('loading') || 'Loading...'}
      </p>
    )

  const showError = (field) => errors[field] && touched[field]

  return (
    <div className="max-w-md mx-auto p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow">
      <h1 className="text-2xl font-bold mb-4 text-gray-900 dark:text-gray-100">
        {t('editProfile') || 'Edit Profile'}
      </h1>

      <form className="flex flex-col gap-4" onSubmit={handleSubmit}>
        {/* Nickname */}
        <div>
          <input
            name="nickname"
            placeholder={t('nickname') || 'Nickname'}
            value={form.nickname}
            onChange={handleChange}
            onBlur={handleBlur}
            className={`p-2 w-full border rounded text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 transition ${
              showError('nickname')
                ? 'border-red-500 ring-red-300'
                : 'border-gray-300 dark:border-gray-600 ring-blue-500'
            }`}
            required
          />
          {showError('nickname') && (
            <p className="text-red-500 text-sm mt-1">{errors.nickname}</p>
          )}
        </div>

        {/* Email */}
        <div>
          <input
            type="email"
            name="email"
            placeholder={t('email') || 'Email'}
            value={form.email}
            onChange={handleChange}
            onBlur={handleBlur}
            className={`p-2 w-full border rounded text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 transition ${
              showError('email')
                ? 'border-red-500 ring-red-300'
                : 'border-gray-300 dark:border-gray-600 ring-blue-500'
            }`}
            required
          />
          {showError('email') && (
            <p className="text-red-500 text-sm mt-1">{errors.email}</p>
          )}
        </div>

        {/* Password */}
        <div>
          <input
            type="password"
            name="password"
            placeholder={t('password') || 'New Password (leave empty to keep)'}
            value={form.password}
            onChange={handleChange}
            onBlur={handleBlur}
            className={`p-2 w-full border rounded text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 transition ${
              showError('password')
                ? 'border-red-500 ring-red-300'
                : 'border-gray-300 dark:border-gray-600 ring-blue-500'
            }`}
          />
          {showError('password') && (
            <p className="text-red-500 text-sm mt-1">{errors.password}</p>
          )}
        </div>

        {/* Submit */}
        <button
          type="submit"
          className="px-4 py-2 bg-blue-600 text-white font-semibold rounded hover:bg-blue-700 transition"
        >
          {t('update') || 'Update'}
        </button>
      </form>
    </div>
  )
}
