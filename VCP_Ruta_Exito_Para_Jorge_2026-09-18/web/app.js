'use strict';
const URLS={medium:'https://class.vendecomopro.net/products/vendecomopro-el-sistema-definitivo-2',elite:'https://class.vendecomopro.net/products/vendecomopro-elite-2',academy:'https://class.vendecomopro.net/library',zoom:'https://class.vendecomopro.net/products/grabaciones-de-zoom',agenda:'https://lp.vendecomopro.net/asesorias-individuales',apertura:'https://drive.google.com/file/d/1UHS7EPOIcp1GeEFS7khszRQGrB0vdCBE/view',analisis:'https://drive.google.com/file/d/1TfnIfd37HpK1l_DTH0p5OaForgWIpiXs/view',checklist:'https://drive.google.com/file/d/14Q-S9Y85pj90Ewq2oIsKk61HahoOgnaJ/view',prep:'https://drive.google.com/file/d/1MZO6gJ2qUFGX2QfDLWD1_Nt4F7EV_V9w/view',impresora:'https://drive.google.com/file/d/1NJR4GbnGg4IpasdzcYwzsHNYs2GGMAYI/view',venta:'https://drive.google.com/file/d/1803F1pzWQ_SkHHj4N-gqiW0L66iFIuhN/view',hushed:'https://hushed.com/',talkyou:'https://www.talkyou.me/es/index.html'};
const GENERAL='María Mesa, Vicky Valdez, Sofía Mesa, Yeaniel Morón, Legna Vargas, Sergio NG o Gael T.';
const ANALYSIS='Oriana Aldrey, Andrés Munera, Yeaniel Morón, Legna Vargas, Sergio NG, Gabriel o Gael T.';
const TITLES=['Campamento base','Onboarding con Miguel','Apertura de cuenta Seller Central','Aprender a analizar','Busca tus primeros productos','Compra','Logística','Envío a Amazon FBA','Amazon recibe','Gestión en Seller Central','Primera venta','Pago + utilidad real'];
const POSITIONS=[[10,32],[27,27],[44,28],[60,27],[77,26],[91,30],[34,56],[59,56],[80,56],[18,81],[43,81],[80,82]];
const STAGES=[
 {title:TITLES[0],description:'Antes de estudiar, deja tus accesos listos. No necesitas aprender todos los canales de Discord todavía.',result:'Entras al curso correcto, Zoom y Discord sin problemas.',why:'Evitas detener tu aprendizaje por accesos o contraseñas.',modules:[],tasks:[['academia','Entré a la Academia y confirmé que puedo abrir el curso de mi sistema.'],['discord','Puedo entrar a Discord en computadora y celular con la misma cuenta, sin duplicar usuarios.'],['zoom','Tengo Zoom listo y mi nombre y apellido visibles.'],['passwords','Guardé mis accesos y sé cómo recuperar una contraseña sin crear otra cuenta.']],channel:'#soporte-técnico',coaches:'Miguel Rondón.',tip:'Durante el onboarding se explicarán los canales principales. La guía de Discord y el kit de accesos se entregarán por separado.',links:[['Abrir Academia','academy']]},
 {title:TITLES[1],description:'Participa una sola vez en el onboarding con Miguel Rondón. Se realiza todos los días a las 6:00 p. m., hora de Miami, y dura 30 minutos.',result:'Sabes cómo usar la academia, ubicar los canales y dar tu siguiente paso.',why:'Comienzas con un orden claro y sabes dónde pedir ayuda.',modules:[],tasks:[['session','Asistí una vez al onboarding o recibí la grabación correspondiente y resolví mis dudas iniciales.'],['channels','Sé encontrar los canales principales que Miguel explicó en el onboarding.'],['help','Entendí cómo preguntar en un canal y cómo contactar por privado a un coach.'],['next','Mi siguiente paso está claro: estudiar los módulos 1 al 5 y preparar mi apertura de cuenta.']],channel:'#soporte-técnico',coaches:'Miguel Rondón · sesión grupal; queda grabada y se envía al estudiante.',tip:'No tienes que entrar todos los días. Llega con Academia, Discord y Zoom funcionando. El enlace del onboarding lo proporciona el equipo; si no lo tienes, solicítalo en soporte.',links:[['Entrar a mi curso','course']]},
 {title:TITLES[2],description:'Estudia los módulos 1 al 5 y prepara los requisitos de tu caso antes de abrir y configurar tu tienda en Seller Central.',result:'Tu cuenta está abierta y la configuración principal queda lista para operar.',why:'Sin una cuenta operativa y configurada no puedes completar el ciclo de venta.',modules:[['Módulo 1 · Bienvenida','Cómo avanzar y dónde volver.'],['Módulo 2 · Fundamentos de Amazon','Reventa, operación, logística y jerga básica.'],['Módulo 3 · Apertura de cuenta','Requisitos, individual o empresa, tarifas y correo de trabajo.'],['Módulo 4 · Payoneer','Proveedor para recibir pagos cuando corresponda a tu caso.'],['Módulo 5 · Configuración de Seller Central','Cuenta bancaria, tarjeta y marketplaces.'],['Checkpoint A','Fundamentos y configuración inicial.']],tasks:[['modules','Completé los módulos 1 al 5 y el Checkpoint A.'],['requirements','Revisé los requisitos que corresponden a mi residencia y a mi tipo de cuenta.'],['account','Puedo ingresar a mi cuenta de Seller Central y revisé su estado de verificación.'],['bank','Configuré la cuenta bancaria y la tarjeta de débito o crédito.'],['markets','Revisé qué marketplaces debo mantener activos y entiendo las tarifas principales.']],channel:'#gestion-de-cuenta-amazon-seller-central',coaches:GENERAL,session:'María Mesa o Vicky Valdez para dudas específicas de apertura y gestión de la cuenta. Es una recomendación, no un requisito.',tip:'Si tu caso requiere revisión, pregunta en el canal o escribe por privado a un coach. No improvises la información que Amazon solicita.',links:[['Estudiar módulos 1 al 5','course'],['Guía de apertura','apertura']]},
 {title:TITLES[3],description:'Aprende a interpretar la información de un producto antes de invertir. Instala Keepa y RevSeller, configura las herramientas y practica con productos reales.',result:'Puedes explicar por qué validarías o descartarías un producto.',why:'El análisis te ayuda a detectar riesgos y proteger tu capital antes de comprar.',modules:[['Módulo 6 · Herramientas básicas','Qué son Keepa y RevSeller y para qué se utilizan.'],['Módulo 7 · Análisis básico','Instalación, configuración, Buy Box, variaciones, Lead List y base de datos.']],tasks:[['installed','Instalé y configuré Keepa y RevSeller en mi computadora.'],['keepa','Puedo leer el historial de precios y la competencia de un producto en Keepa.'],['roi','Puedo calcular margen y ROI con RevSeller e identificar los costos del análisis.'],['practice','Practiqué con productos de #new-leads y anoté mi decisión y sus razones.'],['correction','Pedí corrección de un análisis cuando tuve dudas, antes de comprar.']],channel:'#análisis-y-desbloqueo-de-productos',coaches:ANALYSIS,session:'Sofía Mesa, Oriana Aldrey o Andrés Munera para instalar herramientas. Oriana Aldrey o Andrés Munera para practicar el análisis.',tip:'Rutina sugerida de 20 minutos: elige un producto de #new-leads, revisa Keepa y Buy Box, calcula margen y ROI, anota tu decisión y pregunta en el canal. Practica cada día.',links:[['Estudiar módulos 6 y 7','course'],['Manual de análisis','analisis'],['Checklist de análisis','checklist']]},
 {title:TITLES[4],description:'Convierte tu criterio de análisis en una lista corta de productos que puedas vender. Busca, compara y revisa tu elegibilidad antes de pensar en la compra.',result:'Tienes candidatos analizados y una decisión documentada para cada uno.',why:'Encontrar un producto no basta: debes comprobar que sea adecuado para tu cuenta y tu operación.',modules:[['Módulo 8 · Búsqueda de productos','Búsqueda inversa, filtros Keepa, cupones, cashback y revisión de tiendas.'],['Módulo 9 · Antes de la primera compra','Entiende las restricciones y comprueba la elegibilidad de tu cuenta.'],['Módulo 14 · Refuerzo del análisis','Lectura combinada Keepa + SmartScout + RevSeller; ScanProfit y UPC Scanner cuando apliquen a tu búsqueda.'],['Módulo 10 · Complementario','SmartScout amplía tu búsqueda; no es un requisito para tu primera compra.']],tasks:[['module8','Estudié el módulo 8 y ejecuté una búsqueda de productos.'],['restrictions','Estudié el módulo 9 y comprobé las restricciones de cada candidato en mi cuenta.'],['module14','Revisé las lecciones necesarias del módulo 14 para reforzar mi análisis.'],['shortlist','Creé una lista corta con ASIN, costo, precio analizado, costos estimados y razón de validación.'],['question','Resolví mis dudas sobre la lista antes de avanzar a la compra.']],channel:'#análisis-y-desbloqueo-de-productos',coaches:ANALYSIS,session:'Oriana Aldrey o Andrés Munera para comenzar a buscar y revisar candidatos.',tip:'Esta estación no exige desbloquear marcas. Comprueba primero si puedes vender el producto; los desbloqueos se trabajan más adelante según el historial y los requisitos de Amazon. Desde el módulo 8 puedes aprovechar mejor las clases grupales.',links:[['Abrir módulos de búsqueda','course'],['Checklist de análisis','checklist']]},
 {title:TITLES[5],description:'Haz una primera compra controlada: pocas unidades, producto correcto, costos documentados y logística prevista antes de pagar.',result:'Compraste inventario validado y conservas la documentación de la operación.',why:'Una prueba controlada reduce la exposición al riesgo y permite aprender con datos reales.',modules:[['Módulo 11 · Compras online','Dirección de entrega, billing address y proceso de compra.']],tasks:residence=>[['reanalysis','Reanalicé el producto justo antes de comprar; confirmé ASIN, listing y elegibilidad.'],['costs','Validé costo, tarifas, margen y ROI; incluí preparación y envío en mi cálculo.'],...(residence==='outside'?[['prep-before-buy','Contacté y coordiné mi prep center antes de pagar; tengo dirección y requisitos confirmados.'],['phone-before-buy','Activé mi número virtual y revisé la guía de compras para residentes fuera de Estados Unidos.']]:[['home-before-buy','Confirmé que puedo recibir y preparar los productos en casa.']]),['small','Compré pocas unidades para mi primera prueba.'],['receipts','Guardé factura o comprobante, detalle de la compra y tracking.']],channel:'#análisis-y-desbloqueo-de-productos',coaches:ANALYSIS,session:'Oriana Aldrey o Andrés Munera para practicar la búsqueda y la primera compra.',tip:residence=>residence==='outside'?'Antes de pagar, visita también la estación Logística: el prep center y el número virtual deben estar listos. Si necesitas la guía de compras fuera de USA, solicítala en el canal de análisis.':'Después de confirmar tu compra, adquiere los insumos de preparación indicados en Logística para tenerlos listos cuando lleguen los productos.',links:[['Estudiar módulo 11','course']]},
 {title:TITLES[6],description:residence=>residence==='outside'?'Coordina la recepción y preparación con un prep center. Este contacto se hace antes de tu primera compra. Activa también una línea virtual.':'Organiza tu estación de preparación en casa. Compra los insumos después de confirmar tu primera compra, para tenerlos listos cuando llegue el inventario.',result:'Tus productos tienen un lugar de recepción y un proceso de preparación definidos.',why:'Evitas comprar inventario que no puedas recibir o preparar correctamente.',modules:[],tasks:residence=>residence==='outside'?[['prep-contact','Contacté el prep center antes de comprar y confirmé dirección, código de cliente y forma de entrega.'],['prep-terms','Pregunté por restricciones, recepción, preparación, tarifas y tiempos de despacho.'],['prep-incidents','Confirmé cómo manejan fotos, discrepancias y devoluciones.'],['virtual-line','Activé una línea virtual: Hushed como opción principal o TalkYou como alternativa.']]:[['supplies','Tengo impresora térmica, etiquetas 4x6 y 2x1, polybags, cajas y báscula.'],['inspection','Sé cómo inspeccionar y fotografiar el producto recibido antes de prepararlo.'],['packaging','Revisé con Sofía qué etiquetado y embalaje requiere cada producto.'],['special-labels','Tengo los materiales para paquetes o productos frágiles cuando corresponde.']],channel:'#envio-de-productos-fba-y-fbm',coaches:'Sofía Mesa como coach principal de logística; el equipo general también puede orientarte en Discord.',session:'Sofía Mesa para preparación y logística.',tip:residence=>residence==='outside'?'Hushed es la opción principal. TalkYou es la segunda opción; $4,99 al mes es el precio de referencia indicado en la guía. Confirma tarifas y condiciones con el proveedor antes de contratar.':'No todos los productos necesitan el mismo embalaje. Confirma qué materiales corresponden a tu inventario antes de preparar las unidades.',extra:residence=>residence==='outside'?'<div class="links">'+link('Hushed · principal','hushed')+link('TalkYou · alternativa','talkyou')+link('Guía de prep centers','prep')+'</div>':'<h3>Lista de materiales</h3><p style="color:var(--muted);font-size:.9rem">Estos son los enlaces de referencia compartidos por la academia. Comprueba modelo, tamaño, compatibilidad y disponibilidad antes de comprar.</p><div class="materials">'+MATERIALS.map(([label,url])=>`<a href="${url}" target="_blank" rel="noopener noreferrer">${label} ↗</a>`).join('')+'</div>',links:[['Guía de impresora','impresora']]},
 {title:TITLES[7],description:'Sigue el proceso del módulo 12 para preparar y despachar tu primer inventario a los centros logísticos de Amazon FBA.',result:'Tu inventario está correctamente identificado, preparado y en camino a Amazon.',why:'Un plan de envío bien revisado ayuda a evitar errores y retrasos.',modules:[['Módulo 12 · Envío de productos FBA','Configuración inicial, cómo enlistar, plan de envío y seguimiento.'],['Checkpoint B','Herramientas, análisis, compras online y envíos a FBA.']],tasks:[['configuration','Revisé la configuración inicial para realizar envíos.'],['listing','Enlisté mis productos y confirmé el ASIN y la información correcta.'],['prep','Preparé o coordiné la preparación de los productos y revisé sus etiquetas.'],['plan','Creé y revisé el plan de envío con las unidades y cajas correctas.'],['dispatch','Despaché el inventario y guardé tracking y documentación.'],['checkpoint','Revisé el Checkpoint B y sé cómo dar seguimiento al envío.']],channel:'#envio-de-productos-fba-y-fbm',coaches:'Sofía Mesa.',session:'Sofía Mesa para crear tu primer envío y revisar la preparación.',tip:'Si es tu primer envío o no sabes qué datos colocar, pide revisión antes de despachar. Tu sesión individual de envío consume una sesión del mes.',links:[['Estudiar módulo 12','course']]},
 {title:TITLES[8],description:'Da seguimiento al inventario mientras Amazon lo recibe y procesa. Aprovecha este tiempo para preparar tu gestión de precios y reunir los costos reales.',result:'Confirmas que tus unidades aparecen disponibles en inventario.',why:'Un envío entregado no significa que todas sus unidades estén inmediatamente disponibles para la venta.',modules:[['Empieza el módulo 13 · Gestión de cuenta','Panel principal, recorrido por el menú y cómo crear un caso.']],tasks:[['tracking','Revisé el tracking y el estado del envío en Seller Central.'],['incidents','Documenté diferencias o incidencias y consulté cómo gestionarlas si hubo algún problema.'],['prices','Reanalicé Buy Box y competencia y definí mi rango de precios.'],['learn13','Empecé el módulo 13 y reuní los costos de compra, preparación y envío.'],['available','Confirmé las unidades disponibles en Gestión de todo el inventario.']],channel:'#envio-de-productos-fba-y-fbm · #gestion-de-cuenta-amazon-seller-central',coaches:GENERAL,session:'Sofía Mesa para incidencias de envío; María Mesa o Vicky Valdez para gestión específica de Seller Central.',tip:'La guía utiliza 2 a 3 semanas como referencia aproximada para el proceso hasta quedar activo. El tiempo puede variar. La señal para avanzar es ver el inventario disponible, no simplemente que el transportista marque entregado.',links:[['Estudiar módulo 13','course']]},
 {title:TITLES[9],description:'Aprende a operar tu tienda: ubica inventario, precios, pedidos y la opción para abrir un caso cuando necesites ayuda de Amazon.',result:'Puedes revisar tu inventario, modificar precios y encontrar tus órdenes sin depender de otra persona.',why:'La tienda necesita seguimiento activo cuando el producto queda disponible.',modules:[['Módulo 13 · Gestión de cuenta Amazon Seller','Panel principal + recorrido por el menú; programa de incentivos y cómo crear un caso.']],tasks:[['module13','Completé las lecciones del módulo 13 que necesito para manejar mi tienda.'],['inventory','Sé encontrar mis unidades disponibles en Gestión de todo el inventario.'],['price-change','Sé dónde modificar el precio de venta en Gestión de todo el inventario.'],['orders','Sé encontrar y revisar mis pedidos en Gestión de pedidos.'],['case','Sé cómo crear un caso y reunir la información necesaria cuando hay una incidencia.']],channel:'#gestion-de-cuenta-amazon-seller-central',coaches:GENERAL,session:'María Mesa o Vicky Valdez para gestión específica de cuentas.',tip:'Para distribuir el soporte, puedes preguntar en el canal o escribir por privado a cualquiera de los coaches de apoyo general. Para una sesión individual específica de gestión, reserva con María Mesa o Vicky Valdez.',links:[['Estudiar módulo 13','course'],['Guía de primera venta','venta']]},
 {title:TITLES[10],description:'Con el inventario disponible, revisa el producto otra vez y gestiona su precio con criterio. Sigue tus pedidos y aprende a solicitar reseñas por los mecanismos permitidos.',result:'Identificas tu primera venta real y sabes qué revisar después.',why:'El análisis no termina cuando compras; el mercado puede cambiar mientras el inventario llega a Amazon.',modules:[['Módulo 13 + práctica en Seller Central','Inventario, precios y pedidos.']],tasks:[['active','Verifiqué que el producto y sus unidades están disponibles.'],['new-analysis','Reanalicé Buy Box, competencia y margen con las condiciones actuales.'],['minimum','Definí un precio mínimo considerando mis costos y tarifas.'],['adjust','Revisé o ajusté el precio sin romper mi mínimo rentable.'],['first-order','Vi mi primera venta en Gestión de pedidos y revisé su estado.'],['review','Aprendí cómo solicitar una reseña por los mecanismos permitidos de Amazon cuando corresponde.']],channel:'#gestion-de-cuenta-amazon-seller-central',coaches:GENERAL,session:'María Mesa o Vicky Valdez para revisar precios, órdenes y manejo de la tienda.',tip:'Gestionar el precio no garantiza una venta ni una fecha de venta. Evita ajustes impulsivos y confirma con tu coach cualquier duda sobre reseñas o manejo de órdenes.',links:[['Guía de primera venta','venta'],['Abrir mi curso','course']]},
 {title:TITLES[11],description:'Distingue una venta, un desembolso de Amazon y la utilidad de tu negocio. Reúne todos los costos y utiliza Sellerboard para comparar lo estimado con lo real.',result:'Entiendes tu primer pago y puedes medir la utilidad real del ciclo.',why:'Vender no es lo mismo que cobrar, y cobrar no es lo mismo que tener ganancia.',modules:[['Seller Central · Pagos','Ventas, ajustes, tarifas, reservas y desembolsos.'],['Módulo 16 · Sellerboard','Lección de gestión y control de finanzas. El repricer/BQool se integra después.'],['Cierre · Tiempos de Amazon y flujo de caja','Revisa la lección de tiempos, flujo de caja y cómo escalar.']],tasks:[['payments','Localicé la sección de pagos y sé distinguir ventas, tarifas, reservas y desembolsos.'],['flow','Consulté con mi coach las dudas sobre el flujo de pagos de mi cuenta.'],['sellerboard','Revisé la lección de Sellerboard del módulo 16 y lo configuré para mi contabilidad.'],['all-costs','Registré compra, preparación, envío, tarifas, herramientas y otros gastos del negocio.'],['first-payment','Identifiqué mi primer desembolso de Amazon y confirmé su recepción.'],['profit','Comparé ingresos y costos para calcular la utilidad real de mi primer ciclo.']],channel:'#gestion-de-cuenta-amazon-seller-central',coaches:GENERAL,session:'María Mesa o Vicky Valdez para dudas específicas del flujo de pagos y gestión de la cuenta.',tip:'Venta - costos - tarifas = utilidad real. El calendario de desembolsos y las reservas dependen de tu cuenta. No confundas el saldo mostrado por Amazon con dinero ya depositado o con la ganancia final.',links:[['Estudiar Sellerboard y cierre','course']]}
];
const MATERIALS=[['Impresora térmica','https://www.amazon.com/LabelRange-Ecommerce-Bluetooth-Shipping-Compatible/dp/B0CKVWQLGK'],['Etiquetas 4x6','https://www.amazon.com/dp/B097D96V2X'],['Etiquetas 2x1','https://www.amazon.com/ROLLO-Direct-Thermal-Barcode-Labels/dp/B08NDMLGDB'],['Polybags','https://www.amazon.com/dp/B01MT1XILS'],['Báscula','https://www.amazon.com/dp/B0BD8BHGQ7'],['Cajas · Uline','https://www.uline.com/Grp_9/Corrugated-Boxes'],['Cajas · Lowe’s','https://www.lowes.com/pl/moving-boxes-supplies/moving-boxes/4294713229'],['Cajas · TotalPack','https://totalpack.com/packaging-materials/boxes-corrugated.html'],['Etiquetas para paquetes','https://www.amazon.com/dp/B0BZ542GSV/'],['Etiquetas para frágil','https://www.amazon.com/dp/B0B184XZB5/']];
const DOWNLOADS=[['GUÍA COMPLETA','Ruta de Medium y Élite','26 páginas','guia-completa.pdf'],['MAPA VISUAL','Roadmap del éxito','1 página','roadmap.pdf'],['GUÍA 1 DE 3','Inicio, cuenta y análisis','13 páginas','guia-1.pdf'],['GUÍA 2 DE 3','Productos, compra y logística','8 páginas','guia-2.pdf'],['GUÍA 3 DE 3','Seller Central, venta y pago','7 páginas','guia-3.pdf']];
const KEY='vcp-success-route-v1';
let state={system:'medium',residence:'usa',selected:0,view:'trail',checks:{}};let saveAvailable=true;
try{const stored=JSON.parse(localStorage.getItem(KEY)||'null');if(stored&&['medium','elite'].includes(stored.system)&&['usa','outside'].includes(stored.residence)){state={...state,...stored,selected:Math.max(0,Math.min(11,Number(stored.selected)||0)),checks:stored.checks&&typeof stored.checks==='object'?stored.checks:{}};}}catch{saveAvailable=false;}

