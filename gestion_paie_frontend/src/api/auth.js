import api from './client';

export const authApi = {
  login: (email, password) => api.post('/login', { email, password }),
  register: (name, email, password, passwordConfirmation, role) =>
    api.post('/register', {
      name,
      email,
      password,
      password_confirmation: passwordConfirmation,
      role,
    }),
  logout: () => api.post('/logout'),
  me: () => api.get('/me'),
};
