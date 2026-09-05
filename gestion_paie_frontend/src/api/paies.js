import api from './client';

export const paiesApi = {
  lister: (params) => api.get('/paies', { params }),
  consulter: (id) => api.get(`/paies/${id}`),
  calculer: (data) => api.post('/paies/calculer', data),
  valider: (id) => api.post(`/paies/${id}/valider`),
  anomalies: (id) => api.get(`/paies/${id}/anomalies`),
  supprimer: (id) => api.delete(`/paies/${id}`),
};

export const bulletinsApi = {
  consulter: (id) => api.get(`/bulletins/${id}`),
  telechargerUrl: (id) => `${api.defaults.baseURL}/bulletins/${id}/pdf`,
  regenerer: (paieId) => api.post(`/paies/${paieId}/bulletin`),
  supprimer: (id) => api.delete(`/bulletins/${id}`),
};

export const primesApi = {
  lister: () => api.get('/primes'),
  creer: (data) => api.post('/primes', data),
  supprimer: (id) => api.delete(`/primes/${id}`),
};

export const cotisationsApi = {
  lister: () => api.get('/cotisations'),
  creer: (data) => api.post('/cotisations', data),
  supprimer: (id) => api.delete(`/cotisations/${id}`),
};