// Sincronización en la Nube (Vercel + Supabase + GoHighLevel)
const urlParams=new URLSearchParams(window.location.search);
let currentStudent={
  email:(urlParams.get('email')||localStorage.getItem('vcp-student-email')||'').trim().toLowerCase(),
  name:(urlParams.get('name')||localStorage.getItem('vcp-student-name')||'').trim()
};
if(currentStudent.email)localStorage.setItem('vcp-student-email',currentStudent.email);
if(currentStudent.name)localStorage.setItem('vcp-student-name',currentStudent.name);
if(urlParams.has('system')&&['medium','elite'].includes(urlParams.get('system')))state.system=urlParams.get('system');
if(urlParams.has('residence')&&['usa','outside'].includes(urlParams.get('residence')))state.residence=urlParams.get('residence');

const $=id=>document.getElementById(id);
state.view=['trail','map'].includes(state.view)?state.view:'trail';
let trailOpen=state.selected;
const MILESTONES=[['Cuenta lista',2,'Abierta y configurada.'],['Producto validado',4,'Analizado y apto para tu cuenta.'],['Inventario enviado',7,'Preparado y en camino a Amazon.'],['Primera venta',10,'Una orden real en tu tienda.'],['Pago + utilidad',11,'Cobro y costos comprendidos.']];
const PHASES=['Accesos y herramientas de comunicación','Una sesión para comenzar con claridad','Módulos 1 al 5 + Checkpoint A','Módulos 6 y 7 · Keepa + RevSeller','Módulos 8 y 9 + refuerzo del módulo 14','Módulo 11 · primera compra controlada','Recepción y preparación según tu residencia','Módulo 12 + Checkpoint B','Seguimiento y disponibilidad del inventario','Módulo 13 · inventario, precios y pedidos','Reanálisis y seguimiento de tu primera orden','Pagos + Sellerboard del módulo 16'];

