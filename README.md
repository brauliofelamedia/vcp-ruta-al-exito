# VCP · Ruta del Éxito

Aplicación Laravel que sirve la Ruta del Éxito de VendeComoPro y expone una API REST para guardar el avance de los estudiantes en MySQL.

## Estructura

- `resources/views/ruta.blade.php`: interfaz web existente integrada con Laravel.
- `public/assets`: recursos visuales, guías y tipografías del frontend.
- `app/Models`: modelos `Student` y `StudentProgress`.
- `app/Services/ProgressService.php`: reglas de guardado del progreso.
- `routes/api.php`: API que también podrá consumir React Native / Expo.
- `legacy/`: implementación anterior de Vercel, Supabase y GoHighLevel, preservada como referencia durante la migración.

## Desarrollo local

1. Inicia MySQL desde Laragon.
2. Revisa las credenciales en `.env` (por defecto: base `vcp`, usuario `root`).
3. Ejecuta `php artisan migrate`.
4. Inicia la aplicación con `php artisan serve` o configura Laragon para que el virtual host apunte a `public/`.

## API actual

- `GET /api/progress?email=alumno@ejemplo.com`
- `POST /api/progress`

El endpoint conserva el contrato del frontend actual. La siguiente etapa es sustituir el acceso por correo por autenticación con tokens de Sanctum, antes de publicar la API para la app móvil.

## Docker / EasyPanel

El proyecto incluye un `Dockerfile` para PHP 8.4 + Apache. Configura en EasyPanel las variables `APP_KEY`, `APP_URL`, `DB_CONNECTION`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD`, y ejecuta `php artisan migrate --force` durante el despliegue.
