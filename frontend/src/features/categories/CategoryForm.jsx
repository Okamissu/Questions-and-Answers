import { useState, useEffect, useCallback } from 'react'
import { useNavigate } from 'react-router-dom'
import { categoriesApi } from '../../api/categories'
import { useTranslation } from 'react-i18next'

export default function CategoryForm({ id = null }) {
  const { t } = useTranslation()
  const navigate = useNavigate()
  const isEdit = !!id

  const [form, setForm] = useState({ name: '' })
  const [errors, setErrors] = useState({})
  const [touched, setTouched] = useState({})
  const [isValid, setIsValid] = useState(false)
  const [allCategories, setAllCategories] = useState([])

  // fetch all categories for duplicate check
  useEffect(() => {
    categoriesApi
      .list({ page: 1, limit: 1000 })
      .then((data) => setAllCategories(data.items))
      .catch(console.error)
  }, [])

  // fetch category if editing
  useEffect(() => {
    if (isEdit) {
      categoriesApi.get(id).then((data) => setForm({ name: data.name }))
    }
  }, [id, isEdit])

  const handleChange = (e) => setForm({ name: e.target.value })
  const handleBlur = () => setTouched({ name: true })

  const validate = useCallback(() => {
    const newErrors = {}
    const trimmed = form.name.trim()

    if (!trimmed) newErrors.name = t('requiredField')
    else if (trimmed.length < 3) newErrors.name = t('minLength', { min: 3 })

    const duplicate = allCategories.find(
      (c) =>
        c.name.trim().toLowerCase() === trimmed.toLowerCase() && c.id !== id
    )
    if (duplicate) newErrors.name = t('categoryTaken')

    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }, [form.name, allCategories, id, t])

  useEffect(() => {
    setIsValid(validate())
  }, [form, validate])

  const handleSubmit = async (e) => {
    e.preventDefault()
    setTouched({ name: true })
    if (!validate()) return

    try {
      if (isEdit) await categoriesApi.update(id, form)
      else await categoriesApi.create(form)
      navigate('/categories')
    } catch (err) {
      console.error(err)
      alert(err?.error || 'Something went wrong')
    }
  }

  return (
    <div className="max-w-xl mx-auto p-6 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow">
      <h1 className="text-2xl font-bold mb-4 text-gray-900 dark:text-gray-100">
        {isEdit ? t('editCategory') : t('createCategory')}
      </h1>

      <form className="flex flex-col gap-4" onSubmit={handleSubmit}>
        <input
          name="name"
          placeholder={t('categoryName')}
          value={form.name}
          onChange={handleChange}
          onBlur={handleBlur}
          className="p-2 border border-gray-300 dark:border-gray-600 rounded text-gray-800 dark:text-gray-100 bg-white dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 w-full"
        />
        {errors.name && touched.name && (
          <p className="text-red-500 text-sm">{errors.name}</p>
        )}

        <div className="flex gap-2">
          <button
            type="button"
            onClick={() => navigate('/categories')}
            className="px-4 py-2 rounded border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
          >
            {t('cancel')}
          </button>

          <button
            type="submit"
            disabled={!isValid}
            className={`px-4 py-2 font-semibold rounded text-white transition ${
              isValid
                ? 'bg-blue-600 hover:bg-blue-700'
                : 'bg-gray-400 cursor-not-allowed'
            }`}
          >
            {isEdit ? t('update') : t('create')}
          </button>
        </div>
      </form>
    </div>
  )
}
