import { useEffect, useState, useCallback } from 'react'
import { answersApi } from '../../api/answers'
import { useTranslation } from 'react-i18next'

export default function AnswersList({
  questionId,
  questionAuthorId,
  refreshTrigger,
  currentUser,
  highlightAnswerId,
}) {
  const { t } = useTranslation()
  const [answers, setAnswers] = useState([])
  const [pagination, setPagination] = useState({})
  const [highlightedId, setHighlightedId] = useState(null)
  const [filters, setFilters] = useState({
    page: 1,
    limit: 5,
    search: '',
    sort: 'createdAt:desc',
  })

  // Central fetch function
  const fetchAnswers = useCallback(async () => {
    try {
      const data = await answersApi(questionId).list(filters)
      setAnswers(data.items)
      setPagination(data.pagination)
    } catch (err) {
      console.error('Failed to fetch answers:', err)
    }
  }, [questionId, filters])

  // Fetch on mount / filters / refreshTrigger
  useEffect(() => {
    fetchAnswers()
  }, [fetchAnswers, refreshTrigger])

  // Highlight effect
  useEffect(() => {
    if (highlightAnswerId) {
      setHighlightedId(highlightAnswerId)
      const timeout = setTimeout(() => setHighlightedId(null), 3000)
      return () => clearTimeout(timeout)
    }
  }, [highlightAnswerId])

  const handleDelete = async (id) => {
    if (window.confirm(t('delete') + '?')) {
      try {
        await answersApi(questionId).delete(id)
        await fetchAnswers() // refresh after delete
      } catch (err) {
        console.error(err)
      }
    }
  }

  const handleMarkBest = async (id) => {
    try {
      await answersApi(questionId).markAsBest(id)
      await fetchAnswers() // refresh after markBest
    } catch (err) {
      console.error(err)
    }
  }

  const handleSearchChange = (e) =>
    setFilters({ ...filters, page: 1, search: e.target.value })
  const handleSortChange = (e) =>
    setFilters({ ...filters, page: 1, sort: e.target.value })
  const goToPage = (page) =>
    setFilters({
      ...filters,
      page: Math.max(1, Math.min(page, pagination.totalPages)),
    })

  if (!answers) return <p>{t('loading')}</p>

  const sortedAnswers = [...answers].sort(
    (a, b) => (b.isBest ? 1 : 0) - (a.isBest ? 1 : 0)
  )

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <input
          type="text"
          value={filters.search}
          onChange={handleSearchChange}
          placeholder={t('search')}
          className="border rounded px-2 py-1 flex-1"
        />
        <select
          value={filters.sort}
          onChange={handleSortChange}
          className="border rounded px-2 py-1"
        >
          <option value="createdAt:desc">{t('newest')}</option>
          <option value="createdAt:asc">{t('oldest')}</option>
        </select>
      </div>

      <h2 className="text-xl font-bold">{t('answers')}</h2>

      {sortedAnswers.map((a) => {
        const answerAuthorId = a.author?.id
        const canDelete =
          currentUser?.isAdmin || currentUser?.id === answerAuthorId
        const canMarkBest =
          (currentUser?.isAdmin || currentUser?.id === questionAuthorId) &&
          !a.isBest
        const isHighlighted = a.id === highlightedId

        return (
          <div
            key={a.id}
            className={`rounded-xl p-4 shadow transition-colors border ${
              a.isBest
                ? 'border-yellow-400 bg-yellow-50'
                : isHighlighted
                ? 'border-blue-400 bg-blue-50'
                : 'border-gray-200 bg-white'
            }`}
          >
            <div className="flex justify-between items-center">
              <div>
                <p className="font-semibold">
                  {a.author?.nickname
                    ? a.author.nickname
                    : a.authorNickname
                    ? `${a.authorNickname} (${a.authorEmail ?? 'no-email'})`
                    : 'Anonymous'}
                  {a.isBest && ' ⭐'}
                </p>
              </div>

              {(canDelete || canMarkBest) && (
                <div className="flex gap-2">
                  {canDelete && (
                    <button
                      onClick={() => handleDelete(a.id)}
                      className="px-2 py-1 text-sm border rounded bg-red-50 hover:bg-red-100"
                    >
                      {t('delete')}
                    </button>
                  )}
                  {canMarkBest && (
                    <button
                      onClick={() => handleMarkBest(a.id)}
                      className="px-2 py-1 text-sm border rounded bg-yellow-50 hover:bg-yellow-100"
                    >
                      {t('markBest')}
                    </button>
                  )}
                </div>
              )}
            </div>
            <p className="mt-2">{a.content}</p>
          </div>
        )
      })}

      {pagination.totalPages > 1 && (
        <div className="flex justify-center gap-2 mt-4">
          <button
            onClick={() => goToPage(filters.page - 1)}
            disabled={filters.page <= 1}
            className="px-3 py-1 border rounded disabled:opacity-50"
          >
            {t('prev')}
          </button>
          <span>
            {filters.page} / {pagination.totalPages}
          </span>
          <button
            onClick={() => goToPage(filters.page + 1)}
            disabled={filters.page >= pagination.totalPages}
            className="px-3 py-1 border rounded disabled:opacity-50"
          >
            {t('next')}
          </button>
        </div>
      )}
    </div>
  )
}
