import { useState, useEffect, useCallback } from 'react'
import { useTranslation } from 'react-i18next'
import { questionsApi } from '../../api/questions'
import { categoriesApi } from '../../api/categories'
import { tagsApi } from '../../api/tags'

export default function QuestionForm({ onSuccess }) {
  const { t } = useTranslation()
  const [form, setForm] = useState({
    title: '',
    content: '',
    categoryId: '',
    tagIds: [],
  })
  const [errors, setErrors] = useState({})
  const [touched, setTouched] = useState({})
  const [isValid, setIsValid] = useState(false)

  const [categories, setCategories] = useState([])
  const [tags, setTags] = useState([])

  useEffect(() => {
    categoriesApi.list({}, true).then((res) => setCategories(res.items))
    tagsApi.list({}, true).then((res) => setTags(res.items))
  }, [])

  const handleChange = (e) => {
    const { name, value } = e.target
    setForm((prev) => ({
      ...prev,
      [name]: name === 'categoryId' ? Number(value) || '' : value,
    }))
  }

  const handleBlur = (e) =>
    setTouched((prev) => ({ ...prev, [e.target.name]: true }))

  const handleTagChange = (tagId) => {
    setForm((prev) => ({
      ...prev,
      tagIds: prev.tagIds.includes(tagId)
        ? prev.tagIds.filter((id) => id !== tagId)
        : [...prev.tagIds, tagId],
    }))
  }

  const validate = useCallback(() => {
    const newErrors = {}
    if (!form.title.trim()) newErrors.title = t('requiredField')
    else if (form.title.length < 3)
      newErrors.title = t('titleMinLength', { min: 3 })
    else if (form.title.length > 255)
      newErrors.title = t('titleMaxLength', { max: 255 })

    if (!form.content.trim()) newErrors.content = t('requiredField')
    else if (form.content.length < 10)
      newErrors.content = t('contentMinLength', { min: 10 })

    if (!form.categoryId) newErrors.categoryId = t('requiredField')

    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }, [form, t])

  useEffect(() => {
    setIsValid(validate())
  }, [form, validate])

  const handleSubmit = async (e) => {
    e.preventDefault()
    setTouched({ title: true, content: true, categoryId: true })
    if (!validate()) return

    try {
      await questionsApi.create(form)
      onSuccess?.()
      setForm({ title: '', content: '', categoryId: '', tagIds: [] })
    } catch (err) {
      console.error(err)
      alert(err?.error || 'Something went wrong')
    }
  }

  const showError = (field) => errors[field] && touched[field]

  return (
    <div className="max-w-3xl mx-auto p-6 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow transition-all duration-300 mb-6">
      <h2 className="text-xl font-bold mb-4 text-gray-900 dark:text-gray-100">
        {t('createQuestion')}
      </h2>

      <form className="flex flex-col gap-4" onSubmit={handleSubmit}>
        <input
          name="title"
          placeholder={t('title')}
          value={form.title}
          onChange={handleChange}
          onBlur={handleBlur}
          className="p-2 border border-gray-300 dark:border-gray-600 rounded text-gray-800 dark:text-gray-100 bg-white dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 w-full"
        />
        {showError('title') && (
          <p className="text-red-500 text-sm">{errors.title}</p>
        )}

        <textarea
          name="content"
          placeholder={t('content')}
          value={form.content}
          onChange={handleChange}
          onBlur={handleBlur}
          rows={6}
          className="p-2 border border-gray-300 dark:border-gray-600 rounded text-gray-800 dark:text-gray-100 bg-white dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 w-full"
        />
        {showError('content') && (
          <p className="text-red-500 text-sm">{errors.content}</p>
        )}

        <select
          name="categoryId"
          value={form.categoryId}
          onChange={handleChange}
          onBlur={handleBlur}
          className="p-2 border border-gray-300 dark:border-gray-600 rounded text-gray-800 dark:text-gray-100 bg-white dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 w-full"
        >
          <option value="">{t('selectCategory')}</option>
          {categories.map((cat) => (
            <option key={cat.id} value={cat.id}>
              {cat.name}
            </option>
          ))}
        </select>
        {showError('categoryId') && (
          <p className="text-red-500 text-sm">{errors.categoryId}</p>
        )}

        <div>
          <label className="block text-gray-700 dark:text-gray-300 font-semibold mb-2">
            {t('tags')}
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
                      : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 border-gray-300 dark:border-gray-600 hover:bg-gray-200 dark:hover:bg-gray-600'
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
          disabled={!isValid}
          className={`px-4 py-2 font-semibold rounded text-white transition ${
            isValid
              ? 'bg-blue-600 hover:bg-blue-700'
              : 'bg-gray-400 cursor-not-allowed'
          }`}
        >
          {t('create')}
        </button>
      </form>
    </div>
  )
}