function getStats(){
  const count=TITLES.filter((_,i)=>completed(i)).length;
  let totalTasks=0,completedTasks=0;
  TITLES.forEach((_,i)=>{
    const t=tasksFor(i);
    totalTasks+=t.length;
    completedTasks+=t.filter(([id])=>state.checks[taskKey(i,id)]===true).length;
  });
  const ach=[...MILESTONES].reverse().find(([_,i])=>completed(i));
  return {
    completedStations:count,
    totalStations:12,
    completedTasks,
    totalTasks,
    percentage:totalTasks>0?Math.round((completedTasks/totalTasks)*100):0,
    currentStationName:TITLES[state.selected]||'',
    currentMilestone:ach?ach[0]:'Iniciando'
  };
}

function updateSyncStatus(status){
  const dot=$('sync-dot'),btn=$('user-btn'),note=$('save-note');
  if(!dot||!btn)return;
  dot.className='sync-dot '+(status||'');
  if(currentStudent.email){
    btn.textContent=currentStudent.name||currentStudent.email;
    btn.title=`Conectado como ${currentStudent.email} (Click para cambiar)`;
    if(note){
      if(status==='synced')note.textContent=`🟢 Conectado como ${currentStudent.email} · Tu avance se sincroniza con tus coaches en GoHighLevel.`;
      else if(status==='syncing')note.textContent=`🟡 Guardando cambios en la nube...`;
      else if(status==='error')note.textContent=`🔴 Guardado localmente (sin conexión a la nube). Se reintentará al conectar.`;
    }
  }else{
    btn.textContent='Conectar mi cuenta';
    btn.title='Conecta tu correo de estudiante para guardar en la nube';
    if(note)note.textContent='Tu avance se guarda solo en este navegador. Conecta tu cuenta arriba para sincronizar con tus coaches.';
  }
}

