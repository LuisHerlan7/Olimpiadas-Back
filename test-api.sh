#!/bin/bash

# Script para probar las APIs del backend de olimpiadas
BASE_URL="http://localhost:3000"

echo "🚀 Probando APIs del Backend de Olimpiadas"
echo "=========================================="

# 1. Health Check
echo "1. Health Check:"
curl -X GET "$BASE_URL/api/health" | jq
echo -e "\n"

# 2. Register User
echo "2. Registrando usuario administrador:"
REGISTER_RESPONSE=$(curl -s -X POST "$BASE_URL/api/auth/register" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@olimpiadas.com",
    "password": "password123",
    "nombre": "Admin",
    "apellido": "Sistema",
    "role": "administrador"
  }')

echo $REGISTER_RESPONSE | jq

# Extraer token del registro
TOKEN=$(echo $REGISTER_RESPONSE | jq -r '.data.token')

if [ "$TOKEN" != "null" ] && [ "$TOKEN" != "" ]; then
  echo -e "\n✅ Usuario registrado exitosamente"
  echo "Token: $TOKEN"
  
  # 3. Get Profile
  echo -e "\n3. Obteniendo perfil:"
  curl -X GET "$BASE_URL/api/auth/profile" \
    -H "Authorization: Bearer $TOKEN" | jq
  
  # 4. Admin Dashboard
  echo -e "\n4. Accediendo a dashboard de administrador:"
  curl -X GET "$BASE_URL/api/protected/admin/dashboard" \
    -H "Authorization: Bearer $TOKEN" | jq
  
  # 5. Check Permissions
  echo -e "\n5. Verificando permisos:"
  curl -X GET "$BASE_URL/api/protected/check-permissions" \
    -H "Authorization: Bearer $TOKEN" | jq
  
else
  echo -e "\n❌ Error al registrar usuario"
fi

echo -e "\n✅ Pruebas completadas"
