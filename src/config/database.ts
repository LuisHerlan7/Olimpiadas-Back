import { createClient } from '@supabase/supabase-js';
import dotenv from 'dotenv';

dotenv.config();

// Configuración de Supabase (PostgreSQL en la nube)
const supabaseUrl = process.env.SUPABASE_URL || 'https://qymxhxajotbsprbvtbne.supabase.co';
const supabaseKey = process.env.SUPABASE_ANON_KEY || 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InF5bXhoeGFqb3Ric3ByYnZ0Ym5lIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTk0MTM3NjksImV4cCI6MjA3NDk4OTc2OX0.eQuoLuyy6cjw6ednfdgdDKWzr9tId_IMaz3xnAKUOXc';

// Cliente de Supabase para PostgreSQL
export const supabase = createClient(supabaseUrl, supabaseKey);

export const config = {
  port: process.env.PORT || 3000,
  jwtSecret: process.env.JWT_SECRET || 'tu_jwt_secret_muy_seguro_aqui',
  nodeEnv: process.env.NODE_ENV || 'development',
  supabaseUrl
};
