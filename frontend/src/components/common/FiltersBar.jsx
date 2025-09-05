import React from 'react'
import { useTranslation } from 'react-i18next'

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
  const { t } = useTranslation()

  return (
    <div className="container flex flex-wrap gap-2 md:gap-4 my-4 py-2 items-center">
      {/* Search */}
      <input
        type="text"
        value={search}
        onChange={(e) => setSearch(e.target.value)}
        placeholder={t('search')}
        className="input flex-1 min-w-[150px]"
        aria-label={t('search')}
      />

      {/* Sort */}
      <select
        value={sort}
        onChange={(e) => setSort(e.target.value)}
        className="input min-w-[120px]"
        aria-label={t('sort')}
      >
        <option value="newest">{t('newest')}</option>
        <option value="oldest">{t('oldest')}</option>
        <option value="name">{t('sortName')}</option>
      </select>

      {/* Categories */}
      {categories.length > 0 && (
        <select
          value={categoryId}
          onChange={(e) => setCategoryId(Number(e.target.value) || '')}
          className="input min-w-[150px]"
          aria-label={t('category')}
        >
          <option value="">{t('allCategories')}</option>
          {categories.map((cat) => (
            <option key={cat.id} value={cat.id}>
              {cat.name}
            </option>
          ))}
        </select>
      )}

      {/* Tags */}
      {tags.length > 0 && (
        <select
          value={tagId}
          onChange={(e) => setTagId(Number(e.target.value) || '')}
          className="input min-w-[150px]"
          aria-label={t('tag')}
        >
          <option value="">{t('allTags')}</option>
          {tags.map((tag) => (
            <option key={tag.id} value={tag.id}>
              {tag.name}
            </option>
          ))}
        </select>
      )}

      {/* Clear filters */}
      {(categoryId || tagId) && (
        <button
          onClick={onClear}
          className="button bg-gray-200 dark:bg-gray-700 text-black dark:text-white hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors duration-300"
        >
          {t('clearFilters')}
        </button>
      )}
    </div>
  )
}
