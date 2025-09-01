import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { getCategories, deleteCategory } from '../../api/categories'
import { useTranslation } from 'react-i18next'

export default function CategoriesList({ currentUser }) {
  const { t } = useTranslation()
  const [categories, setCategories] = useState([])

  useEffect(() => {
    fetchCategories()
  }, [])

  const fetchCategories = () => {
    getCategories().then(setCategories)
  }

  const handleDelete = (id) => {
    if (window.confirm(t('delete') + '?')) {
      deleteCategory(id).then(fetchCategories)
    }
  }

  return (
    <div>
      <h1>{t('categories')}</h1>
      {currentUser?.isAdmin && (
        <Link to="/categories/create">➕ {t('addNew')}</Link>
      )}
      <ul>
        {categories.map((cat) => (
          <li key={cat.id}>
            {cat.name}{' '}
            {currentUser?.isAdmin && (
              <>
                <Link to={`/categories/${cat.id}/edit`}>{t('edit')}</Link>{' '}
                <button onClick={() => handleDelete(cat.id)}>
                  {t('delete')}
                </button>
              </>
            )}
          </li>
        ))}
      </ul>
    </div>
  )
}
