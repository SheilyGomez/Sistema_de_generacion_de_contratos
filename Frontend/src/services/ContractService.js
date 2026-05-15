import api from '@/api/axios'

export default {
  uploadVerification(formData) {
    return api.post('/contract-verifications', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
  },

  getVerifications(params = {}) {
    return api.get('/contract-verifications', { params })
  },

  getVerification(id) {
    return api.get(`/contract-verifications/${id}`)
  },

  downloadVerification(id) {
    return api.get(`/contract-verifications/${id}/download`, {
      responseType: 'blob',
    })
  },

  // --- Contract Generations ---

  createGeneration(data) {
    return api.post('/contract-generations', data)
  },

  getGenerations(params = {}) {
    return api.get('/contract-generations', { params })
  },

  getGeneration(id) {
    return api.get(`/contract-generations/${id}`)
  },

  downloadGeneration(id) {
    return api.get(`/contract-generations/${id}/download`, {
      responseType: 'blob',
    })
  },

  requestReview(generationId, data) {
    return api.post(`/contract-generations/${generationId}/request-review`, data)
  },

  getMyLawyerRequests() {
    return api.get('/contract-generations/request-reviews')
  },
}
