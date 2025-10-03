import { Request, Response } from 'express';
import { AuthService } from '../services/authService';
import { LoginRequest, RegisterRequest } from '../types/auth';

const authService = new AuthService();

export class AuthController {
  async register(req: Request, res: Response) {
    try {
      const { email, password, nombre, apellido, role }: RegisterRequest = req.body;

      // Validaciones básicas
      if (!email || !password || !nombre || !apellido || !role) {
        return res.status(400).json({
          success: false,
          message: 'Todos los campos son requeridos'
        });
      }

      // Validar que el rol sea válido
      const validRoles = ['administrador', 'encargado', 'olimpista'];
      if (!validRoles.includes(role)) {
        return res.status(400).json({
          success: false,
          message: 'Rol inválido. Los roles válidos son: administrador, encargado, olimpista'
        });
      }

      // Validar formato de email
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email)) {
        return res.status(400).json({
          success: false,
          message: 'Formato de email inválido'
        });
      }

      // Validar longitud de contraseña
      if (password.length < 6) {
        return res.status(400).json({
          success: false,
          message: 'La contraseña debe tener al menos 6 caracteres'
        });
      }

      const result = await authService.register({
        email,
        password,
        nombre,
        apellido,
        role
      });

      if (result.error) {
        return res.status(400).json({
          success: false,
          message: result.error
        });
      }

      res.status(201).json({
        success: true,
        message: 'Usuario registrado exitosamente',
        data: {
          user: result.user,
          token: result.token
        }
      });
    } catch (error) {
      res.status(500).json({
        success: false,
        message: 'Error interno del servidor'
      });
    }
  }

  async login(req: Request, res: Response) {
    try {
      const { email, password }: LoginRequest = req.body;

      // Validaciones básicas
      if (!email || !password) {
        return res.status(400).json({
          success: false,
          message: 'Email y contraseña son requeridos'
        });
      }

      const result = await authService.login({ email, password });

      if (result.error) {
        return res.status(401).json({
          success: false,
          message: result.error
        });
      }

      res.status(200).json({
        success: true,
        message: 'Inicio de sesión exitoso',
        data: {
          user: result.user,
          token: result.token
        }
      });
    } catch (error) {
      res.status(500).json({
        success: false,
        message: 'Error interno del servidor'
      });
    }
  }

  async logout(req: Request, res: Response) {
    try {
      const authHeader = req.headers.authorization;
      const token = authHeader && authHeader.split(' ')[1];

      if (!token) {
        return res.status(400).json({
          success: false,
          message: 'Token requerido para cerrar sesión'
        });
      }

      const result = await authService.logout(token);

      if (!result.success) {
        return res.status(400).json({
          success: false,
          message: result.error
        });
      }

      res.status(200).json({
        success: true,
        message: 'Sesión cerrada exitosamente'
      });
    } catch (error) {
      res.status(500).json({
        success: false,
        message: 'Error interno del servidor'
      });
    }
  }

  async getProfile(req: Request, res: Response) {
    try {
      if (!req.user) {
        return res.status(401).json({
          success: false,
          message: 'Usuario no autenticado'
        });
      }

      const result = await authService.getUserById(req.user.userId);

      if (result.error) {
        return res.status(404).json({
          success: false,
          message: result.error
        });
      }

      res.status(200).json({
        success: true,
        data: {
          user: result.user
        }
      });
    } catch (error) {
      res.status(500).json({
        success: false,
        message: 'Error interno del servidor'
      });
    }
  }

  async verifyToken(req: Request, res: Response) {
    try {
      if (!req.user) {
        return res.status(401).json({
          success: false,
          message: 'Token inválido'
        });
      }

      res.status(200).json({
        success: true,
        message: 'Token válido',
        data: {
          user: req.user
        }
      });
    } catch (error) {
      res.status(500).json({
        success: false,
        message: 'Error interno del servidor'
      });
    }
  }
}
