# 🔧 Instrucciones para Solucionar Error CORS en Railway

## ⚠️ Problema
El error `Access-Control-Allow-Origin` indica que el backend deployado en Railway no está enviando los headers CORS correctamente.

## ✅ Solución

### 1. **Verificar que los cambios estén en el repositorio**

Asegúrate de que estos archivos estén actualizados:
- ✅ `config/cors.php` - Tiene `https://ohsansi.vercel.app` en allowed_origins
- ✅ `routes/api.php` - Tiene la ruta OPTIONS mejorada
- ✅ `bootstrap/app.php` - Tiene HandleCors configurado

### 2. **Hacer commit y push de los cambios**

```bash
cd Olimpiadas-Back
git add .
git commit -m "Fix: Mejorar configuración CORS para Vercel"
git push
```

### 3. **Redesplegar en Railway**

1. Ve a tu proyecto en Railway: https://railway.app
2. Selecciona tu servicio del backend
3. Ve a la pestaña **Deployments**
4. Haz clic en **Redeploy** en el último deployment
5. O simplemente haz un nuevo push al repositorio (Railway se redesplegará automáticamente)

### 4. **Verificar que el backend responda correctamente**

Después del redeploy, prueba estos endpoints:

#### Test 1: Ping endpoint
```bash
curl -X GET https://olimpiadas-back-production-6956.up.railway.app/api/ping
```
Debería devolver: `{"status":"ok",...}`

#### Test 2: OPTIONS preflight (CORS)
```bash
curl -X OPTIONS https://olimpiadas-back-production-6956.up.railway.app/api/auth/login \
  -H "Origin: https://ohsansi.vercel.app" \
  -H "Access-Control-Request-Method: POST" \
  -H "Access-Control-Request-Headers: Content-Type,Authorization" \
  -v
```

**Deberías ver en los headers de respuesta:**
```
Access-Control-Allow-Origin: https://ohsansi.vercel.app
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH
Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin
```

### 5. **Si el problema persiste**

#### Opción A: Verificar variables de entorno en Railway
Asegúrate de que no haya variables de entorno que estén sobrescribiendo la configuración de CORS.

#### Opción B: Verificar logs de Railway
1. Ve a tu servicio en Railway
2. Abre la pestaña **Logs**
3. Busca errores relacionados con CORS o middleware
4. Verifica que el archivo `config/cors.php` se esté cargando correctamente

#### Opción C: Limpiar caché de configuración
Si Railway tiene caché, puedes forzar una limpieza:
1. En Railway, ve a **Settings** → **Variables**
2. Agrega temporalmente: `APP_ENV=production`
3. Guarda y espera el redeploy
4. Luego elimina esa variable si no la necesitas

## 🔍 Verificación Final

Después del redeploy:

1. Abre tu frontend en Vercel: `https://ohsansi.vercel.app`
2. Abre la consola del navegador (F12)
3. Intenta hacer login
4. En la pestaña **Network**, verifica:
   - La petición OPTIONS (preflight) debe devolver status 200
   - Los headers de respuesta deben incluir `Access-Control-Allow-Origin: https://ohsansi.vercel.app`
   - La petición POST a `/api/auth/login` debe funcionar sin errores CORS

## 📝 Notas Importantes

- **El patrón de Vercel**: El código ya incluye `#^https://.*\.vercel\.app$#` que permite cualquier subdominio de Vercel
- **Localhost**: También está configurado para desarrollo local
- **Credenciales**: `supports_credentials: false` está correcto para tu caso

## ✅ Checklist

- [ ] Cambios en `config/cors.php` están en el repositorio
- [ ] Cambios en `routes/api.php` están en el repositorio  
- [ ] Cambios en `bootstrap/app.php` están en el repositorio
- [ ] Push hecho al repositorio
- [ ] Railway redesplegado
- [ ] Test de ping funciona
- [ ] Test de OPTIONS devuelve headers CORS correctos
- [ ] Frontend en Vercel puede hacer login sin errores CORS

