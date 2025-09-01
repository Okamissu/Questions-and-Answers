import { api } from './api'

export const answersApi = (questionId) => {
  if (!questionId) {
    throw new Error('questionId is required to list answers')
  }

  return {
    // List answers for a question
    list: (params = {}) =>
      api
        .get(`/answers/question/${questionId}`, { params })
        .then((res) => res.data),

    // Get a single answer by ID
    get: (id) => api.get(`/answers/${id}`).then((res) => res.data),

    // Create a new answer
    create: (data) => api.post('/answers', data).then((res) => res.data),

    // Update an answer
    update: (id, data) =>
      api.put(`/answers/${id}`, data).then((res) => res.data),

    // Delete an answer
    delete: (id) => api.delete(`/answers/${id}`).then(() => {}),

    // Mark an answer as best
    markAsBest: (id) =>
      api.post(`/answers/${id}/mark-best`).then((res) => res.data),
  }
}
