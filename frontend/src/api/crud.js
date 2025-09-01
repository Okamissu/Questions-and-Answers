import { api } from './api'

export const createCrud = (endpoint) => ({
  list: (params) => api.get(`/${endpoint}`, { params }).then((res) => res.data),
  get: (id) => api.get(`/${endpoint}/${id}`).then((res) => res.data),
  create: (data) => api.post(`/${endpoint}`, data).then((res) => res.data),
  update: (id, data) =>
    api.put(`/${endpoint}/${id}`, data).then((res) => res.data),
  delete: (id) => api.delete(`/${endpoint}/${id}`).then(() => {}),
})
