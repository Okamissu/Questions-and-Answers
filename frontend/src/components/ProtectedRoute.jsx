import { Link } from 'react-router-dom'
import { useTranslation } from 'react-i18next'

export default function ProtectedRoute({
  children,
  currentUser,
  adminOnly = false,
}) {
  const { t } = useTranslation()
  const isAdmin = currentUser?.roles?.includes('ROLE_ADMIN')

  if (currentUser === undefined)
    return (
      <div className="flex justify-center items-center h-[60vh]">
        <p className="text-gray-700 dark:text-gray-300 text-lg">
          {t('loading')}
        </p>
      </div>
    )

  if (!currentUser)
    return (
      <div className="flex justify-center items-center h-[60vh]">
        <div className="bg-white dark:bg-gray-800 p-6 rounded-lg shadow text-center max-w-sm">
          <p className="text-red-600 dark:text-red-400 font-semibold text-lg mb-4">
            {t('notAuthenticated')}
          </p>
          <p className="text-gray-700 dark:text-gray-300 mb-4">
            {t('loginRequired')}
          </p>
          <Link
            to="/questions"
            className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition"
          >
            {t('backToQuestions')}
          </Link>
        </div>
      </div>
    )

  if (adminOnly && !isAdmin) {
    return (
      <div className="flex justify-center items-center h-[60vh]">
        <div className="bg-white dark:bg-gray-800 p-6 rounded-lg shadow text-center max-w-sm">
          <p className="text-red-600 dark:text-red-400 font-semibold text-lg mb-4">
            {t('notAuthorized')}
          </p>
          <p className="text-gray-700 dark:text-gray-300 mb-4">
            {t('permissionRequired')}
          </p>
          <Link
            to="/questions"
            className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition"
          >
            {t('backToQuestions')}
          </Link>
        </div>
      </div>
    )
  }

  return children
}
