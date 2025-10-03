# Backend Olimpiadas

Backend para sistema de olimpiadas con autenticación por roles usando Supabase.

## Características

- ✅ Autenticación con Supabase
- ✅ Sistema de roles (administrador, encargado, olimpista)
- ✅ Middleware de autorización
- ✅ JWT tokens
- ✅ Rate limiting
- ✅ Validaciones de entrada
- ✅ TypeScript

## Instalación

```bash
# Instalar dependencias
npm install

# Configurar variables de entorno
cp .env.example .env

# Ejecutar en desarrollo
npm run dev

# Compilar para producción
npm run build
npm start
```

## Variables de Entorno

```env
SUPABASE_URL=https://qymxhxajotbsprbvtbne.supabase.co
SUPABASE_ANON_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InF5bXhoeGFqb3Ric3ByYnZ0Ym5lIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTk0MTM3NjksImV4cCI6MjA3NDk4OTc2OX0.eQuoLuyy6cjw6ednfdgdDKWzr9tId_IMaz3xnAKUOXc
JWT_SECRET=tu_jwt_secret_muy_seguro
PORT=3000
NODE_ENV=development
```

## Configuración de Supabase (PostgreSQL)

1. **Ejecutar el esquema SQL en Supabase:**
   - Ve a tu proyecto de Supabase → SQL Editor
   - Ejecuta este código SQL:

```sql
-- Crear tabla de perfiles de usuario
CREATE TABLE IF NOT EXISTS profiles (
  id UUID REFERENCES auth.users(id) PRIMARY KEY,
  email VARCHAR(255) UNIQUE NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  apellido VARCHAR(100) NOT NULL,
  role VARCHAR(20) CHECK (role IN ('administrador', 'encargado', 'olimpista')) NOT NULL,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
  updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Habilitar RLS (Row Level Security)
ALTER TABLE profiles ENABLE ROW LEVEL SECURITY;

-- Políticas de seguridad
CREATE POLICY "Users can view own profile" ON profiles
  FOR SELECT USING (auth.uid() = id);

CREATE POLICY "Users can update own profile" ON profiles
  FOR UPDATE USING (auth.uid() = id);

CREATE POLICY "Users can insert own profile" ON profiles
  FOR INSERT WITH CHECK (auth.uid() = id);
```

2. **No necesitas base de datos local:**
   - Todo se maneja en Supabase (PostgreSQL en la nube)
   - El backend se conecta directamente a Supabase

## API Endpoints

### Autenticación
- `POST /api/auth/register` - Registrar usuario
- `POST /api/auth/login` - Iniciar sesión
- `POST /api/auth/logout` - Cerrar sesión
- `GET /api/auth/profile` - Obtener perfil
- `GET /api/auth/verify` - Verificar token

### Rutas Protegidas
- `GET /api/protected/me` - Información del usuario actual
- `GET /api/protected/check-permissions` - Verificar permisos
- `GET /api/protected/admin/dashboard` - Dashboard administrador
- `GET /api/protected/encargado/dashboard` - Dashboard encargado
- `GET /api/protected/olimpista/dashboard` - Dashboard olimpista

## Ejemplos de Uso

### Registro
```bash
curl -X POST http://localhost:3000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@olimpiadas.com",
    "password": "password123",
    "nombre": "Admin",
    "apellido": "Sistema",
    "role": "administrador"
  }'
```

### Login
```bash
curl -X POST http://localhost:3000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@olimpiadas.com",
    "password": "password123"
  }'
```

### Acceso a ruta protegida
```bash
curl -X GET http://localhost:3000/api/protected/me \
  -H "Authorization: Bearer tu_jwt_token_aqui"
```

## Roles y Permisos

- **Administrador**: Acceso completo al sistema
- **Encargado**: Gestión de eventos y olimpistas
- **Olimpista**: Acceso a su perfil y resultados

## Desarrollo

```bash
# Ejecutar en modo desarrollo
npm run dev

# Compilar TypeScript
npm run build

# Ejecutar tests (cuando estén implementados)
npm test
```
