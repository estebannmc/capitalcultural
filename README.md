# Capital Cultural · rediseño

Rediseño de **[Capital Cultural](https://www.santafeciudad.gov.ar/capitalcultural/)**, el sitio de la Secretaría de Cultura de la Municipalidad de Santa Fe.

## El sitio

| | |
|---|---|
| **Plataforma** | WordPress, en subcarpeta `/capitalcultural/` |
| **Tema** | Astra 4.13.1 (Brainstorm Force) |
| **Maquetador** | Elementor Free — sin Theme Builder, que es de Pro |
| **Escala** | 510 páginas, no todas publicadas ni visibles |
| **Acceso** | Admin de WordPress. **Sin acceso al servidor**: todo se instala por ZIP desde el panel |

Que Elementor sea Free define el reparto: **Astra renderiza header, footer, archivos de agenda, entradas y búsqueda**, así que la mayor parte del sitio se rediseña por child theme. Elementor sólo manda dentro de las páginas armadas con él.

## El rediseño

Aplica la identidad SF 2026 —Brandbook V1.0, febrero 2026— con el material **GlassMorph** que ya se usa en otros proyectos del estudio.

Decisiones tomadas:

- **Paleta cerrada**: Azul 01 `#002751`, celeste `#57b8e9`, la submarca Cultura en rojo `#e63312` o naranja `#fca100` y los neutros. El verde institucional se abandonó por decisión política: Verde 01, Verde 02, Azul 02 y amarillo quedan fuera y no se declaran como tokens.
- **Tema claro únicamente.** El Brandbook no contempla modo oscuro.
- **Todo lo de marca se inclina 15° a la derecha**: el lockup, el remate de barras, el marcador de los títulos, el barrido de los botones y el degradé celeste → azul.
- **Accesibilidad AA como piso**, no como aspiración: es un sitio de gobierno y le aplica la Ley 26.653 y la ODA 1.0 de ONTI. Los contrastes están medidos, no estimados.

## Contenido del repositorio

| Archivo | Qué es |
|---|---|
| `docs/plan-glassmorph.md` | El plan completo: 8 fases, 28–36 días hábiles, riesgos y decisiones |
| `docs/design-tokens.css` | Tokens de marca y material GlassMorph, listos para encolar en el child theme |
| `docs/barras.svg` | El remate de ocho barras, partial para el child theme |
| `docs/sistema-visual.html` | El sistema visual completo, navegable. Abrilo en cualquier navegador |
| `docs/sistema-visual.pdf` | Lo mismo en PDF, 10 páginas A4, para compartir o imprimir |
| `marca/logoFooter.svg` | La marca completa para fondo oscuro: bloque SF calado + escudo. Es el asset oficial, sin tocar |
| `marca/SF_Brandbook_V2_*.pdf` | Manual de marca (67 páginas normativas) |
| `marca/SF_paleta_cromática.pdf` | Ficha cromática 02.4 |
| `sitio-actual/cssAdicionalCCAstra.css` | El CSS adicional que hoy está cargado en Astra (*Personalizar → CSS adicional*) |
| `sitio-actual/botonesCC.js` | Script que posiciona la barra de los botones `.cb-btn` según el texto |
| `sitio-actual/header.html` · `footer.html` | El HTML de los bloques de Ultimate Addons que arman la cabecera y el pie |
| `sitio-nuevo/header.html` | **El header nuevo**, liquid glass: se pega entero en el widget HTML del bloque de header de UAE, en lugar de `sitio-actual/header.html` |
| `docs/header-glass.html` | Vista previa del header nuevo sobre una página de prueba. Abrilo en cualquier navegador |
| `sitio-actual/pluginsCC.txt` | Plugins instalados en el WordPress, con versión; los marcados *¿es necesario?* están en revisión |
| `sitio-actual/paginasUtilCC.docx` | Listado de páginas del sitio |
| `sitio-actual/capitalcultural.WordPress.2026-10-22.xml` | Export de las páginas del WordPress (*Herramientas → Exportar*), con los datos de Elementor. De acá salen los textos e imágenes de las páginas nuevas |
| `sitio-nuevo/huellas-de-mi-ciudad.html` | **Huellas de mi Ciudad**: texto y los tres recorridos (Marcas, Memorias Feministas, Patrimonio). Widget HTML entero |
| `sitio-nuevo/marcas-de-la-memoria.html` | **Marcas de la Memoria** con la marca de José “Pepe” Serra adentro: texto, foto y audio, en el ancla `#jose-pepe-serra` |
| `sitio-nuevo/memorias-urbanas-feministas.html` | **Memorias Urbanas Feministas**: las 13 baldosas en orden cronológico, con tarjeta a cada ficha |
| `sitio-nuevo/centro-cultural-municipal.html` | **Centro Cultural Municipal**: texto, los dos espacios (Biblioteca Muttis y Fotogalería) y el shortcode `[ccmunicipal]` con 50 px abajo. Widget HTML entero |
| `sitio-nuevo/biblioteca-muttis.html` | **Biblioteca Profesor Enrique Muttis**: texto, botón al catálogo, datos de visita, servicios, cómo asociarse, colección e historia, con datos estructurados `Library` de schema.org. Widget HTML entero |
| `sitio-nuevo/anfiteatro.html` | **Anfiteatro Municipal Juan de Garay**: texto, los dos accesos (Teatro en el Anfi y Capacitaciones 4x4), datos de visita con la foto aérea, las seis características del espacio y autoridades, con datos estructurados `PerformingArtsTheater`. Widget HTML entero |
| `sitio-nuevo/mercado-progreso.html` | **Mercado Progreso**: presentación, las nueve propuestas, datos de visita con la foto del patio, historia y autoridades, con datos estructurados `EventVenue`. Widget HTML entero |
| `sitio-nuevo/museos-y-salas.html` | **Museos y Salas**: presentación, los diez espacios con foto en dos grupos (museos y salas) y las 15 publicaciones digitales en grilla de tapas, con número, año, espacio y peso del PDF. Widget HTML entero |
| `sitio-nuevo/somos-musica.html` | **Somos Música**: presentación, inscripción con el formulario y el contacto de la Secretaría, las ocho sedes por tipo de instrumento con enlace a OpenStreetMap, galería de 19 fotos con visor y los logos de quienes acompañan, con datos estructurados `EducationalOccupationalProgram`. Widget HTML entero |
| `sitio-nuevo/espacios/` | **Las 10 fichas de Museos y Salas**: presentación, *Visitá…* con horarios, contacto y mapa de OpenFreeMap, y el título de propuestas. Un widget HTML por página, generado con `generar.py` desde `plantilla.html` |
| `sitio-nuevo/img/redes/` | Imágenes para redes de 1200 × 630 de seis espacios, ya en Medios |
| `sitio-nuevo/img/espacios/` | Siete fotos a color de los espacios de Museos y Salas, en 390 × 290 (16–40 KB cada una), ya en Medios |
| `sitio-nuevo/img/publicaciones/` | Las 15 tapas de las publicaciones digitales: la primera página de cada PDF, en JPG de 566 px de alto (24–56 KB cada una, 569 KB en total). Se suben a Medios |
| `sitio-nuevo/fichas-memorias-feministas.html` | El código de las 13 fichas de baldosas, con *Copiar código* y vista previa. Abrilo en el navegador |
| `sitio-nuevo/festival-disena-comun.html` | **Festival Diseña, bloque común**: Encode Sans variable (única tipografía), estilos `.fd`, menú de la sección e íconos. Primer widget HTML de las cuatro páginas |
| `sitio-nuevo/festival-disena-inicio.html` | **Festival Diseña, inicio**: hero con la foto en tamaño 1536, cifras, ciclo en cinco pasos, galería en mosaico con visor y video que carga YouTube al tocar play |
| `sitio-nuevo/festival-disena-trayectoria.html` | **Festival Diseña, Historia y Trayectoria**: datos clave, línea de hitos, rubros, herramientas de apoyo y Expresiva |
| `sitio-nuevo/festival-disena-formaciones.html` | **Festival Diseña, Formaciones**: ciclo en curso, archivo de clínicas plegado por año y los 15 disertantes con iniciales y CV en ventana |
| `sitio-nuevo/festival-disena-docs.html` | **Festival Diseña, Documentación Oficial**: las 220 marcas seleccionadas con buscador y los siete PDF |

### Huellas de mi Ciudad: lo que queda a mano

- **La página vieja de José “Pepe” Serra** (`/marcas-de-la-memoria/jose-pepe-serra/`, página 20751) sigue publicada, pero ya nada la enlaza. Para no dejar una URL huérfana, pasala a borrador y redirigila a `/huellas-de-mi-ciudad/marcas-de-la-memoria/#jose-pepe-serra`. Yoast SEO gratis no hace redirecciones: hace falta Yoast Premium o un plugin como *Redirection*.
- **Las 13 fichas de baldosas usan la plantilla de ancho completo de Redux**; las de Patrimonio, *Elementor ancho completo* (`elementor_header_footer`) con una sección de ancho por defecto. Con la de Redux la ficha se ensancha y cambia la proporción entre texto y foto. Antes de pegar el código, cambiarla en lote: *Páginas → tildar las 13 → Acciones en lote → Editar → Plantilla*.
- **Siete baldosas no tienen foto** en el sitio (Trata, Unidades básicas, Sindicalistas, Orígenes, Sociabilidad lésbica, Desocupadas y Guardianas). En el listado llevan el remate de barras sobre azul, chico y en la esquina inferior derecha; en la ficha van sólo con texto, a una columna.
- **Se corrigieron erratas** del texto publicado: *resstringia*, *pais*, *entre e las calles*, *serian*, *Paulo Freyre*, *éranos* y el guion de *“Basta” - La huelga*. El botón viejo decía *Pepe Sierra*: el nombre es Serra.

### Biblioteca Muttis: lo que queda a mano

- **El botón del catálogo apunta a `#`.** Cuando esté la URL del sistema de consulta, se cambia el `href` del `.cb-btn`.
- **Yoast** quedó cargado el 02/11/2026: ver *SEO cargado* más abajo.
- **La URL vieja `/capitalcultural/bibliotecamuttis/` da 404**, y con ella las páginas hijas (*Café y literatura*, *Cursos y talleres*…). Conviene redirigirla a `/centroculturalmunicipal/bibliotecamuttis/` con *Redirection* para no perder los enlaces que ya tenía.
- **Los datos de contacto vienen de la ficha de la Red de Bibliotecas** y no tienen correo. La Fotogalería publica otro horario (lunes a viernes de 8 a 20): confirmar cuál es el de la biblioteca antes de publicar. El horario también está en el JSON-LD del final del código, así que hay que cambiarlo en los dos lugares.
- **La dirección es Primera Junta 2451**, confirmada. Wikipedia y la Red de Bibliotecas todavía dicen Cortada Falucho 2450: el edificio da a las dos calles.
- **La historia del edificio** sale de la nota de UNO Santa Fe del 11/09/2020 sobre el acondicionamiento de la sala de las bóvedas, y la de Muttis, de Wikipedia. No se encontró año de construcción ni arquitecto del Banco Municipal.

### Anfiteatro: lo que queda a mano

- **Publicada el 02/11/2026**, como un solo widget HTML con `anfiteatro.html`, y con la foto ya en Medios (`2026/11/anfiteatro-juan-de-garay-aerea.jpg`, ID 33415). El escudo municipal se sacó: ya está en el header y el footer.
- **El correo no coincide.** La página publicada muestra `anfiteatro.juandegaray@santafeciudad.gov.ar` pero el enlace manda a `anfiteatrojuandegaray@gmail.com`. El código nuevo usa el institucional en los dos lados; confirmar que esa casilla existe y se lee antes de publicar. Si no, cambiarlo en el `mailto:`, en el texto y en el JSON-LD.
- **«Coordinador Teatro Municipal»** figura entre las autoridades del Anfiteatro. Se dejó tal cual; confirmar si es el cargo correcto o si falta la coordinación propia del Anfiteatro.
- **La foto** es `sitio-nuevo/img/anfiteatro-juan-de-garay-aerea.jpg` (880 × 660, 103 KB), un recorte de la aérea `SELECCIONADA-10A-120-cm-x-80-cm-300-ppp.jpg` con foco en gradas y escenario.
- **No usar la aérea original en la web.** Está en Medios (ID 33413) con 6300 × 9450 px y 31 MB, y WordPress no le generó ningún tamaño: cualquier página que la use carga los 31 MB enteros. Es un archivo para imprimir (120 × 80 cm a 300 ppp). Si se quiere tener en Medios para la web, subir una versión de 2560 px de lado mayor.
- **La foto aérea anterior** (`2021/06/anfiEdit.jpg`) **tiene marca de agua y no es nuestra.** Se sacó de la página nueva, pero sigue en Medios; conviene revisar si otra página la usa.
- **No hay horario de atención** en la página publicada, así que no se inventó. Si la boletería tiene uno, va como un `<div><dt>Horario</dt><dd>…</dd></div>` más en *Visitá el Anfiteatro*.
- **Se sacó el botón «Convocatorias vigentes»**, que llevaba fuera de Capital Cultural, a la página general de la Secretaría.
- **Cambios de texto:** *Ubicación* y *Accesibilidad* dejaron de ser tarjetas y pasaron a los datos de visita, donde se buscan. Se corrigieron *Caracteristicas* y *podo táctiles*, y los títulos van en minúscula (*Bajo tablas*, *Sobre tablas*) como en el resto del sitio.

### Mercado Progreso: lo que queda a mano

- **Publicada el 02/11/2026**, como un solo widget HTML con `mercado-progreso.html` (página 24705). Se borraron las otras tres secciones de Elementor: el texto, el contacto y la de autoridades con el escudo, que ya está en el header y el footer.
- **El widget quedó en la sección de los botones viejos**, que tenía 50 px de margen arriba. El CSS del archivo anula el margen y el relleno de su sección (`.elementor-section:has(#mp)`), así que entre las migas y el texto queda la misma distancia que en Centro Cultural Municipal.
- **La foto** es la del patio central de Federico Cairoli que ya estaba en Medios (`2021/04/04.Mercado_Progreso_-_Ph.Federico_Cairoli_low.jpg`, ID 12320, 1280 × 853), en su tamaño de 1024. No lleva crédito en la página, igual que las anteriores; si se quiere, va como `<figcaption>`.
- **Las fotos de Marcela Pucci del centenario** (IDs 27980 y 27607) tienen la ñ de *Años* descompuesta en el nombre del archivo: la URL con la ñ normal da 404. Si se usan, copiar la URL desde Medios, no escribirla a mano.
- **No hay horario de atención** en la página publicada, así que no se inventó. Si lo hay, va como un `<div><dt>Horario</dt><dd>…</dd></div>` más en *Visitá el Mercado Progreso*.
- **Las nueve propuestas** van en una grilla de 3 × 3: primero las vigentes y al final las de 2025 (*Aniversario* y *Teatro en el Mercado 2025*). Cuando haya *Teatro en el Mercado 2027*, la de 2025 se puede sacar.
- **Se sacó «Coordinador Teatro Municipal»** de las autoridades: no corresponde al Mercado.
- **Presentación e historia van separadas**, como en la Biblioteca Muttis: arriba qué es el Mercado hoy y abajo, antes de las autoridades, *Historia del Mercado Progreso*. Se sacaron *estratégicamente* y *actualmente*, y los nombres de los ciclos van en minúscula salvo el primer término (*Cine bajo las estrellas*).
- **La historia sale de la nota de El Litoral por el centenario** (31/01/2025, *El Mercado Progreso cumple 100 años: su historia y un misterio…*), que a su vez cita el libro *Inventario. 200 obras del patrimonio arquitectónico de Santa Fe*: la Ordenanza 2087 de 1922, la concesión a Luis Coll, la inauguración del 31/01/1925, las medidas, los puestos y el abandono. No se encontró arquitecto ni constructora.
- **Dos datos para confirmar.** La nota fecha la obra de Barletta en 2010 en el texto y en 2011 en una foto; se dejó 2011, como decía la página. La obra del escenario de 9 × 5 m figura «10 años más tarde, en la gestión de Emilio Jatón», sin año, así que va como «una década después».
- **La dirección es Balcarce 1635**, confirmada. La página vieja decía 1625; se corrigió en los datos de visita, el enlace al mapa, el JSON-LD y la meta descripción de Yoast.

### Museos y Salas: lo que queda a mano

- **Publicada el 04/11/2026** (página 20080), como un solo widget HTML con `museos-y-salas.html` en la primera sección de Elementor. Se borraron las otras dos (botones y publicaciones). El CSS anula el margen y el relleno de esa sección: entre las migas y el texto quedan 44 px en escritorio y 36 px en celular.
- **Las 15 tapas están en Medios** (IDs 33502 a 33516) y **las 7 fotos de los espacios** también (IDs 33495 a 33501), todas en `2026/11/` con el mismo nombre que en el repo. Se usan en su tamaño original, no en los que genera WordPress.
- **Las tapas son la primera página de cada PDF.** Doce son A4; *Fortaleza de la infancia*, *Catálogo abierto* y *Clases de apoyo* son 9:16 y se ven enteras en el recuadro A4, con un margen gris a los costados.
- **La imagen para redes** es `sitio-nuevo/img/museos-y-salas-redes.jpg`, la fachada del CEC en 1200 × 630 (ID 33517).
- **Siete espacios tienen foto a color, sacada de Drive.** CEC, Fotogalería, López Claro y Sor Josefa usan la foto de fachada de la tapa de su dossier 2026, que está en la carpeta *Dossier espacios*; el Museo de la Constitución, la de la tapa de `MCN_Dossier2026.pdf`. El Museo del Teatro usa la araña del Teatro Municipal, de Marcela Pucci (`MPucci_TeatroMunicipal_16052024_056.jpg`), y la Sala Mercado Editorial, una foto de la Noche de los Museos (`NocheDeLosMuseosMercadoEditorial2.jpg`). Las de los dossiers no tienen el nombre del fotógrafo.
- **Tres siguen con la foto en blanco y negro del backup**, ya en Medios, porque en Drive no hay fotos limpias a color, sólo piezas de redes con texto encima o fotos de archivo: Manzana Jesuítica (`2021/05/museoinmaculada-390x290.jpg`), Museo de la Ciudad (`2021/05/museodelaciudad-390x290.jpg`) y Sala Ariel Ramírez (`2022/12/salaRamirez-390x290.jpg`). Cuando haya una foto a color, se sube en 390 × 290 y se cambia el `src`.
- **José Pedroni: obras completas** (#6, 2024) va sin espacio. Si es de la Sala Mercado Editorial o de otro, sumar `<span class="ms-pub__espacio">…</span>` después del título.
- **El espacio de las demás publicaciones sale del nombre del archivo**, salvo *Penumbra*, confirmada en el Museo Municipal César López Claro.
- **Al sumar una publicación:** exportar la primera página del PDF en JPG de 566 px de alto, subirla a Medios y copiar un `<li>` al principio de la lista, con su tapa, número, año, título, espacio y peso del PDF.
- **Cambios de texto:** se partió la presentación en dos párrafos y se acortó. Los títulos de las publicaciones van en minúscula salvo el primer término, como en el resto del sitio. *Entre la lanza y el sable* suma *+ El mundo es bello antes de ser verdadero*, como dice su tapa. El botón decía «Manzana Jesuítica y Colegio Inmaculada»; ahora usa el nombre de su página, «Manzana Jesuítica y Museo Colegio Inmaculada».

### Somos Música: lo que queda a mano

- **Sin publicar.** Va como un solo widget HTML con `somos-musica.html` en la primera sección de Elementor (página 24875); el resto de las secciones (textos, botón, los dos acordeones, la galería y los logos) se borran. El CSS anula el margen y el relleno de su sección, como en el Mercado Progreso, y deja 50 px hasta el footer.
- **Contacto:** `cultura@santafeciudad.gov.ar` y el teléfono de la Secretaría, (0342) 457-1609, el mismo del footer. Están también en el JSON-LD del final.
- **La galería baja de ~40 MB a menos de 1 MB.** La vieja cargaba los 19 originales (unos 2 MB cada uno, ~40 MB) aunque nadie abriera el visor. Ahora la grilla usa el recorte de 400 × 400 de WordPress y el visor el de 1536, que se baja recién al abrir cada foto. *Concierto en barrio El Pozo* no tiene tamaños intermedios en Medios: el visor usa el original, de 145 KB.
- **Sedes:** los ocho enlaces de Google Maps pasaron a OpenStreetMap con las coordenadas de cada lugar. El Club Cabaña Leiva no tenía coordenadas en Google y OpenStreetMap no tiene la altura 8300 de Grandoli: el punto es la calle Grandoli en Cabaña Leiva, aproximado. Si se sabe la dirección de las demás sedes, va como `<span class="sm-nota">…</span>`, como en el club.
- **«Inscripción abierta»** se dejó como decía la página vieja («Continúa abierta la inscripción…»). Cuando cierre, cambiar el título y sacar el botón al formulario de Google.
- **El JSON-LD `FAQPage` que generaba Elementor** con los acordeones (preguntas que eran nombres de escuelas) desaparece con ellos.
- **Cambios de texto:** *Igualdad de oportunidades* se sumó a la presentación. Se corrigieron *Continua*, *Gral.Brigadier E. López* (es Brigadier General Estanislao López) y los nombres van con *N.º* y comillas latinas. «SOMOS Música» va como «Somos Música», el título de la página.
- **Yoast, para cargar:** hoy la imagen para redes es el logo de Terranza.

| Frase clave | Título SEO | Meta descripción | Imagen para redes |
|---|---|---|---|
| `Somos Música` | `Somos Música, orquestas infantiles y juveniles \| Santa Fe` | `Orquestas infantiles y juveniles gratuitas en barrios de Santa Fe, para niñas, niños y adolescentes de 7 a 14 años. Los instrumentos se prestan.` (144) | `2025/06/15-noviembre-2024-SOMOS-Musica-1.jpg` |

### Fichas de los espacios: lo que queda a mano

- **Publicadas el 04/11/2026**, las diez. Cada página quedó con una sola sección: el widget HTML de `sitio-nuevo/espacios/<slug>.html` y, debajo, el shortcode de propuestas. Hay 50 px hasta el footer. El Museo de la Constitución conserva sus dos secciones propias (Parque, Museo y Auditorio, y el material didáctico) debajo de la ficha, y los 50 px van al pie de la última.
- **Para cambiar un texto, un horario o un contacto**, se edita `ESPACIOS` en `generar.py`, se corre `python sitio-nuevo/espacios/generar.py` y se pega el `.html` de ese espacio en su widget. Los horarios van con «horas», nunca con «h».
- **El mapa es MapLibre con el estilo de OpenFreeMap**, como en Patrimonio Público, y carga cuando se acerca a la pantalla. Los enlaces son a OpenStreetMap: no queda ningún Google Maps. Las coordenadas salen de Nominatim; la Fotogalería se ubicó por el frente sobre el Pasaje Falucho, porque OpenStreetMap no tiene la altura 2451 de Primera Junta.
- **Shortcodes corregidos.** La Sala Ariel Ramírez usaba `[salaramirez]` y el Mercado Editorial `[salamercado]`, que no existen: no mostraban ninguna propuesta. Ahora son `[salaarielramirez]` (10 propuestas) y `[salamercadoeditorial]` (13). El Museo del Teatro tenía las propuestas en un widget HTML y pasó al shortcode `[museodelteatro]`. La lista de shortcodes está en *Programación Cultural → Ayuda y shortcodes*.
- **El Museo de la Ciudad es itinerante**: va sin dirección ni mapa, y el horario dice «Según cada muestra».
- **El Museo de la Constitución no tiene propuestas cargadas** en Programación Cultural (0). Si se cargan, se suma un widget con `[museoconstitucion]`.
- **La Sala Ariel Ramírez no tenía horario ni dirección.** Se tomó de sus propuestas: planta alta de la Estación Belgrano, Bv. Gálvez 1150, y abre en los conciertos. No tiene correo propio.
- **El WhatsApp del CEC no coincidía**: el texto decía 511-8351 (el del Museo de la Constitución) y el enlace 511-8392. Quedó el 511-8392; confirmar.
- **Se borraron los dos botones de la Fotogalería**: uno llevaba a `/bibliotecamuttis/`, que da 404, y el otro a `#`. Ahora el texto enlaza al Centro Cultural Municipal y a la Biblioteca Muttis.
- **Los títulos de las páginas no se tocaron**: el del CEC sigue siendo «Sala CEC», que es el que aparece como título y en las migas.
- **Los textos de las propuestas** (los que carga cada espacio en Programación Cultural) todavía usan «h» en los horarios. Eso se corrige en cada propuesta, no en las fichas.

| Página | ID | Frase clave | Título SEO | Imagen para redes |
|---|---|---|---|---|
| CEC | 20428 | `Centro Experimental del Color` | `Centro Experimental del Color (CEC) \| Santa Fe` | Fachada del CEC (ID 33517) |
| Fotogalería | 24139 | `Fotogalería Municipal` | `Fotogalería Municipal \| Santa Fe` | Entrada de la Fotogalería (ID 33525) |
| Manzana Jesuítica | 23414 | `Manzana Jesuítica` | `Manzana Jesuítica y Museo Colegio Inmaculada \| Santa Fe` | `2021/05/museoinmaculada.jpg` (ID 13013) |
| Museo de la Ciudad | 20091 | `Museo de la Ciudad` | `Museo de la Ciudad \| Santa Fe` | `2021/05/museodelaciudad-scaled.jpg` (ID 13014) |
| Museo de la Constitución | 22929 | `Museo de la Constitución Nacional` | `Museo de la Constitución Nacional \| Santa Fe` | Fachada del dossier 2026 (ID 33526) |
| López Claro | 27565 | `Museo César López Claro` | `Casa Museo César López Claro \| Santa Fe` | Fachada (ID 33527) |
| Sor Josefa | 20341 | `Museo Sor Josefa` | `Museo de Artes Visuales Sor Josefa Díaz y Clucellas \| Santa Fe` | Frente del museo (ID 33528) |
| Museo del Teatro | 23358 | `Museo del Teatro` | `Museo del Teatro Municipal 1° de Mayo \| Santa Fe` | La araña del Teatro, de Marcela Pucci (ID 33529) |
| Sala Ariel Ramírez | 15198 | `Sala Ariel Ramírez` | `Sala Ariel Ramírez \| Santa Fe` | `2022/12/salaRamirez.jpg` (ID 20278) |
| Sala Mercado Editorial | 20519 | `Sala Mercado Editorial` | `Sala Mercado Editorial \| Santa Fe` | Noche de los Museos (ID 33530) |

Las meta descripciones se cargaron en Yoast, una por página, de 140 a 149 caracteres, con la dirección del espacio cuando la tiene.

### SEO cargado: Centro Cultural, Biblioteca Muttis, Anfiteatro, Mercado Progreso y Museos y Salas

Cargado en la caja de Yoast el 02/11/2026 y verificado en las páginas publicadas. En las tres primeras sólo se tocó Yoast; el Mercado Progreso se cargó junto con la página nueva.

| Página | Frase clave | Título SEO | Imagen para redes |
|---|---|---|---|
| Centro Cultural Municipal | `Centro Cultural Municipal` | `Centro Cultural Municipal \| Santa Fe` | Feria de la Fotogalería, de Marcela Pucci (ID 31147) |
| Biblioteca Muttis | `Biblioteca Muttis` | `Biblioteca Municipal Profesor Enrique Muttis \| Santa Fe` | La fachada, `2024/09/BMuttis.jpg` (ID 24150) |
| Anfiteatro | `Anfiteatro Juan de Garay` | `Anfiteatro Municipal Juan de Garay \| Santa Fe` | La aérea recortada (ID 33415) |
| Mercado Progreso | `Mercado Progreso` | `Mercado Progreso, espacio cultural en Candioti \| Santa Fe` | El patio central, de Federico Cairoli (ID 12320) |
| Museos y Salas | `Museos y Salas` | `Museos y Salas Municipales \| Santa Fe` | La fachada del CEC a color, del dossier 2026 (ID 33517) |

Meta descripciones:

- **Centro Cultural:** `Exposiciones, presentaciones de libros y talleres abiertos al público en el centro de Santa Fe. Reúne la Fotogalería y la Biblioteca Muttis.`
- **Biblioteca Muttis:** `Biblioteca pública municipal en Primera Junta 2451, Santa Fe. Consulta en sala, préstamo de libros a domicilio y prensa diaria. Horarios y cómo asociarse.`
- **Anfiteatro:** `Anfiteatro público en el Parque del Sur, Av. Presidente Illia 1100, Santa Fe. 1787 butacas, escenario renovado y sala bajo tablas para 70 personas.`
- **Mercado Progreso:** `El Mercado Progreso es el espacio cultural municipal de Barrio Candioti, en Balcarce 1635, Santa Fe: teatro, música, cine y títeres.`
- **Museos y Salas** (04/11/2026): `Los diez museos y salas municipales de Santa Fe, del Museo Sor Josefa al CEC, y sus publicaciones digitales: catálogos de muestras para leer en PDF.`

### Festival Diseña: cómo se carga y lo que queda a mano

- **En cada una de las cuatro páginas** de `/festivaldisena/` hay cinco widgets HTML: header, menú, tarjetas e íconos, contenido y footer. El menú se reemplaza por `festival-disena-comun.html`, el de tarjetas e íconos se borra y el de contenido se reemplaza por el archivo de la página. Header y footer no se tocan.
- **El nombre es «Festival Diseña»**, y «Festival Diseña Santa Fe» donde hace falta el lugar. La pestaña sale del *Título SEO* de Yoast, cargado el 28/10/2026 en las cuatro páginas (con la meta descripción de inicio, que decía «La Diseña»):
  - Inicio: `Festival Diseña Santa Fe`
  - `Historia y Trayectoria · Festival Diseña Santa Fe`
  - `Formaciones · Festival Diseña Santa Fe`
  - `Documentación Oficial · Festival Diseña Santa Fe`
- **El peso de la home baja de ~25 MB a menos de 1 MB.** El visor de la galería cargaba los diez originales (1,8 a 2,6 MB cada uno) aunque nadie lo abriera, y el hero usaba otro original de 2,1 MB. Ahora el hero usa los tamaños de WordPress (768 a 1536), las fotos del visor se bajan al abrirlo y YouTube carga al tocar play.
- **Lo que queda del lado de WordPress** —afecta a todo el sitio, probarlo en otras páginas antes—:
  - *Elementor → Ajustes del sitio → Tipografía*: las fuentes globales piden Alegreya Sans, Andada Pro y Rubik, que no se usan, y Encode Sans dos veces con 18 variantes. Pasarlas todas a Encode Sans.
  - *Elementor → Ajustes → Funciones*: activar *Inline Font Icons*. Hoy se cargan tres hojas de Font Awesome y dos veces eicons sin ningún ícono en la página.
  - *Elementor → Ajustes del sitio → Tipografía → Enlaces*: el color de hover es el verde `#12cb8e` (`.elementor-kit-2577 a:hover`) y pinta de verde los links de todo el sitio. Las páginas del festival lo pisan; pasarlo a Azul 01 lo arregla en todas.
  - El HTML no se guarda en caché: Cloudflare responde `DYNAMIC`. Hace falta un plugin de caché de página (se instala por ZIP) o APO de Cloudflare.
- **Geologica sigue en el header**, sólo en la palabra «CULTURA» del logo. Es el header de todo el sitio (`sitio-nuevo/header.html`); si también ahí va Encode Sans, se cambia en ese archivo.
- **Disertantes sin foto.** LinkedIn no muestra perfiles ni fotos sin iniciar sesión, así que van con iniciales sobre celeste. Lo correcto es pedirles la foto a cada uno, con permiso de uso. Para sumarla: subirla a Medios y reemplazar su `<span class="fd-avatar">` por `<img class="fd-avatar" src="…" width="192" height="192" loading="lazy" alt="">`.
- **Rosario Kerz no tiene CV publicado.** Se sacó el *Ver CV* que llevaba a un texto de relleno; cuando esté, se suma el enlace y su ventana como en los demás.
- **El menú ya no queda fijo al bajar.** No lo hacía antes, porque estaba en un widget propio y `position: sticky` no sale de su contenedor, y además quedaba debajo del header fijo.
- **Cambios de texto:** el hero ya no repite «Festival Diseña es…», porque el título de la página lo dice justo arriba. «¿Tenés una marca? Cómo participar» lleva a Documentación Oficial, donde están los lineamientos, y no a Formaciones.
- **Confirmar los hitos:** «2017, 10 años» y «2022, 15 años» no cierran con una primera edición en 2008. Cierran si se cuentan ediciones (10.ª y 15.ª), no años.

Este repositorio contiene **la documentación de diseño y el plan**, no el child theme. El tema arranca en la Fase 3 del plan.

Para ver el sistema visual con los componentes funcionando —vidrio, botones con sus estados, el remate de barras, la lámina del footer— abrí `docs/sistema-visual.html`.

El PDF se genera capturando cada página con el render real del navegador, no dejando que Chromium pagine solo. Eso conserva el `backdrop-filter`, los degradés y el grano, y permite cortar las páginas donde no parten ninguna tarjeta ni dejan un título huérfano al pie. **La contra: el texto es imagen, no es seleccionable ni buscable.** Para leer y copiar, el HTML.

Para regenerarlo, con `playwright` instalado:

```
node docs/build-pdf.mjs
```

Escribe `docs/sistema-visual.pdf` y, antes de hacerlo, **aborta si alguna tarjeta quedó partida**. La línea que imprime —`PARTIDAS 0 · HUERFANOS 0`— es la verificación, no un adorno: si alguno de los dos da distinto de cero hay que mirar el corte antes de publicar.

## El prototipo en Figma

El rediseño está dibujado en Figma, sobre contenido **real** del sitio —no
*lorem ipsum* ni secciones inventadas— tomado del relevamiento del HTML
publicado.

<https://www.figma.com/design/zpRpd7PIrH1inGQ4w6BcsT/>

| Pantalla | Qué resuelve |
|---|---|
| `01 · Sistema` | Paleta, tipografía, botones con sus estados, el remate de barras y el material de vidrio |
| `02 · Inicio` | Muestra destacada, propuestas vigentes, los 17 destinos agrupados y las publicaciones en PDF |
| `03 · Ficha de espacio cultural` | La plantilla de `/museos-y-salas/<slug>/`: datos prácticos, muestras y galería |
| `04 · Museos y Salas` | El hub con los 10 espacios municipales |
| `05 · Inicio mobile` | El mismo home a 390 px |

Dos cosas que el prototipo decide y conviene no perder de vista:

- **No hay agenda.** Ya existe en `agenda.santafeciudad.gov.ar` y se enlaza
  hacia afuera, rotulada. El sitio sigue siendo un directorio de destinos.
- **Los datos prácticos de la ficha son de relleno.** Horarios, dirección y
  accesibilidad del Museo de la Constitución Nacional están puestos para
  dimensionar la plantilla; hay que reemplazarlos por los reales antes de
  usarla como referencia de contenido.

## Cómo se entrega

Sin acceso al servidor no hay build, ni WP-CLI, ni SSH. El child theme se construye localmente y se sube como **ZIP** desde *Apariencia → Temas → Añadir nuevo*. El CSS va compilado y las tipografías autoalojadas.

Dos cosas hay que cargarlas a mano en el panel, y si no se hacen el tema pelea especificidad para siempre:

1. La **Global Color Palette de Astra** con los cuatro colores.
2. Los **Global Colors de Elementor** (*Site Settings*), más *Disable Default Colors* y *Disable Default Fonts* en **Elementor → Settings**.

## Herramientas para Claude Code

El repo trae configuradas tres herramientas para trabajar con Claude Code. Las skills de [agent-skills](https://github.com/addyosmani/agent-skills) viven en `.claude/skills/` y no requieren instalación; las otras dos se instalan una vez por máquina:

```bash
pip install graphifyy==0.9.84
claude plugin install ponytail@ponytail --scope project
```

- **graphify** arma un grafo del código en `graphify-out/` (no se versiona). Los hooks de `.claude/settings.json` lo llaman en cada lectura y búsqueda, así que tiene que estar en el `PATH`. Para generarlo o actualizarlo: `graphify update .`
- **ponytail** queda habilitado para el proyecto desde `.claude/settings.json`.
