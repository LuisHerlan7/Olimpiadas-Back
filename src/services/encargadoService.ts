import { supabase } from '../config/database';
import jwt from 'jsonwebtoken';
import { config } from '../config/database';
import {
  EncargadoRegisterRequest,
} from '../types/encargado';

export class EncargadoService {
  async register(data: EncargadoRegisterRequest): Promise<{ id?: string; token?: string; error?: string }> {
    try {
      // Crear usuario en Supabase Auth
      const { data: authData, error: authError } = await supabase.auth.signUp({
        email: data.correo,
        password: data.contraseña,
      });

      if (authError) {
        return { error: authError.message };
      }

      if (!authData.user) {
        return { error: 'Error al crear usuario en auth' };
      }

      // Crear perfil en la tabla perfiles
      const { data: profileData, error: profileError } = await supabase
        .from('perfiles')
        .insert({
          id: authData.user.id,
          ci: data.ci,
          correo: data.correo,
          nombre: data.nombre,
          apellido: data.apellido,
          contraseña: data.contraseña,
          fecha_nacimiento: data.fecha_nacimiento,
          curso: data.curso,
          departamento: data.departamento,
          colegio: data.colegio,
          rol: 'encargado',
          fecha_creacion: new Date().toISOString(),
          fecha_actualizacion: new Date().toISOString(),
        })
        .select()
        .single();

      if (profileError) {
        // eliminar usuario de auth si falla la creación del perfil
        try {
          // supabase-js v2 admin deleteUser: supabase.auth.admin.deleteUser
          // si no está disponible, solo retornamos error
          // @ts-ignore
          if (supabase.auth.admin && typeof supabase.auth.admin.deleteUser === 'function') {
            // @ts-ignore
            await supabase.auth.admin.deleteUser(authData.user.id);
          }
        } catch (e) {
          // ignore
        }
        return { error: profileError.message };
      }

      // Generar token JWT
      const token = jwt.sign(
        {
          userId: authData.user.id,
          email: data.correo,
          role: 'encargado',
        },
        config.jwtSecret,
        { expiresIn: '24h' }
      );

      return { id: profileData.id, token };
    } catch (error) {
      return { error: error instanceof Error ? error.message : 'Error interno' };
    }
  }
}
