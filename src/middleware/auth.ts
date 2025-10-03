import { Request, Response, NextFunction } from 'express';
import { AuthService } from '../services/authService';
import { JwtPayload } from '../types/auth';

// Extender la interfaz Request para incluir user
declare global {
  namespace Express {
    interface Request {
      user?: JwtPayload;
    }
  }
}

const authService = new AuthService();

export const authenticateToken = async (req: Request, res: Response, next: NextFunction) => {
  try {
    const authHeader = req.headers.authorization;
    const token = authHeader && authHeader.split(' ')[1]; // Bearer TOKEN

    if (!token) {
      return res.status(401).json({ 
        success: false, 
        message: 'Token de acceso requerido' 
      });
    }

    const result = await authService.verifyToken(token);
    
    if (!result.valid || !result.user) {
      return res.status(403).json({ 
        success: false, 
        message: result.error || 'Token inválido' 
      });
    }

    // Agregar información del usuario al request
    req.user = {
      userId: result.user.id,
      email: result.user.email,
      role: result.user.role,
      iat: 0,
      exp: 0
    };

    next();
  } catch (error) {
    return res.status(500).json({ 
      success: false, 
      message: 'Error interno del servidor' 
    });
  }
};

export const authorizeRoles = (...roles: string[]) => {
  return (req: Request, res: Response, next: NextFunction) => {
    if (!req.user) {
      return res.status(401).json({ 
        success: false, 
        message: 'Usuario no autenticado' 
      });
    }

    if (!roles.includes(req.user.role)) {
      return res.status(403).json({ 
        success: false, 
        message: 'No tienes permisos para acceder a este recurso' 
      });
    }

    next();
  };
};

// Middleware específico para cada rol
export const requireAdmin = authorizeRoles('administrador');
export const requireEncargado = authorizeRoles('encargado', 'administrador');
export const requireOlimpista = authorizeRoles('olimpista', 'encargado', 'administrador');
