import { useEffect, useState } from 'react'
import { useParams } from 'react-router-dom'
import { getAnswers, deleteAnswer, markAsBest } from '../../api/answers'
import { useTranslation } from 'react-i18next'

export default function AnswersList({
  refreshTrigger,
  currentUser,
  highlightAnswerId,
}) {
  const { t } = useTranslation()
  const { questionId } = useParams()
  const [answers, setAnswers] = useState([])
  const [highlightedId, setHighlightedId] = useState(null)

  useEffect(() => {
    const fetchAnswers = () => getAnswers(questionId).then(setAnswers)
    fetchAnswers()
  }, [questionId, refreshTrigger])

  // Highlight new answer for 3 seconds
  useEffect(() => {
    if (highlightAnswerId) {
      setHighlightedId(highlightAnswerId)
      const timeout = setTimeout(() => setHighlightedId(null), 3000)
      return () => clearTimeout(timeout)
    }
  }, [highlightAnswerId])

  const handleDelete = (answerId) => {
    if (window.confirm(t('delete') + '?')) {
      deleteAnswer(answerId).then(() => getAnswers(questionId).then(setAnswers))
    }
  }

  const handleMarkBest = (answerId) => {
    markAsBest(answerId).then(() => getAnswers(questionId).then(setAnswers))
  }

  if (!answers) return <p>{t('loading')}</p>

  // sort: best answer first
  const sortedAnswers = [...answers].sort(
    (a, b) => (b.best ? 1 : 0) - (a.best ? 1 : 0)
  )

  return (
    <div>
      <h2>{t('answers')}</h2>
      {sortedAnswers.length === 0 ? (
        <p>{t('noAnswers')}</p>
      ) : (
        <ul style={{ listStyle: 'none', padding: 0 }}>
          {sortedAnswers.map((a) => {
            const canManage =
              currentUser?.isAdmin || currentUser?.id === a.authorId
            const isHighlighted = a.id === highlightedId

            return (
              <li
                key={a.id}
                style={{
                  border: a.best ? '2px solid gold' : '1px solid #ccc',
                  borderRadius: '4px',
                  marginBottom: '0.5rem',
                  padding: '0.5rem',
                  backgroundColor: a.best
                    ? '#fff9e6'
                    : isHighlighted
                    ? '#e6f7ff'
                    : '#fff',
                  boxShadow: a.best ? '0 0 5px rgba(255, 215, 0, 0.5)' : 'none',
                  transition: 'background-color 0.5s ease',
                }}
              >
                <p>
                  <strong>
                    {a.nickname} {a.best && '⭐'}
                  </strong>{' '}
                  ({a.email})
                </p>
                <p>{a.content}</p>
                {canManage && (
                  <div style={{ display: 'flex', gap: '0.5rem' }}>
                    <button onClick={() => handleDelete(a.id)}>
                      {t('delete')}
                    </button>
                    {!a.best && (
                      <button onClick={() => handleMarkBest(a.id)}>
                        {t('markBest')}
                      </button>
                    )}
                  </div>
                )}
              </li>
            )
          })}
        </ul>
      )}
    </div>
  )
}
