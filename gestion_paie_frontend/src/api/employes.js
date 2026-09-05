import api from './client';

export const employesApi = {
  lister: (params) => api.get('/employes', { params }),
  consulter: (id) => api.get(`/employes/${id}`),
  creer: (data) => api.post('/employes', data),
  modifier: (id, data) => api.patch(`/employes/${id}`, data),
  supprimer: (id) => api.delete(`/employes/${id}`),
  archiver: (id) => api.patch(`/employes/${id}/archiver`),
  anomalies: (id) => api.get(`/employes/${id}/anomalies`),
  bulletins: (id) => api.get(`/employes/${id}/bulletins`),
};
