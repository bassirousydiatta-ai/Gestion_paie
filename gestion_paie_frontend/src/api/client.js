import axios from 'axios';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
});

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      if (window.location.pathname !== '/login') {
        window.location.href = '/login';
      }
    }
    return Promise.reject(error);
  }
);

/**
 * Télécharge un fichier protégé par le token Bearer (PDF, Excel...).
 * Un simple <a href="..."> ne fonctionnerait pas car le header
 * Authorization ne serait pas envoyé par le navigateur.
 */
export async function downloadFile(url, nomFichierParDefaut = 'fichier') {
  const response = await api.get(url, { responseType: 'blob' });

  const contentDisposition = response.headers['content-disposition'];
  const match = contentDisposition?.match(/filename="?([^"]+)"?/);
  const nomFichier = match ? match[1] : nomFichierParDefaut;

  const blobUrl = window.URL.createObjectURL(new Blob([response.data]));
  const lien = document.createElement('a');
  lien.href = blobUrl;
  lien.download = nomFichier;
  document.body.appendChild(lien);
  lien.click();
  lien.remove();
  window.URL.revokeObjectURL(blobUrl);
}

export default api;
