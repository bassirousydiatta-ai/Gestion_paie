import api from './client';

export const contratsApi = {
  lister: (params) => api.get('/contrats', { params }),
  creer: (data) => api.post('/contrats', data),
  modifier: (id, data) => api.patch(`/contrats/${id}`, data),
  supprimer: (id) => api.delete(`/contrats/${id}`),
};

export const departementsApi = {
  lister: () => api.get('/departements'),
  creer: (data) => api.post('/departements', data),
};

export const postesApi = {
  lister: (params) => api.get('/postes', { params }),
  creer: (data) => api.post('/postes', data),
};
