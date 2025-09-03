import { useEffect, useState } from 'react'
import { useParams, Link } from 'react-router-dom'
import { questionsApi } from '../../api/questions'
import { useTranslation } from 'react-i18next'
import AnswersList from '../answers/AnswersList'
import AnswerForm from '../answers/AnswerForm'

export default function QuestionDetail({ currentUser }) {
  const { t } = useTranslation()
  const { id } = useParams()
  const [question, setQuestion] = useState(null)
  const [answersRefresh, setAnswersRefresh] = useState(0)
  const [highlightAnswerId, setHighlightAnswerId] = useState(null)

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
    return <p className="text-gray-500">{t('loading') || 'Loading...'}</p>

  return (
    <div className="max-w-2xl mx-auto p-6 bg-white border border-gray-200 rounded-xl shadow">
      <div className="mb-4">
        <div className="flex justify-between items-center">
          <h1 className="text-2xl font-bold">{question.title}</h1>
          {(currentUser?.isAdmin ||
            currentUser?.id === question.author?.id) && (
            <Link
              to={`/questions/${id}/edit`}
              title={t('edit')}
              className="text-blue-600 hover:underline"
            >
              ✏️
            </Link>
          )}
        </div>
        <p className="mt-2 text-gray-800">{question.content}</p>
      </div>

      {/* Answers list first */}
      <AnswersList
        questionId={id}
        questionAuthorId={question.author?.id}
        refreshTrigger={answersRefresh}
        currentUser={currentUser}
        highlightAnswerId={highlightAnswerId} // pass for highlighting
      />

      {/* Form under the list */}
      <AnswerForm
        questionId={id}
        currentUser={currentUser}
        setAnswersRefresh={() => {
          setAnswersRefresh((prev) => prev + 1)
        }}
        setHighlightAnswerId={setHighlightAnswerId} // pass highlight setter
      />
    </div>
  )
}
