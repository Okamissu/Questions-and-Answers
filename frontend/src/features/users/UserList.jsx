import { useState, useEffect } from 'react'
import { useTranslation } from 'react-i18next'
import { usersApi } from '../../api/users'
import UserForm from './UserForm'

export default function UserList({
  currentUser,
  setCurrentUser,
  currentUserRoles = [],
}) {
  const { t } = useTranslation()
  const [users, setUsers] = useState([])
  const [loading, setLoading] = useState(true)
  const [editingUser, setEditingUser] = useState(null)
  const [showForm, setShowForm] = useState(false)

  const fetchUsers = async () => {
    setLoading(true)
    try {
      const res = await usersApi.list()
      setUsers(res.items)
    } catch (err) {
      console.error(err)
      alert(err?.response?.data?.error || 'Failed to fetch users')
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    fetchUsers()
  }, [])

  const handleDelete = async (userId) => {
    if (!window.confirm(t('confirmDelete') || 'Are you sure?')) return

    try {
      await usersApi.delete(userId)
      setUsers((prev) => prev.filter((u) => u.id !== userId))
    } catch (err) {
      console.error(err)
      if (
        err?.response?.status === 400 ||
        err?.response?.data?.error === 'cannotDeleteLinked'
      ) {
        alert(t('cannotDeleteLinked'))
      } else {
        alert(err?.response?.data?.error || t('failedDeleteUser'))
      }
    }
  }

  const handleEdit = (user) => {
    setEditingUser(user)
    setShowForm(true)
  }

  const handleCreate = () => {
    setEditingUser(null)
    setShowForm(true)
  }

  const handleSaved = () => {
    setShowForm(false)
    fetchUsers()
  }

  if (loading) return <p>{t('loading') || 'Loading...'}</p>

  return (
    <div className="container mx-auto my-4 space-y-4">
      <div className="flex justify-between items-center mb-4">
        <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100">
          {t('users')}
        </h1>
        <button
          onClick={handleCreate}
          className="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
        >
          {t('createUser')}
        </button>
      </div>

      {showForm && (
        <UserForm
          user={editingUser}
          currentUser={currentUser}
          setCurrentUser={setCurrentUser}
          onSaved={handleSaved}
          onCancel={() => setShowForm(false)}
        />
      )}

      <ul className="space-y-4">
        {users.map((user) => (
          <li
            key={user.id}
            className="card p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow flex justify-between items-center transition-colors duration-300 hover:bg-gray-100 dark:hover:bg-gray-700"
          >
            <div>
              <div className="font-semibold text-lg">{user.nickname}</div>
              <div className="text-sm text-gray-600 dark:text-gray-300">
                {user.email}
              </div>
              <div className="mt-1 flex flex-wrap">
                {user.roles.map((role) => {
                  const color =
                    role === 'ROLE_ADMIN'
                      ? 'bg-red-600 dark:bg-red-500'
                      : 'bg-blue-600 dark:bg-blue-500'
                  return (
                    <span
                      key={role}
                      className={`${color} text-white text-xs px-2 py-1 rounded-full mr-1`}
                    >
                      {t(role) || role}
                    </span>
                  )
                })}
              </div>
            </div>
            <div className="flex gap-2">
              <button
                onClick={() => handleEdit(user)}
                className="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700"
              >
                {t('edit')}
              </button>
              {currentUserRoles.includes('ROLE_ADMIN') && (
                <button
                  onClick={() => handleDelete(user.id)}
                  className="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700"
                >
                  {t('delete')}
                </button>
              )}
            </div>
          </li>
        ))}
      </ul>
    </div>
  )
}
