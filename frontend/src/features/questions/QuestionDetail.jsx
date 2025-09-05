import { useEffect, useState } from 'react'
import { useParams, Link, useNavigate } from 'react-router-dom'
import { questionsApi } from '../../api/questions'
import { useTranslation } from 'react-i18next'
import AnswersList from '../answers/AnswersList'
import AnswerForm from '../answers/AnswerForm'

export default function QuestionDetail({ currentUser }) {
  const { t } = useTranslation()
  const { id } = useParams()
  const navigate = useNavigate()
  const [question, setQuestion] = useState(null)
  const [answersRefresh, setAnswersRefresh] = useState(0)
  const [highlightAnswerId, setHighlightAnswerId] = useState(null)

  const handleDelete = async (id) => {
    if (confirm(t('areSure'))) {
      await questionsApi.delete(id)
      navigate('/questions')
    }
  }

  useEffect(() => {
    let isMounted = true
    questionsApi.get(id).then((data) => {
      if (isMounted) setQuestion(data)
    })
    return () => {
      isMounted = false
    }
  }, [id])

  if (!question)
    return (
      <p className="text-gray-500 dark:text-gray-400 text-center mt-6">
        {t('loading') || 'Loading...'}
      </p>
    )

  const canEditDelete =
    currentUser?.isAdmin || currentUser?.id === question.author?.id

  return (
    <div className="card max-w-2xl mx-auto p-6 border border-gray-200 dark:border-gray-700 rounded-xl shadow space-y-6">
      {/* Question header */}
      <div className="space-y-2">
        <div className="flex justify-between items-center">
          <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100">
            {question.title}
          </h1>
          {canEditDelete && (
            <div className="flex gap-2">
              <Link
                to={`/questions/${id}/edit`}
                title={t('edit')}
                className="nav-auth-button nav-auth-login px-3 py-1 text-sm"
              >
                ✏️ {t('edit')}
              </Link>
              <button
                onClick={() => handleDelete(question.id)}
                title={t('delete')}
                className="nav-auth-button nav-auth-logout px-3 py-1 text-sm"
              >
                🗑️ {t('delete')}
              </button>
            </div>
          )}
        </div>

        <p className="text-gray-800 dark:text-gray-300">{question.content}</p>

        {/* Category and Tags */}
        <div className="flex flex-wrap gap-2 mt-2 text-sm">
          {question.category && (
            <span className="text-blue-600 dark:text-blue-400">
              [{question.category.name}]
            </span>
          )}
          {question.tags?.map((tag) => (
            <span key={tag.id} className="text-green-600 dark:text-green-400">
              #{tag.name}
            </span>
          ))}
        </div>
      </div>

      {/* Divider */}
      <hr className="border-gray-300 dark:border-gray-600" />

      {/* Answers */}
      <AnswersList
        questionId={id}
        questionAuthorId={question.author?.id}
        refreshTrigger={answersRefresh}
        currentUser={currentUser}
        highlightAnswerId={highlightAnswerId}
      />

      {/* Answer form */}
      <AnswerForm
        questionId={id}
        currentUser={currentUser}
        setAnswersRefresh={() => setAnswersRefresh((prev) => prev + 1)}
        setHighlightAnswerId={setHighlightAnswerId}
      />
    </div>
  )
}
