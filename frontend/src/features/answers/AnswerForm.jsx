import { useState } from 'react'
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

  const handleChange = (e) =>
    setForm({ ...form, [e.target.name]: e.target.value })

  const handleSubmit = async (e) => {
    e.preventDefault()
    const data = { content: form.content }
    if (!currentUser) {
      data.nickname = form.nickname
      data.email = form.email
    }

    try {
      const newAnswer = await answersApi(questionId).create(data) // send request
      setForm({ nickname: '', email: '', content: '' })
      setAnswersRefresh((p) => p + 1) // refresh list
      setHighlightAnswerId?.(newAnswer.id) // highlight new answer
    } catch (err) {
      console.error(err)
    }
  }

  return (
    <div className="max-w-xl mx-auto mt-6">
      <form
        onSubmit={handleSubmit}
        className="bg-white shadow rounded-xl p-6 space-y-4 border border-gray-200"
      >
        <h2 className="text-xl font-bold">{t('addAnswer')}</h2>
        {!currentUser && (
          <>
            <div>
              <label className="block text-sm font-medium mb-1">
                {t('nickname')}
              </label>
              <input
                name="nickname"
                placeholder={t('nickname')}
                value={form.nickname}
                onChange={handleChange}
                required
                className="w-full border rounded-lg px-3 py-2 focus:ring focus:ring-blue-200"
              />
            </div>

            <div>
              <label className="block text-sm font-medium mb-1">
                {t('email')}
              </label>
              <input
                name="email"
                type="email"
                placeholder={t('email')}
                value={form.email}
                onChange={handleChange}
                required
                className="w-full border rounded-lg px-3 py-2 focus:ring focus:ring-blue-200"
              />
            </div>
          </>
        )}

        <div>
          <label className="block text-sm font-medium mb-1">
            {t('content')}
          </label>
          <textarea
            name="content"
            placeholder={t('content')}
            value={form.content}
            onChange={handleChange}
            required
            rows={4}
            className="w-full border rounded-lg px-3 py-2 focus:ring focus:ring-blue-200"
          />
        </div>

        <div className="flex justify-end">
          <button
            type="submit"
            className="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow"
          >
            {t('submit')}
          </button>
        </div>
      </form>
    </div>
  )
}
