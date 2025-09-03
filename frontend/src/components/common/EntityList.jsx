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
}) {
  if (!items || items.length === 0)
    return <p className="text-gray-500">No items found</p>

  return (
    <ul className="space-y-4 list-none p-0">
      {items.map((item) => {
        const isHighlighted = highlightId === item.id

        // Determine permissions
        const canEditDelete =
          item.currentUser?.isAdmin || item.currentUser?.id === item.author?.id

        return (
          <li
            key={item.id}
            className={`bg-white border border-gray-200 rounded-xl p-4 shadow ${
              isHighlighted ? 'ring-2 ring-blue-300' : ''
            }`}
          >
            <div className="flex justify-between items-center">
              <Link
                to={onClickItem ? '#' : `/questions/${item.id}`}
                onClick={onClickItem ? () => onClickItem(item) : undefined}
                className="font-semibold text-lg hover:underline"
                title={item.title}
              >
                {item.title?.length > 50
                  ? item.title?.slice(0, 50) + '…'
                  : item.title}
              </Link>

              <div className="flex gap-2">
                {onEdit && canEditDelete && (
                  <Link
                    to={`/questions/${item.id}/edit`}
                    title="Edit"
                    className="hover:text-blue-600"
                  >
                    ✏️
                  </Link>
                )}
                {onDelete && canEditDelete && (
                  <button
                    onClick={() => onDelete(item.id)}
                    title="Delete"
                    className="hover:text-red-600"
                  >
                    🗑️
                  </button>
                )}
              </div>
            </div>

            <p className="text-gray-600 mt-1">
              {item.content?.length > 100
                ? item.content?.slice(0, 100) + '…'
                : item.content}
            </p>

            {renderMetadata && (
              <div className="text-gray-400 text-sm mt-1">
                {renderMetadata(item)}
              </div>
            )}
            {renderCategory && renderCategory(item)}
            {renderTags && renderTags(item)}
          </li>
        )
      })}
    </ul>
  )
}
