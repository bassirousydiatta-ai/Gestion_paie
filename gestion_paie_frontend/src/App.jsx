import { Routes, Route, Navigate } from 'react-router-dom';
import ProtectedRoute from './components/ProtectedRoute';
import Layout from './components/layout/Layout';

import LoginPage from './pages/LoginPage';
import DashboardPage from './pages/DashboardPage';
import EmployesPage from './pages/EmployesPage';
import EmployeDetailPage from './pages/EmployeDetailPage';
import ContratsPage from './pages/ContratsPage';
import PaiesPage from './pages/PaiesPage';
import OrganisationPage from './pages/OrganisationPage';
import CataloguePage from './pages/CataloguePage';
import UtilisateursPage from './pages/UtilisateursPage';
import { NotFoundPage, ForbiddenPage } from './pages/ErrorPages';

export default function App() {
  return (
    <Routes>
      <Route path="/login" element={<LoginPage />} />
      <Route path="/interdit" element={<ForbiddenPage />} />

      <Route
        element={
          <ProtectedRoute>
            <Layout />
          </ProtectedRoute>
        }
      >
        <Route path="/dashboard" element={<DashboardPage />} />
        <Route path="/employes" element={<EmployesPage />} />
        <Route path="/employes/:id" element={<EmployeDetailPage />} />
        <Route path="/contrats" element={<ContratsPage />} />
        <Route path="/paies" element={<PaiesPage />} />

        <Route
          path="/organisation"
          element={
            <ProtectedRoute roles={['admin', 'rh']}>
              <OrganisationPage />
            </ProtectedRoute>
          }
        />
        <Route
          path="/catalogue"
          element={
            <ProtectedRoute roles={['admin', 'comptable']}>
              <CataloguePage />
            </ProtectedRoute>
          }
        />
        <Route
          path="/utilisateurs"
          element={
            <ProtectedRoute roles={['admin']}>
              <UtilisateursPage />
            </ProtectedRoute>
          }
        />
      </Route>

      <Route path="/" element={<Navigate to="/dashboard" replace />} />
      <Route path="*" element={<NotFoundPage />} />
    </Routes>
  );
}
