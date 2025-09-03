import { useState, useEffect, useCallback } from 'react'
import { useNavigate } from 'react-router-dom'
import { questionsApi } from '../../api/questions'
import { categoriesApi } from '../../api/categories'
import { tagsApi } from '../../api/tags'
import FiltersBar from '../../components/common/FiltersBar'
import EntityList from '../../components/common/EntityList'
import QuestionForm from './QuestionForm'

export default function QuestionsList({ currentUser }) {
  const navigate = useNavigate()
  const [questions, setQuestions] = useState([])
  const [categories, setCategories] = useState([])
  const [tags, setTags] = useState([])
  const [search, setSearch] = useState('')
  const [sort, setSort] = useState('newest')
  const [categoryId, setCategoryId] = useState('')
  const [tagId, setTagId] = useState('')
  const [page, setPage] = useState(1)
  const [totalPages, setTotalPages] = useState(1)
  const [refreshKey, setRefreshKey] = useState(0) // triggers refetch & remounts form

  // Fetch questions with filters
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
  }, [page, search, sort, categoryId, tagId, currentUser])

  // Fetch categories and tags once
  useEffect(() => {
    categoriesApi.list({}, true).then((res) => setCategories(res.items))
    tagsApi.list({}, true).then((res) => setTags(res.items))
  }, [])

  // Refetch questions when filters, page, refreshKey, or currentUser change
  useEffect(() => {
    fetchQuestions()
  }, [fetchQuestions, refreshKey, currentUser])

  // Force remount QuestionForm on login/logout
  useEffect(() => {
    setRefreshKey((k) => k + 1)
  }, [currentUser])

  const handleDelete = (id) => {
    if (confirm('Are you sure?')) {
      questionsApi.delete(id).then(() => setRefreshKey((k) => k + 1))
    }
  }

  const handleEdit = (id) => {
    navigate(`/questions/${id}/edit`)
  }

  return (
    <div>
      <h1 className="text-2xl font-bold mb-2">Questions</h1>

      {currentUser ? (
        <QuestionForm
          key={`question-form-${refreshKey}`} // remount on login/logout or refresh
          onSuccess={() => setRefreshKey((k) => k + 1)}
        />
      ) : (
        <p className="text-gray-500 mb-4">Log in to post a question</p>
      )}

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

      <EntityList
        items={questions}
        currentUser={currentUser}
        onDelete={handleDelete}
        onEdit={handleEdit}
        renderMetadata={(q) => (
          <>
            {q.author && <>Author: {q.author.nickname} </>}
            {q.createdAt && (
              <> | Created: {new Date(q.createdAt).toLocaleDateString()}</>
            )}
          </>
        )}
        renderCategory={(q) =>
          q.category ? (
            <span className="text-blue-600 cursor-pointer text-sm ml-1">
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
                  className="text-green-600 cursor-pointer text-sm mr-1"
                >
                  #{tag.name}
                </span>
              ))}
            </span>
          ) : null
        }
      />

      {/* Pagination */}
      <div className="mt-4 flex gap-1">
        {Array.from({ length: totalPages }, (_, i) => (
          <button
            key={i}
            disabled={page === i + 1}
            onClick={() => setPage(i + 1)}
            className={`px-2 py-1 border rounded ${
              page === i + 1
                ? 'bg-blue-600 text-white'
                : 'bg-gray-100 text-black hover:bg-gray-200'
            }`}
          >
            {i + 1}
          </button>
        ))}
      </div>
    </div>
  )
}
