import { Navigate } from 'react-router-dom'
import { isAuthenticated, getCurrentUser } from '../api/auth'

export default function ProtectedRoute({ children, adminOnly = false }) {
  const user = getCurrentUser()

  if (!isAuthenticated()) {
    // Not logged in
    return <Navigate to="/login" replace />
  }

  if (adminOnly && !user?.isAdmin) {
    // Logged in but not admin
    return <Navigate to="/dashboard" replace />
  }

  return children
}
