import api from './client';

export const authApi = {
  login: (email, password) => api.post('/login', { email, password }),
  logout: () => api.post('/logout'),
  me: () => api.get('/me'),
};
