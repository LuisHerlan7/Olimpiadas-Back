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

      // Crear perfil de usuario en la tabla perfiles
      const { data: profileData, error: profileError } = await supabase
        .from('perfiles')
        .insert({
          id: authData.user.id,
          correo: data.email,
          nombre: data.nombre,
          apellido: data.apellido,
          rol: data.role,
          fecha_creacion: new Date().toISOString(),
          fecha_actualizacion: new Date().toISOString()
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

      // Mapear los datos del perfil al formato esperado
      const userData = {
        id: profileData.id,
        email: profileData.correo,
        nombre: profileData.nombre,
        apellido: profileData.apellido,
        role: profileData.rol,
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
      console.log('Intentando login para:', data.email);
      
      // Autenticar con Supabase
      const { data: authData, error: authError } = await supabase.auth.signInWithPassword({
        email: data.email,
        password: data.password,
      });

      console.log('Respuesta de Supabase Auth:', { authData, authError });

      if (authError) {
        console.log('Error de autenticación:', authError.message);
        return { user: null, token: null, error: authError.message };
      }

      if (!authData.user) {
        console.log('No se encontró usuario en la respuesta');
        return { user: null, token: null, error: 'Credenciales inválidas' };
      }

      // Obtener perfil del usuario
      console.log('Buscando perfil para usuario ID:', authData.user.id);
      console.log('Tipo de ID:', typeof authData.user.id);
      console.log('ID como string:', JSON.stringify(authData.user.id));
      
      const { data: profileData, error: profileError } = await supabase
        .from('perfiles')
        .select('*')
        .eq('id', authData.user.id)
        .single();

      console.log('Respuesta de perfil:', { profileData, profileError });
      console.log('Error details:', profileError);
      console.log('Datos del perfil encontrado:', profileData);
      console.log('Rol del perfil:', profileData?.role);

      if (profileError) {
        console.log('Error al obtener perfil:', profileError.message);
        return { user: null, token: null, error: 'Perfil de usuario no encontrado' };
      }

      // Generar JWT token
      const token = jwt.sign(
        { 
          userId: authData.user.id, 
          email: profileData.correo, 
          role: profileData.rol 
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
        role: profileData.rol,
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
