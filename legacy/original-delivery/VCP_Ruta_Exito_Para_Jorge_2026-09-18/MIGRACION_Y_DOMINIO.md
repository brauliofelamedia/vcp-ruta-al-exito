# Migración y dominio de VCP

## 1. Confirmar la dirección

Confirmar con Oriana el hostname exacto y quién administra su DNS. Ejemplo de propuesta pendiente de aprobación: `ruta.vendecomopro.net`. No modificar los registros de la web principal, Kajabi, correo u otros subdominios.

Elegir una de las alternativas siguientes; no hace falta reconstruir la página.

## 2. Alternativa A · Mantener el alojamiento actual

El servicio actual admite dominios personalizados. La vinculación debe hacerse desde la cuenta propietaria o mediante acceso administrativo autorizado; este ZIP no concede esos permisos.

1. Registrar el hostname aprobado para el sitio existente desde su administración.
2. Obtener los registros de enrutamiento y verificación generados específicamente para ese hostname.
3. Configurar solo esos registros en el DNS de VCP. Para un subdominio, usar el CNAME indicado por el servicio; no inventar su destino ni usar el URL completo como valor.
4. Completar las verificaciones y esperar a que el dominio y su certificado HTTPS estén activos.
5. Confirmar que sigue vigente el acceso privado y probar entrada/restricción desde el dominio personalizado.

Los valores CNAME/TXT u otros registros no están incluidos porque todavía no existe un hostname aprobado y registrado. No basta crear un CNAME apuntando al dominio actual sin completar la vinculación y verificación del servicio.

Esta alternativa cambia la dirección, pero conserva el proveedor actual. No garantiza corregir restricciones regionales de red ni evita los requisitos de acceso privado existentes.

## 3. Alternativa B · Migrar al alojamiento de VCP

Para independencia del proveedor actual, alojar los archivos en la infraestructura administrada por VCP:

1. Crear un sitio independiente de pruebas, sin reemplazar sitios existentes.
2. Subir TODO el contenido de `web/` a su raíz: `index.html`, `style.css`, `app.js` y `assets/`.
3. No se necesita comando de compilación. La página y sus descargas usan rutas relativas.
4. Configurar una protección real de acceso a nivel de servidor/gateway/portal, también para `assets/*.pdf`. No desplegar públicamente mientras Oriana haya pedido mantenerla privada.
5. Vincular el hostname aprobado y aplicar exclusivamente los registros DNS que indique ese alojamiento.
6. Activar HTTPS válido; revisar los tipos MIME de JavaScript, CSS, WebP, PNG, TTF y PDF.
7. No cachear contenido protegido de forma que otra persona pueda obtenerlo sin autorización.
8. Revisar funcionamiento y acceso siguiendo `CHECKLIST_DE_ENTREGA.md`.
9. Conservar la versión actual como respaldo hasta la aceptación del nuevo sitio.

Si más adelante se autoriza acceso a estudiantes, definir primero cómo se validará su pertenencia a la academia. No abrir el sitio a todo Internet sin autorización de Oriana.

## 4. Progreso y datos

El progreso no depende de una API: funciona con `localStorage`, separado por sistema. Al cambiar dominio cambia el origen del navegador y las marcas antiguas no aparecen automáticamente. Informarlo antes de compartir el enlace nuevo.

Conectar posteriormente un Google Sheet o sincronizar avances requeriría desarrollo adicional: identificación, consentimiento, seguridad y un backend. No poner credenciales de Google en `app.js`.

## 5. Disponibilidad y operación

Un dominio propio no ofrece disponibilidad permanente por sí solo. Revisar renovación del dominio, certificado TLS, servicio de alojamiento, respaldo de esta versión y comprobación periódica de disponibilidad según el proceso habitual de VCP.

Antes de lanzar, probar la conexión sin VPN desde una red o ubicación representativa de los estudiantes y documentar el resultado. Si persiste un bloqueo regional, investigarlo con el proveedor de alojamiento; no atribuirlo únicamente al nombre de dominio.

## 6. Entrega final solicitada a Jorge

- URL HTTPS final funcionando.
- Confirmación de que el acceso permanece privado.
- Ubicación del respaldo y de los archivos editables.
- Resultado del checklist, incluyendo prueba sin VPN.
- Instrucciones para futuras actualizaciones y responsable del alojamiento/DNS.

No se solicita desactivar el sitio anterior ni cambiar accesos a la academia.