async function syncTaskToServer(index,id,checked){
  if(!currentStudent.email)return;
  updateSyncStatus('syncing');
  try{
    const res=await fetch('/api/progress',{
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body:JSON.stringify({
        email:currentStudent.email,
        name:currentStudent.name,
        system:state.system,
        residence:state.residence,
        taskKey:taskKey(index,id),
        completed:checked,
        stats:getStats()
      })
    });
    const data=await res.json();
    updateSyncStatus(data.ok?'synced':'error');
  }catch(err){
    console.warn('Sync failed:',err);
    updateSyncStatus('error');
  }
}

async function syncProfileToServer(){
  if(!currentStudent.email)return;
  updateSyncStatus('syncing');
  try{
    await fetch('/api/progress',{
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body:JSON.stringify({
        email:currentStudent.email,
        name:currentStudent.name,
        system:state.system,
        residence:state.residence,
        stats:getStats()
      })
    });
    updateSyncStatus('synced');
  }catch(e){
    updateSyncStatus('error');
  }
}

async function fetchProgressFromServer(){
  if(!currentStudent.email){updateSyncStatus('');return;}
  updateSyncStatus('syncing');
  try{
    const res=await fetch(`/api/progress?email=${encodeURIComponent(currentStudent.email)}`);
    const data=await res.json();
    if(data.ok&&data.checks){
      state.checks={...state.checks,...data.checks};
      if(data.student){
        if(!urlParams.has('system')&&data.student.system)state.system=data.student.system;
        if(!urlParams.has('residence')&&data.student.residence)state.residence=data.student.residence;
        if(data.student.fullName&&!currentStudent.name){
          currentStudent.name=data.student.fullName;
          localStorage.setItem('vcp-student-name',currentStudent.name);
        }
      }
      save();
      render();
      updateSyncStatus('synced');
    }else{
      updateSyncStatus('synced');
    }
  }catch(err){
    console.warn('Fetch failed:',err);
    updateSyncStatus('error');
  }
}

