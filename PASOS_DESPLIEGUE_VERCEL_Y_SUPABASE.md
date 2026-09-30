# Guía Rápida de Despliegue · Vercel + Supabase + GoHighLevel

Esta guía te explica paso a paso cómo dejar la **Ruta del Éxito de VCP** 100% funcionando en producción con base de datos en la nube y sincronización CRM.

---

## Paso 1: Configurar Supabase (2 minutos)

1. Entra a [supabase.com](https://supabase.com/) e inicia sesión con tu cuenta gratuita.
2. Crea un **Nuevo Proyecto** (ej: `vcp-ruta-exito`).
3. Ve a la sección **SQL Editor** en el menú izquierdo.
4. Abre el archivo [supabase/schema.sql](file:///c:/Users/Braulio/Downloads/VCP_Ruta_Exito_Para_Jorge_2026-09-18%20%281%29/supabase/schema.sql), copia todo su contenido, pégalo en el editor SQL de Supabase y presiona **Run**.
   * *Esto creará las tablas `students` y `student_progress` con sus índices y permisos listos.*
5. Ve a **Project Settings** (el engranaje abajo a la izquierda) -> **API**.
6. Copia estos dos valores (los necesitarás para Vercel):
   * **Project URL** (ej: `https://abcdefgh.supabase.co`)
   * **service_role (secret)** (haz clic en *Reveal* y copia esta clave secreta para el backend)

---

## Paso 2: Configurar GoHighLevel (GHL)

Tienes dos formas de recibir la data en GHL:

### Método A: Webhook en un Workflow de GHL (El más sencillo y recomendado)
1. En GoHighLevel, crea un **Workflow** de automatización.
2. Añade un disparador de tipo **Inbound Webhook**.
3. Copia el **Webhook URL** que te da GHL.
4. Esa URL es tu variable `GHL_WEBHOOK_URL`. Cada vez que el alumno marque tareas, GHL recibirá el progreso completo en tiempo real.

### Método B: Conexión directa por API
1. En GHL ve a **Settings** -> **Business Profile** y copia tu **Location ID**.
2. Genera un API Token / Private Integration Token con permisos de contactos.

---

## Paso 3: Desplegar en Vercel (1 minuto)

### Opción 1: Con Git y GitHub (Recomendado para producción)
1. Sube este proyecto a un repositorio privado de GitHub:
   ```bash
   git init
   git add .
   git commit -m "feat: VCP Ruta del Exito con Supabase y Vercel Serverless"
   git remote add origin https://github.com/tu-usuario/vcp-ruta-exito.git
   git push -u origin main
   ```
2. Entra a [vercel.com](https://vercel.com/) y haz clic en **Add New...** -> **Project**.
3. Importa el repositorio de GitHub.
4. En la sección **Environment Variables**, añade:
   * `SUPABASE_URL` = (Tu URL de Supabase)
   * `SUPABASE_SERVICE_ROLE_KEY` = (Tu clave service_role de Supabase)
   * `GHL_WEBHOOK_URL` = (Tu webhook de GoHighLevel)
5. Haz clic en **Deploy**. ¡Listo!

### Opción 2: Desde la terminal con Vercel CLI
En esta misma carpeta, puedes ejecutar:
```bash
npx vercel
```
Sigue los pasos interactivos y luego añade las variables de entorno con `npx vercel env add` o desde el dashboard de Vercel.

---

## Paso 4: Conectar el Dominio de VCP

1. En tu proyecto de Vercel, ve a **Settings** -> **Domains**.
2. Escribe el subdominio aprobado con Oriana (ejemplo: `ruta.vendecomopro.net`).
3. Vercel te dará un registro DNS (típicamente un `CNAME` apuntando a `cname.vercel-dns.com`).
4. Configúralo en el proveedor de DNS de VendeComoPro.
5. Vercel generará el certificado **SSL / HTTPS** automáticamente en minutos.

---

## Paso 5: Cómo enviar el enlace a los alumnos en el Onboarding

Cuando un estudiante compra el curso o asiste al onboarding con Miguel, configuras el correo automático de GoHighLevel con su link personalizado:

```
https://ruta.vendecomopro.net/?email={{contact.email}}&name={{contact.first_name}}
```

### ¿Qué pasa cuando el estudiante abre ese enlace?
1. La aplicación web detecta automáticamente su correo y nombre.
2. Carga su progreso previo desde Supabase (si ya lo tenía).
3. Muestra el indicador en verde: **🟢 Conectado como estudiante@correo.com**.
4. Cada vez que marca una tarea, se actualiza inmediatamente en pantalla, se guarda en Supabase y notifica a GoHighLevel para que los coaches tengan el control en el CRM.
