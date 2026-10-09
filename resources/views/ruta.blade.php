<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Ruta del Éxito · VendeComoPro</title>
  <meta name="description" content="Tu ruta de Medium y Élite: qué estudiar, qué hacer y dónde pedir apoyo, desde el onboarding hasta tu primera venta y pago de Amazon.">
  <link rel="icon" type="image/png" href="assets/vcp-logo.png">
  <link rel="stylesheet" href="style.css"><script src="app.js" defer></script>
</head>
<body>
  <header class="topbar">
    <a class="brand" href="#" aria-label="VendeComoPro, inicio"><img src="assets/vcp-logo.png" alt="VendeComoPro" width="78" height="44"></a>
    <span class="top-label">ACADEMIA / RUTA DEL ÉXITO</span>
    <div class="user-status-badge" id="user-status-badge" title="Estado de sincronización con la nube">
      <span class="sync-dot" id="sync-dot"></span>
      <button type="button" class="user-email-btn" id="user-btn">Conectar mi cuenta</button>
      <button type="button" class="logout-btn" id="logout-btn" style="display:none" title="Cerrar sesión">Cerrar sesión</button>
    </div>
    <a class="text-link" href="#recursos">Guías en PDF ↗</a>
  </header>
  <main>
    <section class="intro" aria-labelledby="title">
      <div class="intro-copy">
        <p class="eyebrow">SISTEMA DEFINITIVO + ÉLITE · GUÍA DE EXPEDICIÓN</p>
        <h1 id="title">TU RUTA DEL ÉXITO<br><span>EN AMAZON.</span></h1>
        <p>No se trata de terminar videos. Se trata de avanzar por logros,<br class="desktop-break"> desde tus accesos hasta tu primera venta, pago y utilidad real.</p>
        <div class="hero-mantra">AVANZA POR LOGROS, NO POR PRISA.</div>
      </div>
      <div class="character-duo" aria-label="Personajes oficiales de VendeComoPro">
        <img class="character-woman" src="assets/characters/woman-teaching.png" alt="Coach VendeComoPro enseñando" width="300" height="440">
        <img class="character-man" src="assets/characters/man-folder.png" alt="Estudiante VendeComoPro listo para avanzar" width="260" height="430">
      </div>
    </section>
    <section class="profile" aria-label="Personaliza tu ruta"><label>Tu sistema<select id="system"><option value="medium">Sistema Definitivo · Medium</option><option value="elite">Sistema Élite · VIP avanzado</option></select></label><label>Tu residencia<select id="residence"><option value="usa">Dentro de Estados Unidos</option><option value="outside">Fuera de Estados Unidos</option></select></label><a id="course" class="button" target="_blank" rel="noopener noreferrer">Abrir mi curso ↗</a><div id="benefits" class="benefits"></div></section>
    <section class="progress-card" aria-label="Tu progreso"><div><strong id="progress-label">0 de 12 estaciones completas</strong><span id="task-count">0 tareas completadas</span></div><progress id="progress" value="0" max="12" aria-label="Estaciones completadas"></progress><button id="resume" class="text-link" type="button">Mi próxima estación →</button></section>
    <p class="save-note" id="save-note">Tu avance se guarda solo en este navegador y dispositivo. Conecta tu cuenta arriba para sincronizar con tus coaches.</p>
    <section class="milestones" aria-label="Los cinco hitos de tu primer ciclo"><div class="section-head"><h2>TU META NO ES VERLO TODO. ES LOGRAR ESTO.</h2></div><div id="milestones" class="milestone-grid"></div></section>
    <section class="route-heading" aria-labelledby="route-title"><div><p class="eyebrow">AVANZA POR LOGROS, NO POR PRISA</p><h2 id="route-title">TU MUNDO DE EXPEDICIÓN</h2><p>Abre una estación. Completa su misión. Continúa a tu ritmo.</p></div><div class="view-switch" role="group" aria-label="Vista de la ruta"><button type="button" data-view="trail" aria-pressed="true">Ruta paso a paso</button><button type="button" data-view="map" aria-pressed="false">Mapa de expedición</button></div></section>
    <section id="trail" class="trail" aria-label="Ruta paso a paso"></section>
    <div id="map-workspace" class="workspace" hidden>
      <section class="journey" aria-label="Mapa de la expedición">
        <div class="section-head">
          <h2>EL MAPA DE TU ÉXITO</h2>
          <span>12 ESTACIONES</span>
        </div>
        <div class="map brand-map">
          <svg class="route-line" viewBox="0 0 1000 600" role="img" aria-label="Camino conectado de doce estaciones">
            <path d="M95 190 C170 120 230 130 300 160 S430 210 515 165 S680 105 790 155 S930 225 890 300 C850 365 725 340 655 350 S475 395 360 350 S175 360 140 465 C185 540 315 515 430 485 S670 455 820 495" fill="none" stroke="currentColor" stroke-width="13" stroke-linecap="round" stroke-dasharray="18 18"/>
          </svg>
          <span class="map-label map-label-start">CAMPAMENTO BASE</span>
          <span class="map-label map-label-finish">PAGO + UTILIDAD</span>
          <img class="map-character map-character-coach" src="assets/characters/woman-pointing.png" alt="Coach VendeComoPro señalando la ruta" width="220" height="390">
          <img class="map-character map-character-student" src="assets/characters/man-action.png" alt="Estudiante VendeComoPro avanzando" width="260" height="420">
          <div id="map-nodes"></div>
        </div>
        <p class="map-tip">Selecciona un número en el mapa o una estación para ver tu misión.</p>
        <nav id="stations" aria-label="Estaciones de la ruta"></nav>
      </section>
      <section id="detail" class="detail" tabindex="-1" aria-label="Detalle de la estación"></section>
    </div>
    <section class="learning" aria-labelledby="class-title"><div><p class="eyebrow">ACOMPAÑAMIENTO EN VIVO</p><h2 id="class-title">PRIMERO LAS BASES.<br><span>DESPUÉS, LAS CLASES.</span></h2><p>Te recomendamos completar como mínimo el <strong>módulo 8</strong> antes de entrar regularmente a las clases grupales. Así podrás entender los temas y aprovechar tus preguntas.</p></div><div class="class-card"><div><b>Lunes</b><span>Análisis, búsqueda, desbloqueos y mayoreo.</span></div><div><b>Martes</b><span>Preguntas y respuestas.</span></div><div><b>Miércoles</b><span>Seller Central, inventario y apertura de cuenta.</span></div><p><strong>7:00 p. m. · hora de Miami</strong><br>Enlace: <b>#link-clase-en-vivo</b> · Fechas: <b>#calendario-m</b></p></div><div class="recordings"><h3>Resuelve tu duda con una grabación</h3><p>Ambos sistemas tienen acceso a Grabaciones de Zoom. Usa <strong>Search</strong> y escribe una palabra clave: <b>apertura, análisis, búsqueda, envíos, cupones, cashback, pagos o Seller Central</b>. Después, lleva las dudas que queden a la clase en vivo.</p><a class="button secondary" href="https://class.vendecomopro.net/products/grabaciones-de-zoom" target="_blank" rel="noopener noreferrer">Abrir grabaciones de Zoom ↗</a></div></section>
    <section class="sessions"><div><p class="eyebrow">ASESORÍAS INDIVIDUALES</p><h2>RESERVA A TIEMPO.</h2><p id="session-info"></p><p>Cada sesión dura <strong>45 minutos</strong> y consume una de tus sesiones del mes. No se acumulan: las que no uses se pierden al terminar el mes. Los cupos suelen llenarse a final de mes.</p></div><div><a class="button" href="https://lp.vendecomopro.net/asesorias-individuales" target="_blank" rel="noopener noreferrer">Agendar mi sesión ↗</a><p>También encontrarás el enlace en <strong>#agenda-1a1</strong>. Elige entre los coaches disponibles en la página.</p></div></section>
    <section id="recursos" class="resources" aria-labelledby="resources-title"><p class="eyebrow">TU MOCHILA DE RECURSOS</p><h2 id="resources-title">LAS GUÍAS, SIEMPRE A MANO.</h2><div class="download-grid" id="downloads"></div></section>
    <section class="next-level"><p class="eyebrow">DESPUÉS DE TU PRIMER CICLO</p><h2>SIGUIENTE NIVEL</h2><p>Estos contenidos no bloquean tu primera venta. Primero completa un ciclo sencillo; después incorpora lo que corresponda a tu operación.</p><div class="next-grid"><article><b>Módulo 15</b><h3>FBM</h3><p>Preparación y cumplimiento de pedidos por el vendedor.</p></article><article><b>Módulo 17</b><h3>Internacional</h3><p>Listados internacionales.</p></article><article><b>Módulos 18 al 20</b><h3>Protección</h3><p>Hazmat, meltable, salud de la cuenta y Sección 3.</p></article><article><b>Módulos 21 al 23</b><h3>Escalamiento</h3><p>Wholesale, análisis al por mayor y múltiples cuentas.</p></article></div><p><strong>Módulo 16:</strong> Sellerboard se incorpora al ciclo principal para la contabilidad; BQool/repricer queda para una fase posterior, con acompañamiento.</p></section>
  </main>
  <footer><span>VENDECOMOPRO · AVANZA POR LOGROS, NO POR PRISA.</span><button id="reset" type="button">Reiniciar mi progreso</button></footer>
  <dialog id="reset-dialog"><h2>¿Reiniciar tu progreso?</h2><p>Se desmarcarán tus tareas de Medium y Élite en este navegador. Esta acción no cambia tu cuenta de Amazon ni tu progreso en la academia.</p><div><button id="cancel-reset" class="button secondary">Conservar mi avance</button><button id="confirm-reset" class="button">Sí, reiniciar</button></div></dialog>
  <dialog id="student-dialog" class="auth-dialog">
    <div class="auth-dialog-header">
      <h2 id="auth-dialog-title">Acceder a mi cuenta</h2>
      <p id="auth-dialog-desc">Ingresa el correo con el que estás dado de alta en la academia para recibir un enlace de acceso directo sin contraseña.</p>
    </div>
    <div id="auth-error" class="auth-error-msg" role="alert" style="display:none"></div>
    <div id="auth-success" class="auth-success-msg" role="status" style="display:none;background:#14532d30;border:1px solid #22c55e66;color:#86efac;padding:12px 14px;border-radius:8px;font-size:0.88rem;line-height:1.5;margin-bottom:16px"></div>
    <form id="student-form" method="dialog" novalidate>
      <div class="form-group">
        <label for="student-email-input">Correo electrónico registrado *</label>
        <input type="email" id="student-email-input" placeholder="tu-correo@ejemplo.com" required autocomplete="email">
      </div>
      <div class="dialog-actions">
        <button type="button" id="student-dialog-cancel" class="button secondary">Cerrar</button>
        <button type="submit" id="student-dialog-submit" class="button">Enviar enlace mágico ✨</button>
      </div>
      <p style="font-size:0.8rem;color:var(--muted);margin-top:18px;text-align:center;line-height:1.4">
        El registro público está cerrado. Solo pueden acceder estudiantes dados de alta por la academia. Si acabas de adquirir el curso y no puedes ingresar, contacta a tu asesor.
      </p>
    </form>
  </dialog>
  <div id="notice" role="status" aria-live="polite"></div>
</body>
</html>
