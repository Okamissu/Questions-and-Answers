import React from 'react'

export default function FiltersBar({
  search,
  setSearch,
  sort,
  setSort,
  categories = [],
  categoryId,
  setCategoryId,
  tags = [],
  tagId,
  setTagId,
  onClear,
}) {
  return (
    <div className="flex flex-wrap gap-2 my-4">
      <input
        type="text"
        value={search}
        onChange={(e) => {
          setSearch(e.target.value)
        }}
        placeholder="Search..."
        className="border rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
      />

      <select
        value={sort}
        onChange={(e) => setSort(e.target.value)}
        className="border rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
        <option value="newest">Newest</option>
        <option value="oldest">Oldest</option>
      </select>

      {categories.length > 0 && (
        <select
          value={categoryId}
          onChange={(e) => setCategoryId(Number(e.target.value))}
          className="border rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="">All Categories</option>
          {categories.map((cat) => (
            <option key={cat.id} value={cat.id}>
              {cat.name}
            </option>
          ))}
        </select>
      )}

      {tags.length > 0 && (
        <select
          value={tagId}
          onChange={(e) => setTagId(Number(e.target.value) || '')} // '' for "All Tags"
          className="border rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="">All Tags</option>
          {tags.map((tag) => (
            <option key={tag.id} value={tag.id}>
              {tag.name}
            </option>
          ))}
        </select>
      )}

      {(categoryId || tagId) && (
        <button
          onClick={onClear}
          className="bg-gray-200 border border-gray-400 rounded px-3 py-1 hover:bg-gray-300"
        >
          Clear filters
        </button>
      )}
    </div>
  )
}
