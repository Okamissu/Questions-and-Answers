import { api } from './api'
import { jwtDecode } from 'jwt-decode'

// Get current user from token
export const getCurrentUser = () => {
  const token = localStorage.getItem('token')
  if (!token) return null

  try {
    const decoded = jwtDecode(token)
    return {
      id: decoded.id || decoded.user_id,
      roles: decoded.roles || [],
      isAdmin: decoded.roles.includes('ROLE_ADMIN'),
    }
  } catch (error) {
    console.error('Invalid token', error)
    return null
  }
}

// Login
export const loginUser = async (data, setCurrentUser) => {
  const res = await api.post('/login', data)
  localStorage.setItem('token', res.data.token)
  if (setCurrentUser) setCurrentUser(getCurrentUser())
  return res.data
}

// Logout
export const logoutUser = (setCurrentUser) => {
  localStorage.removeItem('token')
  if (setCurrentUser) setCurrentUser(null)
}

// Logout + redirect helper
export const logoutAndRedirect = (
  setCurrentUser,
  navigate,
  path = '/login'
) => {
  logoutUser(setCurrentUser)
  navigate(path)
}
