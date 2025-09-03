import { createCrud } from './crud'
import { api } from './api'

export const usersApi = {
  ...createCrud('users'),

  /**
   * Fetch the currently authenticated user.
   * Works without needing the ID in the token.
   */
  me: async () => {
    try {
      const res = await api.get('/users/me')
      return res.data
    } catch (err) {
      return null
    }
  },
}
