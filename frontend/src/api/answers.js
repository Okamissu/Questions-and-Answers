import { api } from './api'

export const answersApi = (questionId) => {
  if (!questionId) {
    throw new Error('questionId is required to use answersApi')
  }

  return {
    list: ({ page = 1, limit = 10, search = '', sort = '' } = {}) =>
      api
        .get(`/answers/question/${questionId}`, {
          params: { page, limit, search, sort },
        })
        .then((res) => res.data)
        .then((data) => ({
          items: data.items,
          pagination: data.pagination,
        })),

    get: (id) => api.get(`/answers/${id}`).then((res) => res.data),

    create: (data) =>
      api
        .post('/answers', {
          questionId: +questionId,
          content: data.content,
          authorNickname: data.nickname,
          authorEmail: data.email,
        })
        .then((res) => res.data),

    update: (id, data) =>
      api.put(`/answers/${id}`, data).then((res) => res.data),

    delete: (id) => api.delete(`/answers/${id}`).then(() => {}),

    markAsBest: (id) =>
      api.post(`/answers/${id}/mark-best`).then((res) => res.data),
  }
}
