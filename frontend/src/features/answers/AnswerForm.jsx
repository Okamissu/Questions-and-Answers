import { useState, useEffect, useCallback } from 'react'
import { answersApi } from '../../api/answers'
import { useTranslation } from 'react-i18next'

export default function AnswerForm({
  questionId,
  currentUser,
  setAnswersRefresh,
  setHighlightAnswerId,
}) {
  const { t } = useTranslation()
  const [form, setForm] = useState({ nickname: '', email: '', content: '' })
  const [errors, setErrors] = useState({})
  const [touched, setTouched] = useState({})
  const [isValid, setIsValid] = useState(false)

  const handleChange = useCallback(
    (e) => setForm((prev) => ({ ...prev, [e.target.name]: e.target.value })),
    []
  )

  const handleBlur = useCallback(
    (e) => setTouched((prev) => ({ ...prev, [e.target.name]: true })),
    []
  )

  const validate = useCallback(() => {
    const newErrors = {}

    if (!form.content?.trim() || form.content.trim().length < 10) {
      newErrors.content = t('contentMinLength', { min: 10 })
    }

    if (!currentUser) {
      if (!form.nickname?.trim()) newErrors.nickname = t('requiredField')
      if (!form.email?.trim()) newErrors.email = t('requiredField')
      else if (!/\S+@\S+\.\S+/.test(form.email))
        newErrors.email = t('invalidEmail')
    }

    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }, [form, currentUser, t])

  useEffect(() => {
    setIsValid(validate())
  }, [form, currentUser, validate])

  const handleSubmit = async (e) => {
    e.preventDefault()
    setTouched({ nickname: true, email: true, content: true })

    if (!validate()) return

    const data = { content: form.content }
    if (!currentUser) {
      data.nickname = form.nickname
      data.email = form.email
    }

    try {
      const newAnswer = await answersApi(questionId).create(data)
      setForm({ nickname: '', email: '', content: '' })
      setTouched({})
      setErrors({})
      setAnswersRefresh((p) => p + 1)
      setHighlightAnswerId?.(newAnswer.id)
    } catch (err) {
      console.error(err)
    }
  }

  const showError = (field) => errors[field] && touched[field]

  return (
    <div className="max-w-xl mx-auto mt-6">
      <form
        className="card p-6 space-y-4 border border-gray-200 dark:border-gray-700"
        onSubmit={handleSubmit}
      >
        <h2 className="text-xl font-bold">{t('addAnswer')}</h2>

        {!currentUser && (
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label
                htmlFor="nickname"
                className="block text-sm font-medium mb-1"
              >
                {t('nickname')}
              </label>
              <input
                id="nickname"
                name="nickname"
                placeholder={t('nickname')}
                value={form.nickname}
                onChange={handleChange}
                onBlur={handleBlur}
                aria-label={t('nickname')}
                className="input w-full"
              />
              {showError('nickname') && (
                <p className="error-text">{errors.nickname}</p>
              )}
            </div>

            <div>
              <label htmlFor="email" className="block text-sm font-medium mb-1">
                {t('email')}
              </label>
              <input
                id="email"
                name="email"
                type="email"
                placeholder={t('email')}
                value={form.email}
                onChange={handleChange}
                onBlur={handleBlur}
                aria-label={t('email')}
                className="input w-full"
              />
              {showError('email') && (
                <p className="error-text">{errors.email}</p>
              )}
            </div>
          </div>
        )}

        <div>
          <label htmlFor="content" className="block text-sm font-medium mb-1">
            {t('content')}
          </label>
          <textarea
            id="content"
            name="content"
            placeholder={t('content')}
            value={form.content}
            onChange={handleChange}
            onBlur={handleBlur}
            rows={4}
            aria-label={t('content')}
            className="textarea w-full min-h-[100px]"
          />
          {showError('content') && (
            <p className="error-text">{errors.content}</p>
          )}
        </div>

        <div className="flex justify-end">
          <button
            type="submit"
            disabled={!isValid}
            className={`button ${
              isValid ? 'button-enabled' : 'button-disabled'
            }`}
          >
            {t('submit')}
          </button>
        </div>
      </form>
    </div>
  )
}