function save(){try{localStorage.setItem(KEY,JSON.stringify(state));}catch{saveAvailable=false;$('save-note').textContent='Este navegador no permite guardar el avance. Puedes usar la ruta, pero tus marcas se perderán al cerrar la página.';}}
function escapeHTML(str){return String(str).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));}
function taskKey(stage,id){return `${state.system}:${stage}:${id}`;}
function tasksFor(index){const s=STAGES[index];return s?(typeof s.tasks==='function'?s.tasks(state.residence):s.tasks):[];}
function completed(index){const t=tasksFor(index);return t.length>0&&t.every(([id])=>state.checks[taskKey(index,id)]===true);}
function notice(text){$('notice').textContent=text;$('notice').classList.add('visible');setTimeout(()=>$('notice').classList.remove('visible'),2600);}
function selectStage(index,focus=false){if(!Number.isInteger(index)||index<0||index>11)throw new Error('Estación inválida');state.selected=index;trailOpen=index;save();render();if(focus){const target=state.view==='trail'?$('trail-toggle-'+index):$('detail');target.focus({preventScroll:true});target.scrollIntoView({behavior:window.matchMedia('(prefers-reduced-motion: reduce)').matches?'instant':'smooth',block:'start'});}}
function setTask(index,id,checked){if(!tasksFor(index).some(([taskId])=>taskId===id)||typeof checked!=='boolean')throw new Error('Tarea inválida');const before=completed(index);state.checks[taskKey(index,id)]=checked;save();renderProgress();renderStations();renderMilestones();document.querySelectorAll('input[data-task]').forEach(input=>{if(Number(input.dataset.taskStage)===index&&input.dataset.task===id){input.checked=checked;input.closest('.task').classList.toggle('checked',checked);}});updateTrailProgress();document.querySelectorAll(`[data-task-progress="${index}"]`).forEach(el=>el.textContent=taskStatus(index));if(!before&&completed(index))notice('¡Estación completa! Continúa con tu siguiente misión.');syncTaskToServer(index,id,checked);}
function link(label,key){const href=key==='course'?URLS[state.system]:URLS[key];return `<a class="button secondary" href="${href}" target="_blank" rel="noopener noreferrer">${escapeHTML(label)} ↗</a>`;}
function renderProgress(){const count=TITLES.filter((_,i)=>completed(i)).length;const tasks=TITLES.reduce((n,_,i)=>n+tasksFor(i).filter(([id])=>state.checks[taskKey(i,id)]).length,0);$('progress-label').textContent=`${count} de 12 estaciones completas`;$('task-count').textContent=`${tasks} tareas completadas`;$('progress').value=count;}
function renderStations(){$('stations').innerHTML=TITLES.map((title,i)=>`<button type="button" class="station ${completed(i)?'complete':''}" data-stage="${i}" aria-current="${state.selected===i}"><span class="number">${completed(i)?'✓':String(i+1).padStart(2,'0')}</span><span>${escapeHTML(title)}<small>${completed(i)?'Completada':state.selected===i?'Estás aquí':'Ver misión'}</small></span></button>`).join('');$('map-nodes').innerHTML=TITLES.map((title,i)=>`<button type="button" class="map-node ${completed(i)?'complete':''}" style="left:${POSITIONS[i][0]}%;top:${POSITIONS[i][1]}%" data-stage="${i}" title="${i+1}. ${escapeHTML(title)}" aria-label="Estación ${i+1}: ${escapeHTML(title)}" aria-current="${state.selected===i}">${completed(i)?'✓':i+1}</button>`).join('');}
function taskStatus(i){const tasks=tasksFor(i);return completed(i)?'✓ Estación completada':`${tasks.filter(([id])=>state.checks[taskKey(i,id)]===true).length} de ${tasks.length} tareas completas`;}
function renderMilestones(){$('milestones').innerHTML=MILESTONES.map(([title,i,summary],n)=>`<article class="milestone ${completed(i)?'achieved':''}"><span class="milestone-number">${completed(i)?'✓':String(n+1).padStart(2,'0')}</span><div><h3>${escapeHTML(title)}</h3><p>${escapeHTML(summary)}</p><span class="milestone-status">${completed(i)?'Hito logrado':'Estación '+(i+1)}</span></div></article>`).join('');}
function stageBody(i,withHeading=false){const s=STAGES[i],tasks=tasksFor(i);const tip=typeof s.tip==='function'?s.tip(state.residence):s.tip;const description=typeof s.description==='function'?s.description(state.residence):s.description;const extra=typeof s.extra==='function'?s.extra(state.residence):s.extra||'';return `${withHeading?`<p class="eyebrow">ESTACIÓN ${String(i+1).padStart(2,'0')} / 12</p><h2>${escapeHTML(s.title)}</h2>`:''}<div class="mission"><span class="content-label">QUÉ VAS A HACER</span><p class="description">${escapeHTML(description)}</p></div><div class="outcomes"><div><span>QUÉ VAS A LOGRAR</span><p>${escapeHTML(s.result)}</p></div><div><span>POR QUÉ IMPORTA</span><p>${escapeHTML(s.why)}</p></div></div><div class="support"><h3>Dónde pedir ayuda</h3><p><b>Canal de Discord:</b> <span class="channel">${escapeHTML(s.channel)}</span></p><p><b>${i===1?'Onboarding grupal':'Apoyo en Discord'}:</b> ${escapeHTML(s.coaches)}</p>${s.session?`<p><b>Sesión 1 a 1:</b> ${escapeHTML(s.session)}</p>`:''}</div>${s.modules.length?'<h3>Qué estudiar</h3><ul class="module-list">'+s.modules.map(([title,body])=>`<li><strong>${escapeHTML(title)}</strong>${escapeHTML(body)}</li>`).join('')+'</ul>':''}<h3>Tu misión · marca lo que ya hiciste</h3><div class="tasks">${tasks.map(([id,label])=>{const checked=state.checks[taskKey(i,id)]===true;return `<label class="task ${checked?'checked':''}"><input type="checkbox" data-task-stage="${i}" data-task="${id}" ${checked?'checked':''}><span>${escapeHTML(label)}</span></label>`;}).join('')}</div><div class="status" data-task-progress="${i}">${taskStatus(i)}</div>${tip?`<p class="tip">${escapeHTML(tip)}</p>`:''}${extra}<div class="links">${s.links.map(([title,key])=>link(title,key)).join('')}${s.session?link('Agendar 1 a 1','agenda'):''}</div><div class="stage-nav"><button class="text-link" type="button" data-stage="${i-1}" ${i===0?'disabled':''}>← Anterior</button>${i<11?`<button class="button" type="button" data-stage="${i+1}">Siguiente estación →</button>`:'<a class="button" href="#recursos">Ver mis guías ↓</a>'}</div>`;}
function renderDetail(){$('detail').innerHTML=stageBody(state.selected,true);}
function renderTrail(){$('trail').innerHTML=STAGES.map((s,i)=>{const open=trailOpen===i;return `<article class="trail-station ${open?'open':''} ${completed(i)?'complete':''}" data-trail-stage="${i}"><div class="trail-pin" aria-hidden="true">${completed(i)?'✓':String(i+1).padStart(2,'0')}</div><div class="trail-card"><h3 class="trail-title"><button id="trail-toggle-${i}" class="trail-toggle" type="button" data-trail-toggle="${i}" aria-expanded="${open}" aria-controls="trail-body-${i}"><span class="trail-title-text"><span class="trail-phase">${escapeHTML(PHASES[i])}</span><span class="trail-name">${escapeHTML(s.title)}</span><span class="trail-summary">${escapeHTML(s.result)}</span></span><span class="trail-badge">${completed(i)?'Completada':'Por completar'}</span><span class="chevron" aria-hidden="true"></span></button></h3><div id="trail-body-${i}" class="trail-body" role="region" aria-labelledby="trail-toggle-${i}" ${open?'':'hidden'}>${open?stageBody(i):''}</div></div></article>`;}).join('');}
function updateTrailProgress(){document.querySelectorAll('[data-trail-stage]').forEach(el=>{const i=Number(el.dataset.trailStage),done=completed(i);el.classList.toggle('complete',done);el.querySelector('.trail-pin').textContent=done?'✓':String(i+1).padStart(2,'0');el.querySelector('.trail-badge').textContent=done?'Completada':'Por completar';});}
function renderViews(){$('trail').hidden=state.view!=='trail';$('map-workspace').hidden=state.view!=='map';document.querySelectorAll('[data-view]').forEach(b=>b.setAttribute('aria-pressed',String(b.dataset.view===state.view)));}
function render(){$('system').value=state.system;$('residence').value=state.residence;$('course').href=URLS[state.system];$('benefits').textContent=state.system==='elite'?'Élite: 12 meses de Discord · 2 sesiones 1 a 1 al mes · 24 sesiones durante el acceso · clases especiales según calendario.':'Definitivo: 6 meses de Discord · 1 sesión 1 a 1 al mes · 6 sesiones durante el acceso.';$('session-info').textContent=state.system==='elite'?'Tu sistema Élite incluye 2 sesiones individuales al mes durante 12 meses.':'Tu Sistema Definitivo incluye 1 sesión individual al mes durante 6 meses.';renderProgress();renderMilestones();renderStations();renderDetail();renderTrail();renderViews();}
$('system').addEventListener('change',e=>{state.system=e.target.value;save();render();syncProfileToServer();});$('residence').addEventListener('change',e=>{state.residence=e.target.value;save();render();syncProfileToServer();});
document.addEventListener('click',e=>{const view=e.target.closest('button[data-view]');if(view){state.view=view.dataset.view;save();renderViews();return;}const toggle=e.target.closest('button[data-trail-toggle]');if(toggle){const i=Number(toggle.dataset.trailToggle);if(trailOpen===i){trailOpen=null;renderTrail();}else selectStage(i);$('trail-toggle-'+i).focus({preventScroll:true});return;}const b=e.target.closest('button[data-stage]');if(b&&!b.disabled)selectStage(Number(b.dataset.stage),b.closest('#detail,.trail-body')!==null||window.innerWidth<761);});
document.addEventListener('change',e=>{if(e.target.matches('input[data-task]'))setTask(Number(e.target.dataset.taskStage),e.target.dataset.task,e.target.checked);});
$('resume').addEventListener('click',()=>{const next=TITLES.findIndex((_,i)=>!completed(i));selectStage(next===-1?11:next,true);});
$('reset').addEventListener('click',()=>$('reset-dialog').showModal());$('cancel-reset').addEventListener('click',()=>$('reset-dialog').close());$('confirm-reset').addEventListener('click',()=>{state.checks={};state.selected=0;trailOpen=0;save();render();$('reset-dialog').close();notice('Tu progreso se reinició en este dispositivo.');});
$('downloads').innerHTML=DOWNLOADS.map(([tag,title,count,file])=>`<a class="download" href="assets/${file}" download><div><span class="tag">${tag}</span><b>${title}</b></div><span>${count} · Descargar PDF ↓</span></a>`).join('');

