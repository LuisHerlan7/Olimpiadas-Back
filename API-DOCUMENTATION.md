# 📚 Documentación de APIs - Backend Olimpiadas

## Base URL
```
http://localhost:3000
```

## 🔐 Autenticación

### POST /api/auth/register
Registrar nuevo usuario

**Request Body:**
```json
{
  "email": "usuario@ejemplo.com",
  "password": "password123",
  "nombre": "Juan",
  "apellido": "Pérez",
  "role": "administrador" // o "encargado" o "olimpista"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Usuario registrado exitosamente",
  "data": {
    "user": {
      "id": "uuid",
      "email": "usuario@ejemplo.com",
      "nombre": "Juan",
      "apellido": "Pérez",
      "role": "administrador",
      "created_at": "2024-01-01T00:00:00Z"
    },
    "token": "jwt_token_aqui"
  }
}
```

### POST /api/auth/login
Iniciar sesión

**Request Body:**
```json
{
  "email": "usuario@ejemplo.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Inicio de sesión exitoso",
  "data": {
    "user": { /* datos del usuario */ },
    "token": "jwt_token_aqui"
  }
}
```

### GET /api/auth/profile
Obtener perfil del usuario (requiere token)

**Headers:**
```
Authorization: Bearer jwt_token_aqui
```

**Response:**
```json
{
  "success": true,
  "data": {
    "user": { /* datos del usuario */ }
  }
}
```

## 🔒 Rutas Protegidas

### GET /api/protected/me
Información del usuario actual

**Headers:**
```
Authorization: Bearer jwt_token_aqui
```

### GET /api/protected/check-permissions
Verificar permisos del usuario

**Headers:**
```
Authorization: Bearer jwt_token_aqui
```

**Response:**
```json
{
  "success": true,
  "data": {
    "user": { /* datos del usuario */ },
    "permissions": {
      "canManageUsers": true,
      "canManageEvents": true,
      "canViewResults": true,
      "canCreateEvents": true,
      "canEditProfile": true
    }
  }
}
```

### GET /api/protected/admin/dashboard
Dashboard de administrador (solo administradores)

**Headers:**
```
Authorization: Bearer jwt_token_aqui
```

### GET /api/protected/encargado/dashboard
Dashboard de encargado (encargados y administradores)

**Headers:**
```
Authorization: Bearer jwt_token_aqui
```

### GET /api/protected/olimpista/dashboard
Dashboard de olimpista (todos los roles)

**Headers:**
```
Authorization: Bearer jwt_token_aqui
```

## 🚀 Cómo Probar las APIs

### 1. Con cURL
```bash
# Registrar usuario
curl -X POST http://localhost:3000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@test.com","password":"123456","nombre":"Admin","apellido":"Test","role":"administrador"}'

# Login
curl -X POST http://localhost:3000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@test.com","password":"123456"}'

# Usar token en requests protegidos
curl -X GET http://localhost:3000/api/protected/me \
  -H "Authorization: Bearer TU_TOKEN_AQUI"
```

### 2. Con Postman
1. Importa el archivo `API-TESTS.postman_collection.json`
2. Ejecuta las requests en orden
3. Guarda el token de login para usar en requests protegidas

### 3. Con el script de prueba
```bash
chmod +x test-api.sh
./test-api.sh
```

## 📝 Códigos de Estado

- `200` - Éxito
- `201` - Creado exitosamente
- `400` - Error en la petición
- `401` - No autenticado
- `403` - Sin permisos
- `404` - No encontrado
- `500` - Error interno del servidor
