import { useState, useEffect } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import {
  createQuestion,
  getQuestion,
  updateQuestion,
} from '../../api/questions'
import { getCategories } from '../../api/categories'
import { useTranslation } from 'react-i18next'

export default function QuestionForm() {
  const { t } = useTranslation()
  const { id } = useParams()
  const navigate = useNavigate()
  const isEdit = !!id

  const [form, setForm] = useState({ title: '', content: '', category: '' })
  const [categories, setCategories] = useState([])

  useEffect(() => getCategories().then(setCategories), [])

  useEffect(() => {
    if (isEdit) {
      getQuestion(id).then((data) =>
        setForm({
          title: data.title,
          content: data.content,
          category: data.category?.id || '',
        })
      )
    }
  }, [id, isEdit])

  const handleChange = (e) =>
    setForm({ ...form, [e.target.name]: e.target.value })

  const handleSubmit = (e) => {
    e.preventDefault()
    const action = isEdit ? updateQuestion(id, form) : createQuestion(form)
    action.then(() => navigate('/questions'))
  }

  return (
    <div
      style={{
        maxWidth: '700px',
        margin: '2rem auto',
        padding: '1rem',
        border: '1px solid #ddd',
        borderRadius: '6px',
        backgroundColor: '#fafafa',
      }}
    >
      <h1 style={{ marginBottom: '1rem' }}>
        {isEdit
          ? t('editQuestion') || 'Edit Question'
          : t('createQuestion') || 'Create Question'}
      </h1>

      <form
        onSubmit={handleSubmit}
        style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}
      >
        <input
          name="title"
          placeholder={t('title') || 'Title'}
          value={form.title}
          onChange={handleChange}
          style={{
            padding: '0.5rem',
            fontSize: '1rem',
            borderRadius: '4px',
            border: '1px solid #ccc',
          }}
          required
        />

        <textarea
          name="content"
          placeholder={t('content') || 'Content'}
          value={form.content}
          onChange={handleChange}
          rows={6}
          style={{
            padding: '0.5rem',
            fontSize: '1rem',
            borderRadius: '4px',
            border: '1px solid #ccc',
          }}
          required
        />

        <select
          name="category"
          value={form.category}
          onChange={handleChange}
          style={{
            padding: '0.5rem',
            fontSize: '1rem',
            borderRadius: '4px',
            border: '1px solid #ccc',
          }}
          required
        >
          <option value="">
            {t('selectCategory') || '-- Select Category --'}
          </option>
          {categories.map((cat) => (
            <option key={cat.id} value={cat.id}>
              {cat.name}
            </option>
          ))}
        </select>

        <button
          type="submit"
          style={{
            padding: '0.5rem 1rem',
            fontSize: '1rem',
            backgroundColor: '#007bff',
            color: 'white',
            border: 'none',
            borderRadius: '4px',
            cursor: 'pointer',
          }}
        >
          {isEdit ? t('update') || 'Update' : t('create') || 'Create'}
        </button>
      </form>
    </div>
  )
}
