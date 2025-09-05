import React from 'react'
import { Link } from 'react-router-dom'

export default function EntityList({
  items,
  onDelete,
  onEdit,
  onClickItem,
  renderMetadata,
  renderCategory,
  renderTags,
  highlightId,
  currentUser,
}) {
  if (!items || items.length === 0)
    return <p className="text-gray-500 dark:text-gray-400">No items found</p>

  const isAdmin = currentUser?.roles?.includes('ROLE_ADMIN')

  return (
    <ul className="container space-y-4 list-none">
      {items.map((item) => {
        const isHighlighted = highlightId === item.id
        const canEditDelete =
          isAdmin || item.currentUser?.id === item.author?.id

        return (
          <li
            key={item.id}
            className={`card p-4 ${
              isHighlighted ? 'ring-2 ring-blue-300 dark:ring-blue-500' : ''
            } hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-300`}
          >
            <div className="flex justify-between items-start gap-4">
              <Link
                to={onClickItem ? '#' : `/questions/${item.id}`}
                onClick={onClickItem ? () => onClickItem(item) : undefined}
                className="font-semibold text-lg hover:underline transition-colors duration-300"
                title={item.title}
              >
                {item.title?.length > 50
                  ? item.title.slice(0, 50) + '…'
                  : item.title}
              </Link>

              <div className="flex gap-2 flex-shrink-0">
                {onEdit && canEditDelete && (
                  <Link
                    to={`/questions/${item.id}/edit`}
                    title="Edit"
                    className="flex items-center gap-1 px-3 py-1 rounded bg-blue-600 text-white hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 transition-colors duration-300"
                  >
                    ✏️ <span>Edit</span>
                  </Link>
                )}
                {onDelete && canEditDelete && (
                  <button
                    onClick={() => onDelete(item.id)}
                    title="Delete"
                    className="flex items-center gap-1 px-3 py-1 rounded bg-red-600 text-white hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600 transition-colors duration-300"
                  >
                    🗑️ <span>Delete</span>
                  </button>
                )}
              </div>
            </div>

            {item.content && (
              <p className="mt-2">
                {item.content.length > 100
                  ? item.content.slice(0, 100) + '…'
                  : item.content}
              </p>
            )}

            {renderMetadata && (
              <div className="text-sm mt-1">{renderMetadata(item)}</div>
            )}

            {renderCategory && (
              <div className="mt-1">{renderCategory(item)}</div>
            )}
            {renderTags && <div className="mt-1">{renderTags(item)}</div>}
          </li>
        )
      })}
    </ul>
  )
}
