import { api } from './api'

export const createCrud = (endpoint) => ({
  list: async (params = {}, fetchAll = false) => {
    const formattedParams = {}

    // Convert params ending with "Id" to numbers, skip empty/null
    Object.entries(params).forEach(([key, value]) => {
      if (value === '' || value === null || value === undefined) return
      formattedParams[key] = key.endsWith('Id') ? Number(value) : value
    })

    if (!fetchAll) {
      // normal single page fetch
      const res = await api.get(`/${endpoint}`, { params: formattedParams })
      return res.data
    }

    // fetch all pages
    let allItems = []
    let page = 1
    let totalPages = 1

    do {
      const res = await api.get(`/${endpoint}`, {
        params: { ...formattedParams, page },
      })
      allItems = allItems.concat(res.data.items)
      totalPages = res.data.pagination.totalPages
      page++
    } while (page <= totalPages)

    return { items: allItems, pagination: { totalPages, page: 1 } }
  },

  get: (id) => api.get(`/${endpoint}/${id}`).then((res) => res.data),
  create: (data) => api.post(`/${endpoint}`, data).then((res) => res.data),
  update: (id, data) =>
    api.put(`/${endpoint}/${id}`, data).then((res) => res.data),
  delete: (id) => api.delete(`/${endpoint}/${id}`).then(() => {}),
})
