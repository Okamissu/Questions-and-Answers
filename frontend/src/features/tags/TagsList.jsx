import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { tagsApi } from '../../api/tags'
import { useTranslation } from 'react-i18next'

export default function TagsList() {
  const { t } = useTranslation()
  const [tags, setTags] = useState([])

  useEffect(() => {
    fetchTags()
  }, [])

  const fetchTags = () => tagsApi.list().then(setTags)

  const handleDelete = (id) => {
    if (window.confirm(t('delete') + '?')) {
      tagsApi.delete(id).then(fetchTags)
    }
  }

  return (
    <div>
      <h1>{t('tags')}</h1>
      <Link to="/tags/create">➕ {t('addNew')}</Link>
      <ul>
        {tags.map((tag) => (
          <li key={tag.id}>
            {tag.name} <Link to={`/tags/${tag.id}/edit`}>{t('edit')}</Link>{' '}
            <button onClick={() => handleDelete(tag.id)}>{t('delete')}</button>
          </li>
        ))}
      </ul>
    </div>
  )
}
