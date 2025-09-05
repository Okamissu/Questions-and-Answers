import { useState, useEffect, useCallback } from 'react'
import { useNavigate } from 'react-router-dom'
import { useTranslation } from 'react-i18next'
import { questionsApi } from '../../api/questions'
import { categoriesApi } from '../../api/categories'
import { tagsApi } from '../../api/tags'
import FiltersBar from '../../components/common/FiltersBar'
import EntityList from '../../components/common/EntityList'
import QuestionForm from './QuestionForm'

export default function QuestionsList({ currentUser }) {
  const navigate = useNavigate()
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
  const [refreshKey, setRefreshKey] = useState(0)
  const [showForm, setShowForm] = useState(false)

  const fetchQuestions = useCallback(() => {
    questionsApi
      .list({ page, search, sort, categoryId, tagId })
      .then((data) => {
        setQuestions(
          data.items.map((q) => ({
            ...q,
            currentUser,
          }))
        )
        setTotalPages(data.pagination.totalPages)
      })
      .catch((err) => console.error('Failed to fetch questions:', err))
  }, [page, search, sort, categoryId, tagId, currentUser])

  useEffect(() => {
    categoriesApi.list({}, true).then((res) => setCategories(res.items))
    tagsApi.list({}, true).then((res) => setTags(res.items))
  }, [])

  useEffect(() => {
    fetchQuestions()
  }, [fetchQuestions, refreshKey, currentUser])

  useEffect(() => {
    setRefreshKey((k) => k + 1)
  }, [currentUser])

  const handleDelete = (id) => {
    if (confirm(t('confirmDelete') || 'Are you sure?')) {
      questionsApi.delete(id).then(() => setRefreshKey((k) => k + 1))
    }
  }

  const handleEdit = (id) => {
    navigate(`/questions/${id}/edit`)
  }

  const filtered = questions.filter((q) => {
    const matchesSearch = q.title.toLowerCase().includes(search.toLowerCase())
    const matchesCategory = categoryId ? q.category?.id === categoryId : true
    const matchesTag = tagId ? q.tags?.some((tag) => tag.id === tagId) : true
    return matchesSearch && matchesCategory && matchesTag
  })

  const sorted = filtered.slice().sort((a, b) => {
    if (sort === 'newest') return new Date(b.createdAt) - new Date(a.createdAt)
    if (sort === 'oldest') return new Date(a.createdAt) - new Date(b.createdAt)
    if (sort === 'name') return a.title.localeCompare(b.title)
    return 0
  })

  return (
    <div className="space-y-6">
      {/* Heading + Create Button */}
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100">
          {t('questions')}
        </h1>
        {currentUser && (
          <button
            onClick={() => setShowForm((prev) => !prev)}
            className="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition"
          >
            {showForm ? t('cancel') : t('createQuestion')}
          </button>
        )}
      </div>

      {/* Question form */}
      {showForm && currentUser && (
        <QuestionForm
          key={`question-form-${refreshKey}`}
          onSuccess={() => {
            setShowForm(false)
            setRefreshKey((k) => k + 1)
          }}
        />
      )}

      {/* Filters */}
      <FiltersBar
        search={search}
        setSearch={setSearch}
        sort={sort}
        setSort={setSort}
        categories={categories}
        categoryId={categoryId}
        setCategoryId={setCategoryId}
        tags={tags}
        tagId={tagId}
        setTagId={setTagId}
        onClear={() => {
          setCategoryId('')
          setTagId('')
          setPage(1)
        }}
      />

      {/* Question list */}
      <EntityList
        items={sorted}
        currentUser={currentUser}
        onDelete={handleDelete}
        onEdit={handleEdit}
        renderMetadata={(q) => (
          <div className="text-sm text-gray-600 dark:text-gray-300">
            {q.author && (
              <>
                {t('author')}: {q.author.nickname}{' '}
              </>
            )}
            {q.createdAt && (
              <>
                | {t('created')}: {new Date(q.createdAt).toLocaleDateString()}
              </>
            )}
          </div>
        )}
        renderCategory={(q) =>
          q.category ? (
            <span className="text-blue-600 dark:text-blue-400 cursor-pointer text-sm ml-1">
              [{q.category.name}]
            </span>
          ) : null
        }
        renderTags={(q) =>
          q.tags?.length > 0 ? (
            <span className="ml-1">
              {q.tags.map((tag) => (
                <span
                  key={tag.id}
                  className="text-green-600 dark:text-green-400 cursor-pointer text-sm mr-1"
                >
                  #{tag.name}
                </span>
              ))}
            </span>
          ) : null
        }
        editTitle={t('edit')}
        deleteTitle={t('delete')}
      />

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
