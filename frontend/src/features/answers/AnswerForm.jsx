import { useState } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import { createAnswer } from '../../api/answers'
import { useTranslation } from 'react-i18next'

export default function AnswerForm({ onSubmit }) {
  const { t } = useTranslation()
  const { questionId } = useParams() // make sure your route param matches
  const navigate = useNavigate()

  const [form, setForm] = useState({ nickname: '', email: '', content: '' })

  const handleChange = (e) =>
    setForm({ ...form, [e.target.name]: e.target.value })

  const handleSubmit = (e) => {
    e.preventDefault()
    const data = { ...form, question: questionId }

    if (onSubmit) {
      onSubmit(data) // use callback from parent
    } else {
      // fallback: default API + navigate
      createAnswer(data).then(() =>
        navigate(`/questions/${questionId}/answers`)
      )
    }

    setForm({ nickname: '', email: '', content: '' }) // reset form
  }

  return (
    <form onSubmit={handleSubmit}>
      <h2>{t('addAnswer')}</h2>
      <input
        name="nickname"
        placeholder={t('nickname')}
        value={form.nickname}
        onChange={handleChange}
        required
      />
      <input
        name="email"
        type="email"
        placeholder={t('email')}
        value={form.email}
        onChange={handleChange}
        required
      />
      <textarea
        name="content"
        placeholder={t('content')}
        value={form.content}
        onChange={handleChange}
        required
      />
      <button type="submit">{t('submit')}</button>
    </form>
  )
}
