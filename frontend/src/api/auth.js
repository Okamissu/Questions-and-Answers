import { api } from './api'
import jwt_decode from 'jwt-decode'

// Login / logout
export const loginUser = (data) =>
  api.post('/login_check', data).then((res) => {
    localStorage.setItem('token', res.data.token)
    return res.data
  })

export const logoutUser = () => localStorage.removeItem('token')

// Current user helper
export const getCurrentUser = () => {
  const token = localStorage.getItem('token')
  if (!token) return null
  try {
    const decoded = jwt_decode(token)
    return {
      id: decoded.id || decoded.user_id,
      roles: decoded.roles || [],
      isAdmin: decoded.roles?.includes('ROLE_ADMIN'),
    }
  } catch (error) {
    console.error('Invalid token', error)
    return null
  }
}

export const isAdmin = () => getCurrentUser()?.roles.includes('ROLE_ADMIN')
export const isAuthenticated = () => !!getCurrentUser()
