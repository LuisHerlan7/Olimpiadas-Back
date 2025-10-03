import { Router } from 'express';
import { authenticateToken, requireAdmin, requireEncargado, requireOlimpista } from '../middleware/auth';

const router = Router();

// Aplicar autenticación a todas las rutas
router.use(authenticateToken);

// Rutas específicas para administradores
router.get('/admin/dashboard', requireAdmin, (req, res) => {
  res.json({
    success: true,
    message: 'Dashboard de administrador',
    data: {
      user: req.user,
      message: 'Bienvenido al panel de administración'
    }
  });
});

// Rutas para encargados (y administradores)
router.get('/encargado/dashboard', requireEncargado, (req, res) => {
  res.json({
    success: true,
    message: 'Dashboard de encargado',
    data: {
      user: req.user,
      message: 'Bienvenido al panel de encargado'
    }
  });
});

// Rutas para olimpistas (y todos los roles)
router.get('/olimpista/dashboard', requireOlimpista, (req, res) => {
  res.json({
    success: true,
    message: 'Dashboard de olimpista',
    data: {
      user: req.user,
      message: 'Bienvenido al panel de olimpista'
    }
  });
});

// Ruta para obtener información del usuario actual
router.get('/me', (req, res) => {
  res.json({
    success: true,
    data: {
      user: req.user,
      role: req.user?.role,
      message: `Usuario autenticado como ${req.user?.role}`
    }
  });
});

// Ruta para verificar permisos específicos
router.get('/check-permissions', (req, res) => {
  const userRole = req.user?.role;
  const permissions = {
    canManageUsers: userRole === 'administrador',
    canManageEvents: ['administrador', 'encargado'].includes(userRole || ''),
    canViewResults: ['administrador', 'encargado', 'olimpista'].includes(userRole || ''),
    canCreateEvents: userRole === 'administrador',
    canEditProfile: true // Todos pueden editar su perfil
  };

  res.json({
    success: true,
    data: {
      user: req.user,
      permissions
    }
  });
});

export default router;
