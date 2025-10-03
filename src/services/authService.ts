import { supabase } from '../config/database';
import { User, AuthResponse, LoginRequest, RegisterRequest } from '../types/auth';
import jwt from 'jsonwebtoken';
import { config } from '../config/database';

export class AuthService {
  async register(data: RegisterRequest): Promise<AuthResponse> {
    try {
      // Registrar usuario en Supabase Auth
      const { data: authData, error: authError } = await supabase.auth.signUp({
        email: data.email,
        password: data.password,
      });

      if (authError) {
        return { user: null, token: null, error: authError.message };
      }

      if (!authData.user) {
        return { user: null, token: null, error: 'Error al crear usuario' };
      }

      // Crear perfil de usuario en la tabla profiles
      const { data: profileData, error: profileError } = await supabase
        .from('profiles')
        .insert({
          id: authData.user.id,
          email: data.email,
          nombre: data.nombre,
          apellido: data.apellido,
          role: data.role,
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString()
        })
        .select()
        .single();

      if (profileError) {
        // Si hay error al crear el perfil, eliminar el usuario de auth
        await supabase.auth.admin.deleteUser(authData.user.id);
        return { user: null, token: null, error: profileError.message };
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

      return {
        user: profileData as User,
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

      // Obtener perfil del usuario
      const { data: profileData, error: profileError } = await supabase
        .from('profiles')
        .select('*')
        .eq('id', authData.user.id)
        .single();

      if (profileError) {
        return { user: null, token: null, error: 'Perfil de usuario no encontrado' };
      }

      // Generar JWT token
      const token = jwt.sign(
        { 
          userId: authData.user.id, 
          email: profileData.email, 
          role: profileData.role 
        },
        config.jwtSecret,
        { expiresIn: '24h' }
      );

      return {
        user: profileData as User,
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
        .from('profiles')
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
        .from('profiles')
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
