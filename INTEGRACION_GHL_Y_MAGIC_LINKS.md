# Documentación de Integración: Alta de Usuarios, Magic Links y Notificaciones por Inactividad (GoHighLevel)

Este documento detalla la arquitectura, endpoints, webhooks y comandos para la gestión de usuarios, acceso sin contraseña (Magic Link) y recordatorios automáticos de inactividad integrados con **GoHighLevel (GHL)**.

---

## 1. Alta y Aprovisionamiento de Usuarios (API)

Para registrar un nuevo estudiante cuando compra el curso o se da de alta en GoHighLevel / Zapier / Make, envía una petición HTTP POST al backend.

### Endpoint: `POST /api/v1/users`
*(También disponible en `/api/v1/registration-requests` para retrocompatibilidad).*

- **Método:** `POST`
- **Headers:**
  - `Content-Type: application/json`
  - `Accept: application/json`

#### Payload (JSON)
```json
{
  "name": "Juan Perez",
  "email": "juan@ejemplo.com",
  "system": "medium",
  "residence": "usa",
  "send_magic_link": true
}
```

| Campo | Tipo | Requerido | Descripción |
|---|---|---|---|
| `name` | string | **Sí** | Nombre completo del alumno. |
| `email` | string | **Sí** | Correo electrónico del alumno. |
| `system` | string | No | `medium` (por defecto) o `elite`. |
| `residence` | string | No | `usa` (por defecto) o `outside`. |
| `send_magic_link` | boolean | No | Si es `true`, envía de inmediato el webhook a GHL con el enlace mágico de bienvenida. |

#### Respuesta Exitosa (`201 Created` o `200 OK`)
```json
{
  "ok": true,
  "message": "Usuario dado de alta exitosamente.",
  "user": {
    "id": 15,
    "name": "Juan Perez",
    "email": "juan@ejemplo.com"
  },
  "student": {
    "id": 15,
    "system": "medium",
    "residence": "usa",
    "registration_status": "approved"
  },
  "magic_link_sent": true
}
```

---

## 2. Acceso con Magic Link (Sin Contraseña)

El registro público en la web fue deshabilitado. Los estudiantes solo ingresan indicando su correo electrónico registrado.

### Flujo de Funcionamiento:
1. El estudiante hace clic en **"Conectar mi cuenta"** en la plataforma web.
2. Ingresa su correo en el modal.
3. El frontend envía la petición a:
   - **`POST /api/v1/auth/magic-link`**
   - Payload: `{"email": "juan@ejemplo.com"}`
4. Si el correo no existe en la base de datos, el sistema devuelve `404` indicándole que debe ser dado de alta por la academia.
5. Si el correo existe:
   - Se crea un token seguro con expiración de 24 horas (`magic_login_tokens`).
   - Se dispara un webhook a GoHighLevel con el enlace generado y el **cuerpo HTML completo**.
6. El estudiante abre el correo en su bandeja de entrada y pulsa el botón:
   - Enlace: `GET https://tudominio.com/auth/magic-login?token={TOKEN}&email={EMAIL}`
7. El backend valida el token (de uso único), lo consume, genera el token de Sanctum y redirige a la ruta con sesión iniciada automáticamente.

---

## 3. Webhook de Magic Link para GoHighLevel

Cuando un usuario solicita un enlace o se aprovisiona con `send_magic_link: true`, el backend hace un `POST` a la URL configurada en:
- `GHL_MAGIC_LINK_WEBHOOK_URL` (o `GHL_WEBHOOK_URL`).

### Payload enviado a GHL
```json
{
  "event": "vcp_magic_link_requested",
  "email": "juan@ejemplo.com",
  "name": "Juan Perez",
  "magic_link_url": "https://tudominio.com/auth/magic-login?token=a8f...&email=juan%40ejemplo.com",
  "expires_at": "2026-10-03T19:30:00+00:00",
  "html": "<!DOCTYPE html><html lang=\"es\">...<!-- HTML completo listo para usar en GHL --></html>"
}
```

