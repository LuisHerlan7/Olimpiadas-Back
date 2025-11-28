# 🌱 Ejecutar Seeders en Railway

## Problema
El backend deployado en Railway no tiene los usuarios base (admin, responsable, evaluador) porque no se han ejecutado los seeders.

## ✅ Solución: Ejecutar Seeders en Railway

### Opción 1: Usando Railway CLI (Recomendado)

1. **Instala Railway CLI** (si no lo tienes):
   ```bash
   npm i -g @railway/cli
   ```

2. **Inicia sesión en Railway**:
   ```bash
   railway login
   ```

3. **Conecta a tu proyecto**:
   ```bash
   cd Olimpiadas-Back
   railway link
   ```

4. **Ejecuta las migraciones** (si no están ejecutadas):
   ```bash
   railway run php artisan migrate
   ```

5. **Ejecuta los seeders**:
   ```bash
   railway run php artisan db:seed
   ```

### Opción 2: Usando Railway Dashboard

1. Ve a tu proyecto en Railway: https://railway.app
2. Selecciona tu servicio del backend
3. Ve a la pestaña **Deployments**
4. Haz clic en el último deployment
5. Ve a la pestaña **Logs** o **Shell**
6. Si hay una terminal disponible, ejecuta:
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

### Opción 3: Usando Railway Shell (Web Terminal)

1. Ve a tu proyecto en Railway: https://railway.app
2. Selecciona tu servicio del backend
3. Busca la opción **Shell** o **Terminal**
4. Ejecuta:
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

### Opción 4: Crear un Script de Deploy

Puedes crear un script que se ejecute automáticamente en cada deploy:

1. Crea un archivo `deploy.sh` en la raíz de `Olimpiadas-Back`:
   ```bash
   #!/bin/bash
   php artisan migrate --force
   php artisan db:seed --force
   ```

2. Configura Railway para ejecutar este script después del deploy (en las variables de entorno o configuración de Railway).

## ✅ Verificación

Después de ejecutar los seeders, deberías tener estos usuarios:

- **Admin**: `admin@ohsansi.bo` / `admin123`
- **Responsable**: `responsable@ohsansi.bo` / `resp123`
- **Evaluador**: `evaluador@ohsansi.bo` / `eval123`

## 🧪 Probar el Login

1. Ve a tu frontend: `https://ohsansi.vercel.app`
2. Intenta hacer login con:
   - Correo: `admin@ohsansi.bo`
   - Password: `admin123`

## ⚠️ Nota Importante

Si ya tienes datos en producción y no quieres perderlos, usa `updateOrCreate` en lugar de `create` (que ya está en `DatabaseSeeder.php`), así que es seguro ejecutarlo múltiples veces.

## 🔄 Si Necesitas Re-ejecutar

Si necesitas re-ejecutar solo los seeders de usuarios:

```bash
railway run php artisan db:seed --class=DatabaseSeeder
```

O si quieres ejecutar un seeder específico:

```bash
railway run php artisan db:seed --class=UsuariosSeeder
```

