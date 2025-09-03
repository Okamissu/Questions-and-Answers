import { useState, useEffect } from 'react'
import { useTranslation } from 'react-i18next'
import { usersApi } from '../../api/users'
import { logoutUser } from '../../api/auth'
import { useNavigate } from 'react-router-dom'

export default function ProfileForm({ currentUser, onUpdate, setCurrentUser }) {
  console.log({ currentUser, onUpdate, setCurrentUser })
  const { t } = useTranslation()
  const navigate = useNavigate()

  const [form, setForm] = useState({
    email: '',
    password: '',
    nickname: '',
  })
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
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    try {
      const payload = {
        email: form.email,
        nickname: form.nickname,
      }
      if (form.password) payload.plainPassword = form.password

      await usersApi.update(currentUser.id, payload)

      alert(
        t('profileUpdatedLogout') ||
          'Profile updated successfully! You will be logged out to apply changes.'
      )

      // Force logout to refresh auth token and update navbar
      logoutUser(setCurrentUser)
      navigate('/login')
    } catch (err) {
      console.error(err)
      alert(err?.response?.data?.error || 'Something went wrong')
    }
  }

  if (loading) return <p>{t('loading') || 'Loading...'}</p>

  return (
    <div className="max-w-md mx-auto p-6 bg-white border border-gray-200 rounded-xl shadow">
      <h1 className="text-2xl font-bold mb-4">
        {t('editProfile') || 'Edit Profile'}
      </h1>
      <form className="flex flex-col gap-4" onSubmit={handleSubmit}>
        <input
          name="nickname"
          placeholder={t('nickname') || 'Nickname'}
          value={form.nickname}
          onChange={handleChange}
          required
          className="p-2 border border-gray-300 rounded text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        <input
          type="email"
          name="email"
          placeholder={t('email') || 'Email'}
          value={form.email}
          onChange={handleChange}
          required
          className="p-2 border border-gray-300 rounded text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        <input
          type="password"
          name="password"
          placeholder={t('password') || 'New Password (leave empty to keep)'}
          value={form.password}
          onChange={handleChange}
          className="p-2 border border-gray-300 rounded text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
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
