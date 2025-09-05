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

  const isAdmin = currentUser?.roles?.includes('ROLE_ADMIN')

  const fetchAnswers = useCallback(async () => {
    try {
      const data = await answersApi(questionId).list(filters)
      setAnswers(data.items)
      setPagination(data.pagination)
    } catch (err) {
      console.error('Failed to fetch answers:', err)
    }
  }, [questionId, filters])

  useEffect(() => {
    if (currentUser !== undefined) fetchAnswers()
  }, [fetchAnswers, refreshTrigger, currentUser])

  // Highlight pulse
  useEffect(() => {
    if (highlightAnswerId) {
      setHighlightedId(highlightAnswerId)
      const timeout = setTimeout(() => setHighlightedId(null), 2000)
      return () => clearTimeout(timeout)
    }
  }, [highlightAnswerId])

  const handleDelete = async (id) => {
    if (window.confirm(`${t('delete')}?`)) {
      try {
        await answersApi(questionId).delete(id)
        await fetchAnswers()
      } catch (err) {
        console.error(err)
      }
    }
  }

  const handleMarkBest = async (id) => {
    try {
      await answersApi(questionId).markAsBest(id)
      await fetchAnswers()
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

  if (currentUser === undefined)
    return <p className="text-gray-500 dark:text-gray-400">{t('loading')}</p>

  const sortedAnswers = [...answers].sort((a, b) => {
    // Best answer always first
    if (a.isBest && !b.isBest) return -1
    if (!a.isBest && b.isBest) return 1

    // Sort by filters.sort
    if (filters.sort === 'createdAt:desc')
      return new Date(b.createdAt) - new Date(a.createdAt)
    if (filters.sort === 'createdAt:asc')
      return new Date(a.createdAt) - new Date(b.createdAt)
    return 0
  })

  return (
    <div className="space-y-4">
      {/* Filters */}
      <div className="flex flex-wrap gap-2 mb-4">
        <input
          type="text"
          value={filters.search}
          onChange={handleSearchChange}
          placeholder={t('search')}
          aria-label={t('search')}
          className="input flex-1 min-w-[120px]"
        />
        <select
          value={filters.sort}
          onChange={handleSortChange}
          className="input"
          aria-label={t('sort')}
        >
          <option value="createdAt:desc">{t('newest')}</option>
          <option value="createdAt:asc">{t('oldest')}</option>
        </select>
      </div>

      {/* Heading */}
      <h2 className="text-xl font-bold text-gray-900 dark:text-gray-100">
        {t('answers')}
      </h2>

      {/* Answers */}
      {sortedAnswers.map((a) => {
        const answerAuthorId = a.author?.id
        const canDelete =
          currentUser && (isAdmin || currentUser.id === answerAuthorId)
        const canMarkBest =
          currentUser &&
          (isAdmin || currentUser.id === questionAuthorId) &&
          !a.isBest
        const isHighlighted = a.id === highlightedId

        const cardClasses = [
          'card p-4 rounded-xl shadow transition-all duration-500 border',
          a.isBest
            ? 'border-yellow-400 bg-yellow-50 dark:bg-yellow-800 dark:border-yellow-500'
            : isHighlighted
            ? 'border-blue-400 bg-blue-50 dark:bg-blue-900 dark:border-blue-500 animate-pulse-soft'
            : 'border-gray-200 bg-white dark:bg-gray-800 dark:border-gray-700',
        ].join(' ')

        return (
          <div key={a.id} className={cardClasses}>
            <div className="flex justify-between items-center">
              <p className="font-semibold text-gray-900 dark:text-gray-100">
                {a.author?.nickname
                  ? a.author.nickname
                  : a.authorNickname
                  ? `${a.authorNickname} (${a.authorEmail ?? 'no-email'})`
                  : 'Anonymous'}
                {a.isBest && ' ⭐'}
              </p>

              {(canDelete || canMarkBest) && (
                <div className="flex gap-2">
                  {canDelete && (
                    <button
                      onClick={() => handleDelete(a.id)}
                      className="flex items-center gap-1 px-3 py-1 rounded bg-red-500 text-white hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600 transition-colors duration-300 text-sm"
                    >
                      🗑️ <span>{t('delete')}</span>
                    </button>
                  )}
                  {canMarkBest && (
                    <button
                      onClick={() => handleMarkBest(a.id)}
                      className="flex items-center gap-1 px-3 py-1 rounded bg-yellow-200 text-gray-900 hover:bg-yellow-500 dark:bg-yellow-600 dark:text-white dark:hover:bg-yellow-700 transition-colors duration-300 text-sm"
                    >
                      ⭐ <span>{t('markBest')}</span>
                    </button>
                  )}
                </div>
              )}
            </div>
            <p className="mt-2 text-gray-800 dark:text-gray-300">{a.content}</p>
          </div>
        )
      })}

      {/* Pagination */}
      {pagination.totalPages > 1 && (
        <div className="flex justify-center items-center gap-2 mt-4">
          <button
            onClick={() => goToPage(filters.page - 1)}
            disabled={filters.page <= 1}
            className={`flex items-center gap-1 px-3 py-1 rounded transition-colors duration-300 text-sm ${
              filters.page <= 1
                ? 'bg-gray-400 cursor-not-allowed text-gray-200'
                : 'bg-blue-600 hover:bg-blue-700 text-white dark:bg-blue-500 dark:hover:bg-blue-600'
            }`}
          >
            ◀ {t('prev')}
          </button>
          <span className="text-gray-900 dark:text-gray-100">
            {filters.page} / {pagination.totalPages}
          </span>
          <button
            onClick={() => goToPage(filters.page + 1)}
            disabled={filters.page >= pagination.totalPages}
            className={`flex items-center gap-1 px-3 py-1 rounded transition-colors duration-300 text-sm ${
              filters.page >= pagination.totalPages
                ? 'bg-gray-400 cursor-not-allowed text-gray-200'
                : 'bg-blue-600 hover:bg-blue-700 text-white dark:bg-blue-500 dark:hover:bg-blue-600'
            }`}
          >
            {t('next')} ▶
          </button>
        </div>
      )}
    </div>
  )
}
