import axios from 'axios'

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: { 'Content-Type': 'application/json' },
})

// Include JWT automatically
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token) config.headers['Authorization'] = `Bearer ${token}`
    return config
  },
  (error) => Promise.reject(error)
)

// Generic wrapper for requests
export const handleRequest = async (promise) => {
  try {
    const res = await promise
    return res.data
  } catch (error) {
    if (error.response) {
      // Backend returned an error
      throw error.response.data
    } else {
      throw error
    }
  }
}

export { api }
