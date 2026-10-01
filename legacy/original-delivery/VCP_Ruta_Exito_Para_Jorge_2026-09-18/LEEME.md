# VCP · Ruta del Éxito · Entrega para Jorge

Fecha: 18 de septiembre de 2026.
Responsable de implementación: Jorge · Jorge@felamedia.com.
Solicitado por: Oriana Aldrey.

## Objetivo

Llevar la ruta interactiva de Sistema Definitivo (Medium) y Sistema Élite (VIP avanzado) a una dirección de VCP, manteniendo la experiencia aprobada y la privacidad hasta que Oriana autorice compartirla con estudiantes.

Sitio actual: https://vcp-ruta-del-exito.orianacarlota22.chatgpt.site

El sitio actual está privado. El enlace por sí solo no concede acceso a Jorge. Este paquete sí permite revisar y alojar una copia completa, sin depender del acceso al sitio actual.

## Qué contiene

- `web/index.html`: página completa.
- `web/style.css`: diseño y adaptación a móvil.
- `web/app.js`: contenido, selector de sistema/residencia, estaciones y progreso.
- `web/assets/`: mapa, explorador, logo, tipografías locales con sus licencias y cinco PDF.
- `MIGRACION_Y_DOMINIO.md`: alternativas y condiciones de la migración.
- `CHECKLIST_DE_ENTREGA.md`: verificaciones antes de entregar el dominio nuevo.

PDF incluidos en `web/assets/`:

1. `guia-completa.pdf`: ruta completa Medium y Élite, 26 páginas.
2. `roadmap.pdf`: mapa visual, 1 página.
3. `guia-1.pdf`: inicio, cuenta y análisis, 13 páginas.
4. `guia-2.pdf`: productos, compra y logística, 8 páginas.
5. `guia-3.pdf`: Seller Central, venta y pago, 7 páginas.

Todos corresponden a la entrega sin Pablo como coach.

## Implementación

Es una web estática: HTML, CSS y JavaScript, sin framework, compilación, npm, servidor de aplicación, claves API ni base de datos. Publicar el contenido de `web/` como raíz del sitio, manteniendo intacta la carpeta `assets/`.

Servirla por HTTP/HTTPS, no usar `file://` como prueba de producción. Para una revisión local, por ejemplo:

```sh
python3 -m http.server 8080 --directory web
```

Abrir `http://localhost:8080/`. Esta vista local no es un enlace para estudiantes.

## Comportamiento que debe mantenerse

- Dos vistas: ruta paso a paso desplegable y mapa de expedición.
- Doce estaciones y cinco hitos del primer ciclo.
- Selector de sistema con el curso correcto para cada uno.
- Selector de residencia dentro/fuera de Estados Unidos.
- Progreso por tareas guardado SOLO en el navegador/dispositivo.
- Definitivo: seis meses de Discord y una sesión individual al mes.
- Élite: doce meses de Discord y dos sesiones individuales al mes.
- Sesiones individuales de 45 minutos, no acumulables.
- Hushed principal y TalkYou alternativa para línea virtual.

Cursos:

- Definitivo: https://class.vendecomopro.net/products/vendecomopro-el-sistema-definitivo-2
- Élite: https://class.vendecomopro.net/products/vendecomopro-elite-2
- Grabaciones, ambos: https://class.vendecomopro.net/products/grabaciones-de-zoom
- Academia: https://class.vendecomopro.net/library
- Asesorías: https://lp.vendecomopro.net/asesorias-individuales

El contenido editable principal está en las constantes `URLS`, `STAGES`, `MATERIALS` y `DOWNLOADS` de `app.js`. El progreso utiliza `localStorage` con la clave `vcp-success-route-v1`.

## Límites importantes

Cambiar de dominio/origen NO transfiere automáticamente el progreso local de estudiantes. Para esta migración empieza un registro local nuevo; si se desea conservar marcas anteriores, habrá que diseñar una transferencia explícita y segura antes de cambiar de dominio.

No existe sincronización entre dispositivos, registro de estudiantes ni conexión con Google Sheets. Esta entrega es la ruta interactiva, no el quiz tecnológico de onboarding.

La privacidad del sitio actual es proporcionada por su alojamiento. Los archivos estáticos NO incluyen autenticación. Una copia en un hosting nuevo necesita su propia protección de acceso, incluyendo los PDF e imágenes. No basta ocultar el URL, usar `noindex` o añadir una contraseña en JavaScript.

La entrega no incluye contraseñas, tokens, certificados, credenciales de DNS ni acceso a cuentas. Solicitar lo necesario a Oriana por un canal seguro y con permisos mínimos.

## Procedencia

Entrega basada en la versión aprobada y publicada el 18/09/2026, revisión de fuente `d70eb3c7cf2af54af6ad6fd6a176f0b901b61e98`.

No se ha registrado, elegido ni cambiado ningún dominio como parte de esta entrega.
