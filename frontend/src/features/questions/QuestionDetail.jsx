import { useEffect, useState } from 'react'
import { useParams, Link } from 'react-router-dom'
import { getQuestion } from '../../api/questions'
import { useTranslation } from 'react-i18next'

import AnswersList from '../answers/AnswersList'
import AnswerForm from '../answers/AnswerForm'
import { createAnswer } from '../../api/answers'

export default function QuestionDetail({ currentUser }) {
  const { t } = useTranslation()
  const { id } = useParams()
  const [question, setQuestion] = useState(null)
  const [answersRefresh, setAnswersRefresh] = useState(0)
  const [highlightAnswerId, setHighlightAnswerId] = useState(null)

  useEffect(() => getQuestion(id).then(setQuestion), [id])

  const handleAddAnswer = (data) => {
    createAnswer({ ...data, question: id }).then((newAnswer) => {
      setHighlightAnswerId(newAnswer.id)
      setAnswersRefresh((prev) => prev + 1)
    })
  }

  if (!question) return <p>{t('loading') || 'Loading...'}</p>

  return (
    <div
      style={{
        maxWidth: '700px',
        margin: '2rem auto',
        padding: '1rem',
        border: '1px solid #ddd',
        borderRadius: '6px',
        backgroundColor: '#fafafa',
      }}
    >
      {/* Question Card */}
      <div style={{ marginBottom: '1rem' }}>
        <div
          style={{
            display: 'flex',
            justifyContent: 'space-between',
            alignItems: 'center',
          }}
        >
          <h1 style={{ margin: 0 }}>{question.title}</h1>
          <Link to={`/questions/${id}/edit`} title={t('edit')}>
            ✏️
          </Link>
        </div>

        <p style={{ margin: '0.5rem 0', color: '#333' }}>{question.content}</p>

        {/* Metadata */}
        <div
          style={{
            fontSize: '0.85rem',
            color: '#666',
            display: 'flex',
            gap: '1rem',
          }}
        >
          {question.author && (
            <span>
              {t('author') || 'Author'}: {question.author.nickname}
            </span>
          )}
          {question.createdAt && (
            <span>
              {t('createdAt') || 'Created'}:{' '}
              {new Date(question.createdAt).toLocaleDateString()}
            </span>
          )}
        </div>

        {/* Category */}
        {question.category && (
          <span
            style={{
              cursor: 'pointer',
              color: 'blue',
              fontSize: '0.85rem',
              marginRight: '0.5rem',
            }}
            title={t('filterByCategory')}
          >
            [{question.category.name}]
          </span>
        )}

        {/* Tags */}
        {question.tags?.length > 0 && (
          <span style={{ fontSize: '0.85rem' }}>
            {question.tags.map((tag) => (
              <span
                key={tag.id}
                style={{
                  cursor: 'pointer',
                  color: 'green',
                  marginRight: '0.25rem',
                }}
                title={t('filterByTag')}
              >
                #{tag.name}
              </span>
            ))}
          </span>
        )}
      </div>

      <hr style={{ margin: '1rem 0' }} />

      {/* Answers */}
      <AnswersList
        refreshTrigger={answersRefresh}
        currentUser={currentUser}
        highlightAnswerId={highlightAnswerId}
      />
      <AnswerForm onSubmit={handleAddAnswer} />
    </div>
  )
}
