// src/features/users/UserForm.jsx
import { useState, useEffect } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import { useTranslation } from 'react-i18next'
import { api } from '../../api/api'

export default function UserForm() {
  const { t } = useTranslation()
  const navigate = useNavigate()
  const { id } = useParams()
  const isEdit = !!id

  const [form, setForm] = useState({
    nickname: '',
    email: '',
    plainPassword: '',
  })

  const [errors, setErrors] = useState([])

  useEffect(() => {
    if (isEdit) {
      api.get(`/users/${id}`).then((res) =>
        setForm({
          nickname: res.data.nickname,
          email: res.data.email,
          plainPassword: '',
        })
      )
    }
  }, [id, isEdit])

  const handleChange = (e) =>
    setForm({ ...form, [e.target.name]: e.target.value })

  const handleSubmit = async (e) => {
    e.preventDefault()
    setErrors([])

    try {
      if (isEdit) {
        await api.put(`/users/${id}`, form)
      } else {
        await api.post('/users', form)
      }
      navigate('/users')
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
      <h1 className="text-2xl font-bold">
        {isEdit
          ? t('editUser') || 'Edit User'
          : t('createUser') || 'Create User'}
      </h1>

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
          required={!isEdit}
        />

        <button
          type="submit"
          className="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700"
        >
          {isEdit ? t('update') || 'Update' : t('create') || 'Create'}
        </button>
      </form>
    </div>
  )
}
