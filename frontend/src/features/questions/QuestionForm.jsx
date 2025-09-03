import { useState, useEffect } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import { questionsApi } from '../../api/questions'
import { categoriesApi } from '../../api/categories'
import { tagsApi } from '../../api/tags'
import { useTranslation } from 'react-i18next'

export default function QuestionForm({ onSuccess }) {
  const { t } = useTranslation()
  const { id } = useParams()
  const navigate = useNavigate()
  const isEdit = !!id

  const [form, setForm] = useState({
    title: '',
    content: '',
    categoryId: '',
    tagIds: [],
  })

  const [categories, setCategories] = useState([])
  const [tags, setTags] = useState([])

  // Load categories & tags
  useEffect(() => {
    categoriesApi.list({}, true).then((res) => setCategories(res.items))
    tagsApi.list({}, true).then((res) => setTags(res.items))
  }, [])

  // Load existing question if editing
  useEffect(() => {
    if (isEdit) {
      questionsApi.get(id).then((data) =>
        setForm({
          title: data.title,
          content: data.content,
          categoryId: data.category?.id || '',
          tagIds: data.tags?.map((tag) => tag.id) || [],
        })
      )
    }
  }, [id, isEdit])

  const handleChange = (e) => {
    const { name, value } = e.target
    if (name === 'categoryId') {
      setForm({ ...form, [name]: Number(value) })
    } else {
      setForm({ ...form, [name]: value })
    }
  }

  const handleTagChange = (tagId) => {
    setForm((prev) => ({
      ...prev,
      tagIds: prev.tagIds.includes(tagId)
        ? prev.tagIds.filter((id) => id !== tagId)
        : [...prev.tagIds, tagId],
    }))
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    try {
      const payload = {
        title: form.title,
        content: form.content,
        categoryId: form.categoryId,
        tagIds: form.tagIds,
      }

      if (isEdit) {
        await questionsApi.update(id, payload)
      } else {
        await questionsApi.create(payload)
      }

      if (onSuccess) onSuccess()
      navigate('/questions')
    } catch (err) {
      console.error('Error creating/updating question', err)
      alert(err?.error || 'Something went wrong')
    }
  }

  return (
    <div className="max-w-2xl mx-auto p-6 bg-white border border-gray-200 rounded-xl shadow">
      <h1 className="text-2xl font-bold mb-4">
        {isEdit
          ? t('editQuestion') || 'Edit Question'
          : t('createQuestion') || 'Create Question'}
      </h1>

      <form className="flex flex-col gap-4" onSubmit={handleSubmit}>
        <input
          name="title"
          placeholder={t('title') || 'Title'}
          value={form.title}
          onChange={handleChange}
          required
          className="p-2 border border-gray-300 rounded text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500"
        />

        <textarea
          name="content"
          placeholder={t('content') || 'Content'}
          value={form.content}
          onChange={handleChange}
          rows={6}
          required
          className="p-2 border border-gray-300 rounded text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500"
        />

        <select
          name="categoryId"
          value={form.categoryId}
          onChange={handleChange}
          required
          className="p-2 border border-gray-300 rounded text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500"
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

        {/* Tags with modern pill-style checkboxes */}
        <div>
          <label className="block text-gray-700 font-semibold mb-2">
            {t('tags') || 'Tags'}
          </label>
          <div className="flex flex-wrap gap-2">
            {tags.map((tag) => {
              const selected = form.tagIds.includes(tag.id)
              return (
                <button
                  key={tag.id}
                  type="button"
                  onClick={() => handleTagChange(tag.id)}
                  className={`px-3 py-1 rounded-full border transition ${
                    selected
                      ? 'bg-blue-600 text-white border-blue-600'
                      : 'bg-gray-100 text-gray-800 border-gray-300 hover:bg-gray-200'
                  }`}
                >
                  {tag.name}
                </button>
              )
            })}
          </div>
        </div>

        <button
          type="submit"
          className="px-4 py-2 bg-blue-600 text-white font-semibold rounded hover:bg-blue-700 transition"
        >
          {isEdit ? t('update') || 'Update' : t('create') || 'Create'}
        </button>
      </form>
    </div>
  )
}
