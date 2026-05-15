import api from '@/api/axios'

export default {
  getLawyerRequests() {
    return api.get('/lawyer-requests')
  },

  getLawyerRequest(id) {
    return api.get(`/lawyer-requests/${id}`)
  },

  uploadCorrectedContract(id, formData) {
    return api.post(`/lawyer-requests/${id}/upload`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
  },

  completeRequest(id, data) {
    return api.post(`/lawyer-requests/${id}/complete`, data)
  },

  downloadCorrectedContract(id) {
    return api.get(`/lawyer-requests/${id}/download`, {
      responseType: 'blob',
    })
  },

  getLawyers() {
    return api.get('/lawyers')
  },

  getMyLawyerRequests() {
    return api.get('/contract-generations/request-reviews')
  },
}
