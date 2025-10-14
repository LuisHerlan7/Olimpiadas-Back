import { createClient } from '@supabase/supabase-js';
import dotenv from 'dotenv';

dotenv.config();

// Configuración de Supabase (PostgreSQL en la nube)
const supabaseUrl = process.env.SUPABASE_URL || 'https://qymxhxajotbsprbvtbne.supabase.co';
const supabaseAnonKey = process.env.SUPABASE_ANON_KEY || 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InF5bXhoeGFqb3Ric3ByYnZ0Ym5lIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTk0MTM3NjksImV4cCI6MjA3NDk4OTc2OX0.eQuoLuyy6cjw6ednfdgdDKWzr9tId_IMaz3xnAKUOXc';
const supabaseServiceKey = process.env.SUPABASE_SERVICE_KEY;

// Debug: Verificar variables de entorno
console.log('🔧 Configuración Supabase:');
console.log('URL:', supabaseUrl);
console.log('Service Key presente:', supabaseServiceKey ? 'Sí' : 'No');
console.log('Anon Key presente:', supabaseAnonKey ? 'Sí' : 'No');
console.log('Service Key (primeros 20 chars):', supabaseServiceKey ? supabaseServiceKey.substring(0, 20) + '...' : 'No configurada');

// Cliente de Supabase para autenticación (usa clave anónima)
export const supabase = createClient(supabaseUrl, supabaseAnonKey);

// Cliente de Supabase para consultas de base de datos (usa service key)
if (!supabaseServiceKey) {
  console.error('❌ SUPABASE_SERVICE_KEY no está configurada!');
  console.error('Por favor, agrega SUPABASE_SERVICE_KEY a tu archivo .env');
}

export const supabaseAdmin = createClient(supabaseUrl, supabaseServiceKey || supabaseAnonKey, {
  auth: {
    autoRefreshToken: false,
    persistSession: false
  },
  global: {
    headers: {
      'Authorization': `Bearer ${supabaseServiceKey || supabaseAnonKey}`
    }
  }
});

export const config = {
  port: process.env.PORT || 3000,
  jwtSecret: process.env.JWT_SECRET || 'tu_jwt_secret_muy_seguro_aqui',
  nodeEnv: process.env.NODE_ENV || 'development',
  supabaseUrl
};
