# 🚀 Ejecutar Seeders desde Railway Dashboard (MÁS RÁPIDO)

## ✅ Método Rápido: Usar Railway Dashboard

### Paso 1: Abrir Terminal en Railway

1. Ve a: https://railway.app
2. Inicia sesión en tu cuenta
3. Selecciona tu proyecto del backend
4. Selecciona el servicio de tu backend
5. Ve a la pestaña **"Deployments"** o busca **"Shell"** o **"Terminal"**

### Paso 2: Ejecutar Comandos

Una vez que tengas la terminal abierta, ejecuta estos comandos uno por uno:

```bash
# 1. Ejecutar migraciones (si no están ejecutadas)
php artisan migrate --force

# 2. Ejecutar seeders (crear usuarios)
php artisan db:seed --force
```

### Paso 3: Verificar

Deberías ver mensajes como:
- ✅ "Migration table created successfully"
- ✅ "Usuarios base creados: admin, responsable, evaluador."

## 🎯 Alternativa: Usar Railway CLI con Token

Si prefieres usar CLI, puedes usar un token de autenticación:

1. Ve a Railway Dashboard → Settings → Tokens
2. Crea un nuevo token
3. Usa: `railway login --token TU_TOKEN`

Pero el método del Dashboard es más rápido y directo.

## ✅ Después de Ejecutar

Prueba hacer login en tu frontend:
- Correo: `admin@ohsansi.bo`
- Password: `admin123`

