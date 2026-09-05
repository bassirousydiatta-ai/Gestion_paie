import api from './client';

export const dashboardApi = {
  statistiques: () => api.get('/dashboard/statistiques'),
};

export const usersApi = {
  lister: () => api.get('/users'),
  creer: (data) => api.post('/users', data),
  modifier: (id, data) => api.patch(`/users/${id}`, data),
  supprimer: (id) => api.delete(`/users/${id}`),
};

export const exportsApi = {
  paiesExcelPath: (params = {}) =>
    `/exports/paies/xlsx?${new URLSearchParams(params).toString()}`,
  paiesPdfPath: (params = {}) =>
    `/exports/paies/pdf?${new URLSearchParams(params).toString()}`,
  employesExcelPath: (params = {}) =>
    `/exports/employes/xlsx?${new URLSearchParams(params).toString()}`,
};
