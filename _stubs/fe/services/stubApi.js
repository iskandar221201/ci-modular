import api from '@/shared/services/api'

export const {{MODULES_LOWER}}Api = {
  list: (params) => api.get('/{{MODULES_LOWER}}', { params }),
  show: (id) => api.get(`/{{MODULES_LOWER}}/${id}`),
  create: (data) => api.post('/{{MODULES_LOWER}}', data),
  update: (id, data) => api.put(`/{{MODULES_LOWER}}/${id}`, data),
  remove: (id) => api.delete(`/{{MODULES_LOWER}}/${id}`),
}