### Cómo insertarlo en GoHighLevel:
1. En tu Workflow de GoHighLevel, añade un trigger **Inbound Webhook** que reciba este payload.
2. Añade la acción **Send Email**.
3. En el editor de correo de GHL:
   - Puedes seleccionar la vista de código HTML (`</>`) y usar el campo de variable personalizada del webhook: `{{webhook.html}}`.
   - O si prefieres diseñar el correo dentro de GHL, puedes usar las variables individuales:
     - Nombre: `{{webhook.name}}`
     - Enlace al botón: `{{webhook.magic_link_url}}`

---

## 4. Notificaciones por Inactividad cada 8 Días

El sistema monitorea el avance de los estudiantes registrados para reactivar a quienes lleven 8 días o más sin registrar actividad en la ruta.

### Reglas de Detección:
- Estudiantes con estado `approved` o cuenta vinculada.
- Estudiantes que aún no han terminado la ruta (`completed_stations < 12`).
- Que hayan pasado $\ge 8$ días desde su último movimiento (`last_active_at` o fecha de creación).
- Que no hayan recibido recordatorio en los últimos 8 días (para evitar spam). Si pasan otros 8 días inactivos (día 16), el sistema vuelve a notificar.

### Comando Manual / Cron
```bash
php artisan vcp:notify-inactive-students --days=8
```

### Ejecución Automática (Scheduler):
Está registrado en `routes/console.php` para ejecutarse diariamente:
```php
Schedule::command('vcp:notify-inactive-students')
    ->daily()
    ->withoutOverlapping(60)
    ->onOneServer();
```

### Payload enviado al Webhook de Inactividad (`GHL_INACTIVITY_WEBHOOK_URL`):
```json
{
  "event": "vcp_student_inactive_reminder",
  "email": "juan@ejemplo.com",
  "name": "Juan Perez",
  "days_inactive": 8,
  "current_station_number": 3,
  "current_station_name": "Apertura de cuenta Seller Central",
  "completed_stations": 2,
  "completed_tasks": 8,
  "progress_percentage": 17,
  "system": "medium",
  "residence": "usa",
  "route_url": "https://tudominio.com/auth/magic-login?token=...",
  "magic_link_url": "https://tudominio.com/auth/magic-login?token=...",
  "html": "<!DOCTYPE html>...<!-- Correo motivacional completo con barra de progreso y botón de retorno instantáneo -->"
}
```

> **Ventaja clave del correo:** El botón "Continuar mi Ruta Ahora" incluye su propio Magic Link, por lo que el alumno hace clic desde el correo e ingresa directamente a su estación sin tener que iniciar sesión manualmente.

---

## 5. Variables de Entorno (`.env`)

Agrega las siguientes variables a tu archivo `.env` en producción o entorno local:

```env
# Webhook general de fallback
GHL_WEBHOOK_URL=https://services.leadconnectorhq.com/hooks/TU_WEBHOOK_GENERAL

# Webhook específico para Magic Link (opcional, si está vacío usa GHL_WEBHOOK_URL)
GHL_MAGIC_LINK_WEBHOOK_URL=https://services.leadconnectorhq.com/hooks/TU_WEBHOOK_MAGIC_LINK

# Webhook específico para recordatorios de inactividad (opcional, si está vacío usa GHL_WEBHOOK_URL)
GHL_INACTIVITY_WEBHOOK_URL=https://services.leadconnectorhq.com/hooks/TU_WEBHOOK_INACTIVIDAD

# Credenciales de API de GoHighLevel (para sincronización de contactos y custom fields)
GHL_API_KEY=
GHL_LOCATION_ID=
```

---

## 6. Pruebas Automatizadas

Para validar que todos los flujos funcionan correctamente, ejecuta:

```bash
php artisan test
```

Los tests cubren:
- Bloqueo de registro público con respuesta 403.
- Aprovisionamiento de usuario y estudiante aprobado vía API.
- Generación de Magic Link y envío de webhook con plantilla HTML completa.
- Validación y consumo de tokens de un solo uso.
- Detección de alumnos inactivos $\ge 8$ días y generación de webhook con datos de avance.
