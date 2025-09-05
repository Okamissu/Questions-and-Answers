import { useState, useEffect } from 'react'
import { useTranslation } from 'react-i18next'
import { usersApi } from '../../api/users'
import { logoutUser } from '../../api/auth'
import { useNavigate } from 'react-router-dom'

export default function UserForm({
  user, // user being edited
  currentUser, // logged-in user
  setCurrentUser, // function to update current user
  onSaved, // callback after save
  onCancel, // cancel callback
}) {
  const { t } = useTranslation()
  const navigate = useNavigate()

  const [form, setForm] = useState({ email: '', nickname: '', password: '' })
  const [errors, setErrors] = useState({})
  const [touched, setTouched] = useState({})
  const [allUsers, setAllUsers] = useState([])

  // fetch all users for uniqueness check
  useEffect(() => {
    const fetchUsers = async () => {
      try {
        const res = await usersApi.list()
        setAllUsers(res.items)
      } catch (err) {
        console.error(err)
      }
    }
    fetchUsers()
  }, [])

  // populate form when user prop is available
  useEffect(() => {
    if (user) {
      setForm({
        email: user.email || '',
        nickname: user.nickname || '',
        password: '',
      })
    }
  }, [user])

  const handleChange = (e) => {
    const { name, value } = e.target
    setForm((prev) => ({ ...prev, [name]: value }))
    setErrors((prev) => ({ ...prev, [name]: null }))
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
      else if (!user || user.email !== value) {
        if (allUsers.some((u) => u.email === value)) error = t('emailTaken')
      }
    }

    if (field === 'nickname') {
      if (!value.trim()) error = t('requiredField')
      else if (value.length < 3) error = t('contentMinLength', { min: 3 })
      else if (!user || user.nickname !== value) {
        if (allUsers.some((u) => u.nickname === value))
          error = t('nicknameTaken')
      }
    }

    if (field === 'password') {
      if (!user && !value) error = t('requiredField') // required on create
      else if (value && value.length < 6)
        error = t('contentMinLength', { min: 6 })
    }

    setErrors((prev) => ({ ...prev, [field]: error }))
    return !error
  }

  const validateForm = () =>
    ['email', 'nickname', 'password'].every(validateField)

  const handleSubmit = async (e) => {
    e.preventDefault()
    setTouched({ email: true, nickname: true, password: true })

    if (!validateForm()) return

    try {
      const payload = { email: form.email, nickname: form.nickname }
      if (form.password) payload.plainPassword = form.password

      if (user?.id) await usersApi.update(user.id, payload)
      else await usersApi.create(payload)

      // logout if current user edited their email or password
      const emailChanged =
        currentUser?.id === user?.id &&
        form.email.trim() !== currentUser.email.trim()
      const passwordChanged = currentUser?.id === user?.id && !!form.password

      if (emailChanged || passwordChanged) {
        alert(
          t('profileUpdatedLogout') ||
            'Profile updated! You will be logged out to apply changes.'
        )
        logoutUser(setCurrentUser)
        navigate('/login')
        return
      }

      onSaved?.()
    } catch (err) {
      console.error(err)
      alert(err?.response?.data?.error || 'Something went wrong')
    }
  }

  const showError = (field) => errors[field] && touched[field]

  const hasErrors =
    Object.values(errors).some(Boolean) ||
    !form.email ||
    !form.nickname ||
    (!user && !form.password)

  return (
    <form
      onSubmit={handleSubmit}
      className="flex flex-col gap-4 p-4 border rounded shadow"
    >
      {/* Nickname */}
      <input
        name="nickname"
        placeholder={t('nickname')}
        value={form.nickname}
        onChange={handleChange}
        onBlur={handleBlur}
        className={`p-2 border rounded ${
          showError('nickname') ? 'border-red-500' : 'border-gray-300'
        }`}
        required
      />
      {showError('nickname') && (
        <p className="text-red-500 text-sm">{errors.nickname}</p>
      )}

      {/* Email */}
      <input
        name="email"
        type="email"
        placeholder={t('email')}
        value={form.email}
        onChange={handleChange}
        onBlur={handleBlur}
        className={`p-2 border rounded ${
          showError('email') ? 'border-red-500' : 'border-gray-300'
        }`}
        required
      />
      {showError('email') && (
        <p className="text-red-500 text-sm">{errors.email}</p>
      )}

      {/* Password */}
      <input
        name="password"
        type="password"
        placeholder={user?.id ? t('passwordPlaceholder') : t('password')}
        value={form.password}
        onChange={handleChange}
        onBlur={handleBlur}
        className={`p-2 border rounded ${
          showError('password') ? 'border-red-500' : 'border-gray-300'
        }`}
      />
      {showError('password') && (
        <p className="text-red-500 text-sm">{errors.password}</p>
      )}

      {/* Actions */}
      <div className="flex gap-2">
        <button
          type="submit"
          disabled={hasErrors}
          className={`px-4 py-2 rounded text-white ${
            hasErrors
              ? 'bg-gray-400 cursor-not-allowed'
              : 'bg-blue-600 hover:bg-blue-700'
          }`}
        >
          {user?.id ? t('editUser') : t('createUser')}
        </button>
        <button
          type="button"
          onClick={onCancel}
          className="px-4 py-2 bg-gray-300 rounded"
        >
          {t('cancel')}
        </button>
      </div>
    </form>
  )
}