if($('user-btn'))$('user-btn').addEventListener('click',()=>{
  if($('student-email-input'))$('student-email-input').value=currentStudent.email||'';
  if($('student-name-input'))$('student-name-input').value=currentStudent.name||'';
  $('student-dialog').showModal();
});
if($('student-dialog-cancel'))$('student-dialog-cancel').addEventListener('click',()=>$('student-dialog').close());
if($('student-form'))$('student-form').addEventListener('submit',e=>{
  e.preventDefault();
  const email=$('student-email-input').value.trim().toLowerCase();
  const name=$('student-name-input').value.trim();
  if(!email)return;
  currentStudent.email=email;
  currentStudent.name=name;
  localStorage.setItem('vcp-student-email',email);
  localStorage.setItem('vcp-student-name',name);
  $('student-dialog').close();
  notice(`¡Bienvenido! Sincronizando con ${email}...`);
  fetchProgressFromServer().then(()=>syncProfileToServer());
});

render();
updateSyncStatus(currentStudent.email?'syncing':'');
fetchProgressFromServer();
if(!saveAvailable)$('save-note').textContent='Este navegador no permite guardar el avance. Tus marcas no se conservarán al cerrar la página.';
const modelContext=document.modelContext;
if(modelContext?.registerTool){
 const lifecycle=new AbortController();
 const register=tool=>{try{Promise.resolve(modelContext.registerTool(tool,{signal:lifecycle.signal})).catch(()=>{});}catch{}};
 register({name:'read_route_progress',title:'Leer avance de la ruta',description:'Lee las estaciones y tareas marcadas en este navegador, sin modificar el progreso.',inputSchema:{type:'object',properties:{},additionalProperties:false},annotations:{readOnlyHint:true,untrustedContentHint:false},execute(){return {system:state.system,residence:state.residence,selectedStation:state.selected+1,stations:TITLES.map((title,i)=>({station:i+1,title,complete:completed(i),tasks:tasksFor(i).map(([id,label])=>({id,label,checked:state.checks[taskKey(i,id)]===true}))}))};}});
 register({name:'navigate_route_station',title:'Abrir estación de la ruta',description:'Abre una estación de la ruta; no completa sus tareas.',inputSchema:{type:'object',properties:{station:{type:'integer',minimum:1,maximum:12}},required:['station'],additionalProperties:false},annotations:{readOnlyHint:false,untrustedContentHint:false},execute(input){if(!input||!Number.isInteger(input.station)||input.station<1||input.station>12)throw new Error('Estación inválida');selectStage(input.station-1,true);return {selectedStation:input.station,title:TITLES[input.station-1]};}});
 register({name:'set_route_task_completion',title:'Marcar tarea de la ruta',description:'Marca o desmarca una tarea concreta en el progreso local del estudiante.',inputSchema:{type:'object',properties:{station:{type:'integer',minimum:1,maximum:12},taskId:{type:'string'},completed:{type:'boolean'}},required:['station','taskId','completed'],additionalProperties:false},annotations:{readOnlyHint:false,untrustedContentHint:false},execute(input){if(!input||!Number.isInteger(input.station)||input.station<1||input.station>12)throw new Error('Estación inválida');setTask(input.station-1,input.taskId,input.completed);return {station:input.station,taskId:input.taskId,completed:input.completed,stationComplete:completed(input.station-1)};}});
 window.addEventListener('pagehide',()=>lifecycle.abort(),{once:true});
}
