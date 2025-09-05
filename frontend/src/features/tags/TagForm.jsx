import { useState, useEffect, useCallback } from 'react'
import { useNavigate } from 'react-router-dom'
import { tagsApi } from '../../api/tags'
import { useTranslation } from 'react-i18next'

export default function TagForm({ id = null }) {
  const { t } = useTranslation()
  const navigate = useNavigate()
  const isEdit = !!id

  const [form, setForm] = useState({ name: '' })
  const [errors, setErrors] = useState({})
  const [touched, setTouched] = useState({})
  const [isValid, setIsValid] = useState(false)
  const [allTags, setAllTags] = useState([]) // all existing tags

  // Fetch existing tags once
  useEffect(() => {
    tagsApi
      .list({ page: 1, limit: 1000 })
      .then((data) => setAllTags(data.items))
      .catch(console.error)
  }, [])

  // If editing, fetch the tag
  useEffect(() => {
    if (isEdit) {
      tagsApi.get(id).then((data) => setForm({ name: data.name }))
    }
  }, [id, isEdit])

  const handleChange = (e) => setForm({ name: e.target.value })
  const handleBlur = () => setTouched({ name: true })

  const validate = useCallback(() => {
    const newErrors = {}

    const trimmed = form.name.trim()
    if (!trimmed) newErrors.name = t('requiredField')
    else if (trimmed.length < 2) newErrors.name = t('minLength', { min: 2 })

    // Check duplicate
    const duplicate = allTags.find(
      (t) =>
        t.name.trim().toLowerCase() === trimmed.toLowerCase() && t.id !== id
    )
    if (duplicate) newErrors.name = t('tagTaken')

    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }, [form.name, allTags, id, t])

  useEffect(() => {
    setIsValid(validate())
  }, [form, validate])

  const handleSubmit = async (e) => {
    e.preventDefault()
    setTouched({ name: true })
    if (!validate()) return

    try {
      if (isEdit) await tagsApi.update(id, form)
      else await tagsApi.create(form)
      navigate('/tags')
    } catch (err) {
      console.error(err)
      alert(err?.error || 'Something went wrong')
    }
  }

  return (
    <div className="max-w-xl mx-auto p-6 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow">
      <h1 className="text-2xl font-bold mb-4 text-gray-900 dark:text-gray-100">
        {isEdit ? t('editTag') : t('createTag')}
      </h1>

      <form className="flex flex-col gap-4" onSubmit={handleSubmit}>
        <input
          name="name"
          placeholder={t('tagName')}
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
            onClick={() => navigate('/tags')}
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
