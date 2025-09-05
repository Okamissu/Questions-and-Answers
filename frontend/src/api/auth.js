import { api } from './api'
import { jwtDecode } from 'jwt-decode'
import { usersApi } from './users'

// ✅ Check if token is expired
export const isTokenExpired = (token) => {
  if (!token) return true

  try {
    const decoded = jwtDecode(token)
    const now = Math.floor(Date.now() / 1000) // current time in seconds
    return decoded.exp < now
  } catch (error) {
    console.error('Invalid token', error)
    return true
  }
}

// ⚠️ Optional fallback: decode minimal info from token
export const getCurrentUserFromToken = () => {
  const token = localStorage.getItem('token')
  if (!token || isTokenExpired(token)) return null

  try {
    const decoded = jwtDecode(token)
    return {
      id: decoded.id || decoded.user_id,
      roles: decoded.roles || [],
      isAdmin: decoded.roles?.includes('ROLE_ADMIN') || false,
    }
  } catch (error) {
    console.error('Invalid token', error)
    return null
  }
}

// ✅ Login (store token + fetch full user)
export const loginUser = async (data, setCurrentUser) => {
  const res = await api.post('/login', data)
  localStorage.setItem('token', res.data.token)

  if (setCurrentUser) {
    try {
      const user = await usersApi.me()
      setCurrentUser(user)
    } catch (err) {
      console.error('Failed to fetch current user after login', err)
      setCurrentUser(getCurrentUserFromToken()) // fallback to decoded
    }
  }

  return res.data
}

// ✅ Logout (clear token + reset state)
export const logoutUser = (setCurrentUser) => {
  localStorage.removeItem('token')
  if (setCurrentUser) setCurrentUser(null)
}

// ✅ Logout + redirect helper
export const logoutAndRedirect = (
  setCurrentUser,
  navigate,
  path = '/login'
) => {
  logoutUser(setCurrentUser)
  navigate(path)
}
