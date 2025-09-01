import { useState, useEffect, useCallback } from 'react'
import { Link } from 'react-router-dom'
import { getQuestions, deleteQuestion } from '../../api/questions'
import { getCategories } from '../../api/categories'
import { getTags } from '../../api/tags'
import { useTranslation } from 'react-i18next'

export default function QuestionsList() {
  const { t } = useTranslation()

  const [questions, setQuestions] = useState([])
  const [categories, setCategories] = useState([])
  const [tags, setTags] = useState([])

  const [search, setSearch] = useState('')
  const [sort, setSort] = useState('newest')
  const [categoryId, setCategoryId] = useState('')
  const [tagId, setTagId] = useState('')
  const [page, setPage] = useState(1)
  const [totalPages, setTotalPages] = useState(1)

  // Fetch questions
  const fetchQuestions = useCallback(() => {
    getQuestions({ page, search, sort, categoryId, tagId }).then((data) => {
      setQuestions(data.items)
      setTotalPages(data.pagination.totalPages)
    })
  }, [page, search, sort, categoryId, tagId])

  const fetchCategories = useCallback(
    () => getCategories().then(setCategories),
    []
  )
  const fetchTags = useCallback(() => getTags().then(setTags), [])

  useEffect(() => {
    fetchCategories()
    fetchTags()
  }, [fetchCategories, fetchTags])

  useEffect(() => {
    fetchQuestions()
  }, [fetchQuestions])

  const handleDelete = (id) => {
    if (confirm(t('areSure') || 'Are you sure?')) {
      deleteQuestion(id).then(fetchQuestions)
    }
  }

  return (
    <div>
      <h1>{t('questions')}</h1>
      <Link to="/questions/create">➕ {t('addNew')}</Link>

      {/* Filters */}
      <div
        style={{
          margin: '1rem 0',
          display: 'flex',
          flexWrap: 'wrap',
          gap: '0.5rem',
        }}
      >
        <input
          type="text"
          placeholder={t('search') || 'Search...'}
          value={search}
          onChange={(e) => {
            setSearch(e.target.value)
            setPage(1)
          }}
        />

        <select
          value={sort}
          onChange={(e) => {
            setSort(e.target.value)
            setPage(1)
          }}
        >
          <option value="newest">{t('newest') || 'Newest'}</option>
          <option value="oldest">{t('oldest') || 'Oldest'}</option>
        </select>

        <select
          value={categoryId}
          onChange={(e) => {
            setCategoryId(e.target.value)
            setPage(1)
          }}
        >
          <option value="">{t('allCategories') || 'All Categories'}</option>
          {categories.map((cat) => (
            <option key={cat.id} value={cat.id}>
              {cat.name}
            </option>
          ))}
        </select>

        <select
          value={tagId}
          onChange={(e) => {
            setTagId(e.target.value)
            setPage(1)
          }}
        >
          <option value="">{t('allTags') || 'All Tags'}</option>
          {tags.map((tag) => (
            <option key={tag.id} value={tag.id}>
              {tag.name}
            </option>
          ))}
        </select>

        {(categoryId || tagId) && (
          <button
            onClick={() => {
              setCategoryId('')
              setTagId('')
              setPage(1)
            }}
            style={{ backgroundColor: '#eee', border: '1px solid #ccc' }}
          >
            {t('clearFilters') || 'Clear filters'}
          </button>
        )}
      </div>

      {/* Questions List */}
      <ul style={{ listStyle: 'none', padding: 0 }}>
        {questions.map((q) => (
          <li
            key={q.id}
            style={{
              marginBottom: '1rem',
              padding: '0.5rem',
              border: '1px solid #ddd',
              borderRadius: '4px',
              backgroundColor: '#fafafa',
            }}
          >
            <div
              style={{
                display: 'flex',
                justifyContent: 'space-between',
                alignItems: 'center',
              }}
            >
              <Link
                to={`/questions/${q.id}`}
                title={q.title}
                style={{ fontWeight: 'bold', fontSize: '1.1rem' }}
              >
                {q.title.length > 50 ? q.title.slice(0, 50) + '…' : q.title}
              </Link>
              <div>
                <Link to={`/questions/${q.id}/edit`} title={t('edit')}>
                  ✏️
                </Link>{' '}
                <button onClick={() => handleDelete(q.id)} title={t('delete')}>
                  🗑️
                </button>
              </div>
            </div>

            <div
              style={{
                marginTop: '0.25rem',
                fontSize: '0.9rem',
                color: '#555',
              }}
            >
              {q.content.length > 100
                ? q.content.slice(0, 100) + '…'
                : q.content}
            </div>

            {/* Metadata */}
            <div
              style={{
                marginTop: '0.25rem',
                fontSize: '0.8rem',
                color: '#888',
              }}
            >
              {q.author && (
                <span>
                  {t('author') || 'Author'}: {q.author.nickname}
                </span>
              )}
              {q.createdAt && (
                <span style={{ marginLeft: '1rem' }}>
                  {t('createdAt') || 'Created'}:{' '}
                  {new Date(q.createdAt).toLocaleDateString()}
                </span>
              )}
            </div>

            {/* Category */}
            {q.category && (
              <span
                style={{
                  marginLeft: '0.5rem',
                  cursor: 'pointer',
                  color: 'blue',
                  fontSize: '0.85rem',
                }}
                onClick={() => {
                  setCategoryId(q.category.id)
                  setPage(1)
                }}
                title={t('filterByCategory')}
              >
                [{q.category.name}]
              </span>
            )}

            {/* Tags */}
            {q.tags && q.tags.length > 0 && (
              <span style={{ marginLeft: '0.5rem', fontSize: '0.85rem' }}>
                {q.tags.map((tag) => (
                  <span
                    key={tag.id}
                    style={{
                      cursor: 'pointer',
                      color: 'green',
                      marginRight: '0.25rem',
                    }}
                    onClick={() => {
                      setTagId(tag.id)
                      setPage(1)
                    }}
                    title={t('filterByTag')}
                  >
                    #{tag.name}
                  </span>
                ))}
              </span>
            )}
          </li>
        ))}
      </ul>

      {/* Pagination */}
      <div style={{ marginTop: '1rem' }}>
        {Array.from({ length: totalPages }, (_, i) => (
          <button
            key={i}
            disabled={page === i + 1}
            onClick={() => setPage(i + 1)}
            style={{
              marginRight: '0.25rem',
              padding: '0.25rem 0.5rem',
              backgroundColor: page === i + 1 ? '#007bff' : '#f0f0f0',
              color: page === i + 1 ? 'white' : 'black',
              border: '1px solid #ccc',
              borderRadius: '3px',
              cursor: page === i + 1 ? 'default' : 'pointer',
            }}
          >
            {i + 1}
          </button>
        ))}
      </div>
    </div>
  )
}
