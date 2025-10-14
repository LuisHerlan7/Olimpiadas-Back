import { supabase, supabaseAdmin } from '../config/database';
import { User, AuthResponse, LoginRequest, RegisterRequest } from '../types/auth';
import jwt from 'jsonwebtoken';
import { config } from '../config/database';
import { createClient } from '@supabase/supabase-js';

export class AuthService {
  async register(data: RegisterRequest): Promise<AuthResponse> {
    try {
      // Registrar usuario en Supabase Auth con todos los datos en options.data
      // El trigger de la base de datos se encargará de crear el perfil y la tabla de rol correspondiente
      const { data: authData, error: authError } = await supabase.auth.signUp({
        email: data.email,
        password: data.password,
        options: {
          data: {
            nombre: data.nombre,
            apellido: data.apellido,
            rol: data.role
          }
        }
      });

      if (authError) {
        return { user: null, token: null, error: authError.message };
      }

      if (!authData.user) {
        return { user: null, token: null, error: 'Error al crear usuario' };
      }

      // Esperar un momento para que el trigger procese los datos
      await new Promise(resolve => setTimeout(resolve, 1000));

      // Obtener el perfil creado por el trigger
      const { data: profileData, error: profileError } = await supabase
        .from('perfiles')
        .select('*')
        .eq('id', authData.user.id)
        .single();

      if (profileError) {
        return { user: null, token: null, error: 'Error al obtener perfil del usuario' };
      }

      // Generar JWT token
      const token = jwt.sign(
        { 
          userId: authData.user.id, 
          email: data.email, 
          role: data.role 
        },
        config.jwtSecret,
        { expiresIn: '24h' }
      );

      // Mapear los datos del perfil al formato esperado
      const userData = {
        id: profileData.id,
        email: profileData.correo,
        nombre: profileData.nombre,
        apellido: profileData.apellido,
        role: data.role,
        created_at: profileData.fecha_creacion,
        updated_at: profileData.fecha_actualizacion
      };

      return {
        user: userData as User,
        token
      };
    } catch (error) {
      return { 
        user: null, 
        token: null, 
        error: error instanceof Error ? error.message : 'Error interno del servidor' 
      };
    }
  }

  async login(data: LoginRequest): Promise<AuthResponse> {
    try {
      // Autenticar con Supabase
      const { data: authData, error: authError } = await supabase.auth.signInWithPassword({
        email: data.email,
        password: data.password,
      });

      if (authError) {
        return { user: null, token: null, error: authError.message };
      }

      if (!authData.user) {
        return { user: null, token: null, error: 'Credenciales inválidas' };
      }

      // SOLUCIÓN TEMPORAL: Usar datos del usuario autenticado
      console.log('Usuario autenticado:', authData.user);
      
      // Obtener datos del usuario desde la sesión de Supabase
      const userEmail = authData.user.email;
      const userName = authData.user.user_metadata?.nombre || 'Usuario';
      const userLastName = authData.user.user_metadata?.apellido || 'Sin Apellido';
      const userRole = authData.user.user_metadata?.rol || 'competidor';
      
      console.log('Datos del usuario:', {
        email: userEmail,
        nombre: userName,
        apellido: userLastName,
        rol: userRole,
        metadata: authData.user.user_metadata
      });
      
      // SOLUCIÓN TEMPORAL: Verificar rol por email
      let finalRole = userRole;
      if (userEmail) {
        if (userEmail.includes('admin') || userEmail.includes('administrador')) {
          finalRole = 'administrador';
          console.log('Usuario detectado como administrador por email');
        } else if (userEmail.includes('encargado')) {
          finalRole = 'encargado';
          console.log('Usuario detectado como encargado por email');
        } else if (userEmail.includes('competidor') || userEmail.includes('olimpista')) {
          finalRole = 'competidor';
          console.log('Usuario detectado como competidor por email');
        }
      }
      
      // Crear objeto de perfil temporal
      const profileData = {
        id: authData.user.id,
        correo: userEmail,
        nombre: userName,
        apellido: userLastName,
        rol: finalRole,
        fecha_creacion: authData.user.created_at,
        fecha_actualizacion: authData.user.updated_at
      };
      
      console.log('Perfil temporal creado:', profileData);

      // Generar JWT token con el rol final
      const token = jwt.sign(
        { 
          userId: authData.user.id, 
          email: profileData.correo, 
          role: finalRole 
        },
        config.jwtSecret,
        { expiresIn: '24h' }
      );

      // Mapear los datos del perfil al formato esperado
      const userData = {
        id: profileData.id,
        email: profileData.correo,
        nombre: profileData.nombre,
        apellido: profileData.apellido,
        role: finalRole,
        created_at: profileData.fecha_creacion,
        updated_at: profileData.fecha_actualizacion
      };

      return {
        user: userData as User,
        token
      };
    } catch (error) {
      console.error('Error en login:', error);
      return { 
        user: null, 
        token: null, 
        error: error instanceof Error ? error.message : 'Error interno del servidor' 
      };
    }
  }

  async logout(token: string): Promise<{ success: boolean; error?: string }> {
    try {
      // Verificar y decodificar el token
      const decoded = jwt.verify(token, config.jwtSecret) as any;
      
      // Cerrar sesión en Supabase
      const { error } = await supabase.auth.signOut();
      
      if (error) {
        return { success: false, error: error.message };
      }

      return { success: true };
    } catch (error) {
      return { 
        success: false, 
        error: error instanceof Error ? error.message : 'Token inválido' 
      };
    }
  }

  async verifyToken(token: string): Promise<{ valid: boolean; user?: User; error?: string }> {
    try {
      const decoded = jwt.verify(token, config.jwtSecret) as any;
      
      // Obtener datos actualizados del usuario
      const { data: profileData, error: profileError } = await supabase
        .from('perfiles')
        .select('*')
        .eq('id', decoded.userId)
        .single();

      if (profileError) {
        return { valid: false, error: 'Usuario no encontrado' };
      }

      return {
        valid: true,
        user: profileData as User
      };
    } catch (error) {
      return { 
        valid: false, 
        error: error instanceof Error ? error.message : 'Token inválido' 
      };
    }
  }

  async getUserById(userId: string): Promise<{ user: User | null; error?: string }> {
    try {
      const { data: profileData, error: profileError } = await supabase
        .from('perfiles')
        .select('*')
        .eq('id', userId)
        .single();

      if (profileError) {
        return { user: null, error: profileError.message };
      }

      return { user: profileData as User };
    } catch (error) {
      return { 
        user: null, 
        error: error instanceof Error ? error.message : 'Error interno del servidor' 
      };
    }
  }
}
