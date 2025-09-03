import { useState, useEffect } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import { tagsApi } from '../../api/tags'
import { useTranslation } from 'react-i18next'

export default function TagForm() {
  const { t } = useTranslation()
  const { id } = useParams()
  const navigate = useNavigate()
  const isEdit = !!id

  const [form, setForm] = useState({ name: '' })

  useEffect(() => {
    if (isEdit) {
      tagsApi.get(id).then((data) => setForm({ name: data.name }))
    }
  }, [id, isEdit])

  const handleChange = (e) =>
    setForm({ ...form, [e.target.name]: e.target.value })

  const handleSubmit = (e) => {
    e.preventDefault()
    const action = isEdit ? tagsApi.update(id, form) : tagsApi.create(form)
    action.then(() => navigate('/tags'))
  }

  return (
    <form onSubmit={handleSubmit}>
      <h1>
        {isEdit ? t('edit') + ' ' + t('tags') : t('create') + ' ' + t('tags')}
      </h1>
      <input
        name="name"
        placeholder={t('name')}
        value={form.name}
        onChange={handleChange}
      />
      <button type="submit">{isEdit ? t('update') : t('create')}</button>
    </form>
  )
}
