import { useState, useEffect, useCallback } from 'react'
import { useNavigate, Link } from 'react-router-dom'
import { useTranslation } from 'react-i18next'
import { tagsApi } from '../../api/tags'

export default function TagsList() {
  const { t } = useTranslation()
  const navigate = useNavigate()

  const [tags, setTags] = useState([])
  const [search, setSearch] = useState('')
  const [sort, setSort] = useState('newest')
  const [page, setPage] = useState(1)
  const [totalPages, setTotalPages] = useState(1)
  const [refreshKey, setRefreshKey] = useState(0)

  const fetchTags = useCallback(() => {
    tagsApi
      .list({ page, search, sort })
      .then((data) => {
        setTags(data.items)
        setTotalPages(data.pagination.totalPages)
      })
      .catch(console.error)
  }, [page, search, sort])

  useEffect(() => {
    fetchTags()
  }, [fetchTags, refreshKey])

  useEffect(() => {
    setPage(1)
  }, [search, sort])

  const handleDelete = (tag) => {
    if (!window.confirm(t('confirmDelete'))) return

    tagsApi
      .delete(tag.id)
      .then(() => setRefreshKey((k) => k + 1))
      .catch((err) => {
        console.error(err)
        if (err.response?.status === 500) {
          alert(
            t('cannotDeleteLinked', { item: tag.name }) ||
              'Cannot delete tag — it has linked questions.'
          )
        } else {
          alert(err.message || 'Delete failed')
        }
      })
  }

  const filtered = tags.filter((tag) =>
    tag.name.toLowerCase().includes(search.toLowerCase())
  )

  const sorted = filtered.slice().sort((a, b) => {
    if (sort === 'newest') return new Date(b.createdAt) - new Date(a.createdAt)
    if (sort === 'oldest') return new Date(a.createdAt) - new Date(b.createdAt)
    if (sort === 'name') return a.name.localeCompare(b.name)
    return 0
  })

  const buttonStyle =
    'px-3 py-1 rounded transition-colors duration-300 flex items-center gap-1'

  return (
    <div className="container mx-auto my-4 space-y-4">
      {/* Header */}
      <div className="flex justify-between items-center">
        <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100">
          {t('tags')}
        </h1>
        <button
          onClick={() => navigate('/tags/create')}
          className={`${buttonStyle} bg-blue-600 text-white hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600`}
        >
          {t('createTag')}
        </button>
      </div>

      {/* Search + Sort */}
      <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mt-4">
        <input
          type="text"
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          placeholder={t('search')}
          className="p-2 border border-gray-300 dark:border-gray-600 rounded w-full md:w-1/2 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        <select
          value={sort}
          onChange={(e) => setSort(e.target.value)}
          className="p-2 border border-gray-300 dark:border-gray-600 rounded text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="newest">{t('newest')}</option>
          <option value="oldest">{t('oldest')}</option>
          <option value="name">{t('sortName')}</option>
        </select>
      </div>

      {/* Tag List */}
      {sorted.length === 0 ? (
        <p className="text-gray-500 dark:text-gray-400 mt-4">
          {t('noItemsFound')}
        </p>
      ) : (
        <ul className="space-y-4 list-none mt-4">
          {sorted.map((tag) => {
            const canEditDelete = true
            const hasLinkedQuestions = tag.questionsCount > 0

            return (
              <li
                key={tag.id}
                className="card p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-300 flex justify-between items-start gap-4"
              >
                <div>
                  <div className="font-semibold text-lg">{tag.name}</div>
                  <div className="text-sm text-gray-600 dark:text-gray-300 mt-1">
                    {tag.createdAt && (
                      <div>
                        {t('created')}:{' '}
                        {new Date(tag.createdAt).toLocaleDateString()}
                      </div>
                    )}
                    {tag.updatedAt && (
                      <div>
                        {t('updated')}:{' '}
                        {new Date(tag.updatedAt).toLocaleDateString()}
                      </div>
                    )}
                  </div>
                </div>

                <div className="flex gap-2 flex-shrink-0">
                  {canEditDelete && (
                    <Link
                      to={`/tags/${tag.id}/edit`}
                      title={t('edit')}
                      className={`${buttonStyle} bg-blue-600 text-white hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600`}
                    >
                      ✏️ {t('edit')}
                    </Link>
                  )}
                  {canEditDelete && (
                    <button
                      onClick={() => handleDelete(tag)}
                      title={t('delete')}
                      disabled={hasLinkedQuestions}
                      className={`${buttonStyle} bg-red-600 text-white hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600 ${
                        hasLinkedQuestions
                          ? 'opacity-50 cursor-not-allowed hover:bg-red-600 dark:hover:bg-red-500'
                          : ''
                      }`}
                    >
                      🗑️ {t('delete')}
                    </button>
                  )}
                </div>
              </li>
            )
          })}
        </ul>
      )}

      {/* Pagination */}
      {totalPages > 1 && (
        <div className="flex gap-2 justify-center mt-4">
          {Array.from({ length: totalPages }, (_, i) => (
            <button
              key={i}
              disabled={page === i + 1}
              onClick={() => setPage(i + 1)}
              className={`px-3 py-1 rounded border transition ${
                page === i + 1
                  ? 'bg-blue-600 text-white'
                  : 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-700'
              }`}
            >
              {i + 1}
            </button>
          ))}
        </div>
      )}
    </div>
  )
}
