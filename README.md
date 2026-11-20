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
| `docs/inicio-preview.html` | **Vista previa de la home nueva**, antes de cargarla en Elementor: la home publicada con el header y los cuatro widgets nuevos en su lugar, y la agenda del día en que se armó; las noticias se leen en vivo. Se regenera con `python docs/build-inicio-preview.py` |
| `sitio-actual/pluginsCC.txt` | Plugins instalados en el WordPress, con versión; los marcados *¿es necesario?* están en revisión |
| `sitio-actual/paginasUtilCC.docx` | Listado de páginas del sitio |
| `sitio-actual/capitalcultural.WordPress.2026-10-22.xml` | Export de las páginas del WordPress (*Herramientas → Exportar*), con los datos de Elementor. De acá salen los textos e imágenes de las páginas nuevas |
| `sitio-nuevo/huellas-de-mi-ciudad.html` | **Huellas de mi Ciudad**: texto y los tres recorridos (Marcas, Memorias Feministas, Patrimonio). Widget HTML entero |
| `sitio-nuevo/marcas-de-la-memoria.html` | **Marcas de la Memoria** con la marca de José “Pepe” Serra adentro: texto, foto y audio, en el ancla `#jose-pepe-serra` |
| `sitio-nuevo/memorias-urbanas-feministas.html` | **Memorias Urbanas Feministas**: las 13 baldosas en orden cronológico, con tarjeta a cada ficha |
| `sitio-nuevo/centro-cultural-municipal.html` | **Centro Cultural Municipal**: texto, los dos espacios (Biblioteca Muttis y Fotogalería) y el shortcode `[ccmunicipal]` con 50 px abajo. Widget HTML entero |
| `sitio-nuevo/biblioteca-muttis.html` | **Biblioteca Profesor Enrique Muttis**: texto, botón al catálogo, datos de visita, servicios, cómo asociarse, colección e historia, con datos estructurados `Library` de schema.org. Widget HTML entero |
| `sitio-nuevo/fichas-memorias-feministas.html` | El código de las 13 fichas de baldosas, con *Copiar código* y vista previa. Abrilo en el navegador |
| `sitio-nuevo/festival-disena-comun.html` | **Festival Diseña, bloque común**: Encode Sans variable (única tipografía), estilos `.fd`, menú de la sección e íconos. Primer widget HTML de las cuatro páginas |
| `sitio-nuevo/festival-disena-inicio.html` | **Festival Diseña, inicio**: hero con la foto en tamaño 1536, cifras, ciclo en cinco pasos, galería en mosaico con visor y video que carga YouTube al tocar play |
| `sitio-nuevo/festival-disena-trayectoria.html` | **Festival Diseña, Historia y Trayectoria**: datos clave, línea de hitos, rubros, herramientas de apoyo y Expresiva |
| `sitio-nuevo/festival-disena-formaciones.html` | **Festival Diseña, Formaciones**: ciclo en curso, archivo de clínicas plegado por año y los 15 disertantes con iniciales y CV en ventana |
| `sitio-nuevo/festival-disena-docs.html` | **Festival Diseña, Documentación Oficial**: las 220 marcas seleccionadas con buscador y los siete PDF |
| `sitio-nuevo/inicio-intro.html` | **Home, intro**: el H1 con la frase de qué es Capital Cultural y los estilos de los cuatro widgets de la home. Va primero |
| `sitio-nuevo/inicio-agenda.html` | **Home, agenda**: lee el feed de la agenda, muestra los eventos elegidos en los ajustes del plugin (muestras y actividades en la misma fila), junta los eventos con varias fechas, marca «Gratis», enlaza las propuestas de Capital Cultural a su ficha y, con más de 4 tarjetas, arma un carrusel |
| `sitio-nuevo/inicio-accesos.html` | **Home, accesos**: los 16 destinos en tres grupos (Eventos · Espacios · Programas), con los botones `.cb-btn` de siempre y el nombre alineado a la izquierda; Teatro Municipal y Convocatorias vigentes llevan ↗ porque salen de Capital Cultural |
| `sitio-nuevo/inicio-noticias.html` | **Home, noticias**: las 18 últimas noticias de Cultura, leídas directo de santafenoticias.gob.ar, con el diseño del plugin de noticias. Publicada el 18/11/2026 en lugar del plugin, que se eliminó |
| `sitio-actual/capital-cultural-noticias/` | **Copia del plugin de noticias 1.3.0** que se eliminó del sitio el 18/11/2026, bajada del editor de plugins y comparada archivo por archivo. Sólo por si hay que volver atrás: subir la carpeta en un ZIP desde *Plugins → Añadir nuevo* |
| `sitio-actual/capital-cultural-agenda/` | **El plugin de agenda 1.6.0**, tal como está en el sitio desde el 18/11/2026. El commit `922f589` guarda la 1.3.1 original. Se edita en *Plugins → Editor de archivos de plugins* y se vuelve a copiar acá |
| `sitio-nuevo/inicio-newsletter.html` | **Home, newsletter**: la lámina «Suscribite a nuestro newsletter cultural» con el formulario de Contact Form 7 adentro |

### Huellas de mi Ciudad: lo que queda a mano

- **La página vieja de José “Pepe” Serra** (`/marcas-de-la-memoria/jose-pepe-serra/`, página 20751) sigue publicada, pero ya nada la enlaza. Para no dejar una URL huérfana, pasala a borrador y redirigila a `/huellas-de-mi-ciudad/marcas-de-la-memoria/#jose-pepe-serra`. Yoast SEO gratis no hace redirecciones: hace falta Yoast Premium o un plugin como *Redirection*.
- **Las 13 fichas de baldosas usan la plantilla de ancho completo de Redux**; las de Patrimonio, *Elementor ancho completo* (`elementor_header_footer`) con una sección de ancho por defecto. Con la de Redux la ficha se ensancha y cambia la proporción entre texto y foto. Antes de pegar el código, cambiarla en lote: *Páginas → tildar las 13 → Acciones en lote → Editar → Plantilla*.
- **Siete baldosas no tienen foto** en el sitio (Trata, Unidades básicas, Sindicalistas, Orígenes, Sociabilidad lésbica, Desocupadas y Guardianas). En el listado llevan el remate de barras sobre azul, chico y en la esquina inferior derecha; en la ficha van sólo con texto, a una columna.
- **Se corrigieron erratas** del texto publicado: *resstringia*, *pais*, *entre e las calles*, *serian*, *Paulo Freyre*, *éranos* y el guion de *“Basta” - La huelga*. El botón viejo decía *Pepe Sierra*: el nombre es Serra.

### Biblioteca Muttis: lo que queda a mano

- **El botón del catálogo apunta a `#`.** Cuando esté la URL del sistema de consulta, se cambia el `href` del `.cb-btn`.
- **Yoast**, en la caja de la página:
  - Frase clave: `Biblioteca Muttis`
  - Título SEO: `Biblioteca Municipal Profesor Enrique Muttis | Santa Fe`
  - Meta descripción: `Biblioteca pública municipal en Primera Junta 2451, Santa Fe. Consulta en sala, préstamo de libros a domicilio y prensa diaria. Horarios y cómo asociarse.`
  - Imagen para redes (pestaña *Social*): `2024/09/BMuttis.jpg`, la fachada.
- **La URL vieja `/capitalcultural/bibliotecamuttis/` da 404**, y con ella las páginas hijas (*Café y literatura*, *Cursos y talleres*…). Conviene redirigirla a `/centroculturalmunicipal/bibliotecamuttis/` con *Redirection* para no perder los enlaces que ya tenía.
- **Los datos de contacto vienen de la ficha de la Red de Bibliotecas** y no tienen correo. La Fotogalería publica otro horario (lunes a viernes de 8 a 20): confirmar cuál es el de la biblioteca antes de publicar. El horario también está en el JSON-LD del final del código, así que hay que cambiarlo en los dos lugares.
- **La dirección es Primera Junta 2451**, confirmada. Wikipedia y la Red de Bibliotecas todavía dicen Cortada Falucho 2450: el edificio da a las dos calles.
- **La historia del edificio** sale de la nota de UNO Santa Fe del 11/09/2020 sobre el acondicionamiento de la sala de las bóvedas, y la de Muttis, de Wikipedia. No se encontró año de construcción ni arquitecto del Banco Municipal.

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
  - *Elementor → Ajustes del sitio → Tipografía → Enlaces*: el hover era el verde `#12cb8e` (`.elementor-kit-2577 a:hover`) y pintaba de verde los links de todo el sitio. **Cambiado el 29/10/2026 a celeste**, enlazado al color global *Primario* (`#57b8e9`), así sigue a la paleta si cambia. Decisión tomada sabiendo que el celeste como texto sobre fondo claro da 2,2:1 de contraste (AA pide 4,5:1); sobre el footer azul da 6,7:1. Los botones `.cb-btn` y el menú del header no cambian: tienen su propio hover.
  - El HTML no se guarda en caché: Cloudflare responde `DYNAMIC`. Hace falta un plugin de caché de página (se instala por ZIP) o APO de Cloudflare.
- **Geologica sigue en el header**, sólo en la palabra «CULTURA» del logo. Es el header de todo el sitio (`sitio-nuevo/header.html`); si también ahí va Encode Sans, se cambia en ese archivo.
- **Disertantes sin foto.** LinkedIn no muestra perfiles ni fotos sin iniciar sesión, así que van con iniciales sobre celeste. Lo correcto es pedirles la foto a cada uno, con permiso de uso. Para sumarla: subirla a Medios y reemplazar su `<span class="fd-avatar">` por `<img class="fd-avatar" src="…" width="192" height="192" loading="lazy" alt="">`.
- **Rosario Kerz no tiene CV publicado.** Se sacó el *Ver CV* que llevaba a un texto de relleno; cuando esté, se suma el enlace y su ventana como en los demás.
- **El menú ya no queda fijo al bajar.** No lo hacía antes, porque estaba en un widget propio y `position: sticky` no sale de su contenedor, y además quedaba debajo del header fijo.
- **Cambios de texto:** el hero ya no repite «Festival Diseña es…», porque el título de la página lo dice justo arriba. «¿Tenés una marca? Cómo participar» lleva a Documentación Oficial, donde están los lineamientos, y no a Formaciones.
- **Confirmar los hitos:** «2017, 10 años» y «2022, 15 años» no cierran con una primera edición en 2008. Cierran si se cuentan ediciones (10.ª y 15.ª), no años.

### Home: cómo se carga y lo que queda a mano

**Publicada el 29/10/2026**, en este orden y con el header nuevo. Las secciones del slider, de los botones y de la agenda traían márgenes de Elementor (40, 50 y 50/25 px) que se pusieron en 0, y la de Noticias va con *Separación de columnas: sin separación* y 50 px de margen abajo hasta el footer. La sección de la intro se creó desde la API de Elementor: si se rehace a mano, verificar que la columna y el widget tengan id.

- **El orden en Elementor**, de arriba abajo. Cada widget nuevo es un widget HTML con el archivo entero adentro:
  1. Header, sin tocar la sección. Su HTML es `sitio-nuevo/header.html`, que ahora trae el menú visible en escritorio (Agenda · Espacios · Eventos · Convocatorias · Noticias) y el buscador. Es el de todo el sitio.
  2. `inicio-intro.html`, en una sección nueva. Tiene el H1 y los estilos de los otros tres widgets, así que va primero.
  3. La sección del slider `[capital_slider]`, sin tocar.
  4. `inicio-accesos.html`, en la sección de los doce botones de espacios. Se borra la sección de los cuatro eventos (+ Feria, Festival Diseña, Noche de los Museos y Feria del Libro): ahora están en el grupo Eventos. Se dice así y no «Festivales» porque el único festival es el Diseña; los otros son eventos que la Secretaría produce o coproduce.
  5. `inicio-agenda.html`, en la sección de la agenda: se borra el shortcode de la agenda y se pone este widget en su lugar. Trae primero *En exhibición* y después la *Agenda*.
  6. La sección de Noticias, con *Avanzado → ID CSS* `noticias` para que funcione el enlace del menú. Adentro, `inicio-noticias.html` en un widget HTML (desde el 18/11/2026; antes era el shortcode `[capital_noticias titulo="Noticias"]`).
  7. `inicio-newsletter.html`, en una sección nueva. **Queda para después**: la home se publica sin el newsletter hasta elegir el servicio de envío (ver abajo).
  8. Se borra la sección **Propuestas activas y próximas**: repetía tres muestras de la agenda.
- **El newsletter no se publica todavía.** Contact Form 7 sólo junta correos: no guarda la lista ni envía. Lo recomendado es un servicio externo (Brevo o MailerLite, o el que ya use la Municipalidad) con la cuenta a nombre de `capitalcultural@`, el dominio autenticado por sistemas (SPF/DKIM) y el texto de consentimiento revisado por legales (Ley 25.326; la base puede tener que registrarse ante la AAIP). Cuando esté, se cambia el shortcode de `inicio-newsletter.html` por el formulario de ese servicio. Lo que sigue es la variante con Contact Form 7, por si se usa mientras tanto.
- **Variante con Contact Form 7**, que ya está instalado. Hay que crear el formulario y cambiar `NEWSLETTER` en el shortcode por su id. Plantilla del formulario:
  ```
  <label>Tu correo electrónico [email* correo autocomplete:email placeholder "nombre@correo.com"]</label>
  [acceptance acepto] Acepto recibir la agenda cultural por correo. Puedo darme de baja cuando quiera. [/acceptance]
  [submit "Suscribirme"]
  ```
  En *Correo*: para `capitalcultural@santafeciudad.gov.ar`, asunto `Alta en el newsletter cultural: [correo]`. CF7 sólo manda un mail por alta, no guarda la lista ni envía el newsletter: para tener la lista, instalar *Flamingo* (es del mismo autor), y para enviarlo hace falta un servicio de envío (Brevo, Mailchimp…). Si se elige uno, se reemplaza el shortcode por su formulario y la lámina queda igual.
- **«Gratis» sale de la agenda.** El feed no trae el precio, así que la marca aparece cuando el evento tiene la categoría «Gratis» o «Entrada libre» en `agenda.santafeciudad.gov.ar`, o lo dice el título. Hoy ningún evento la tiene. No está comprobado que la categoría nueva llegue al feed: cargarla en un evento y mirar la home.
- **«Más fechas»**: los eventos con el mismo título y el mismo lugar se juntan en una tarjeta, con la primera fecha y «Más fechas hasta el…». La agenda nueva no tiene los filtros Hoy · Este finde · Esta semana: con dos o tres eventos de Cultura por semana casi siempre quedaban vacíos.
- **Las muestras** (las que en la agenda llevan «Muestra») van sólo en *En exhibición*, ordenadas por la que cierra antes.
- **Noticias sin plugin.** `inicio-noticias.html` hace lo mismo que el plugin *capital-cultural-noticias* 1.3.0, sin pasar por el servidor: pide a la API pública de santafenoticias.gob.ar las 18 últimas noticias de la categoría Cultura (id 997) y sus fotos, y las muestra en tres páginas de seis con el mismo HTML y CSS del plugin. Comprobado el 18/11/2026 en la vista previa: las mismas 18 noticias y fechas que el plugin, flechas andando y las 17 fotos con alt (una noticia no tiene foto).
  - **Publicado el 18/11/2026.** El widget reemplazó al shortcode en la sección Noticias de la home (widget `889fbcf`, cargado desde la API de Elementor y comprobado idéntico al archivo). Después se desactivó y se eliminó el plugin, y se purgó LiteSpeed. Comprobado sin sesión: la home trae el widget y ningún archivo del plugin, y en el navegador carga las 18 noticias en tres páginas, con las flechas andando. La home era la única página del sitemap que usaba el plugin. Quedaron en la base sus opciones (`ccn_settings`, `ccn_status`, `ccn_last_good`, `ccn_category`), que no molestan: el plugin no traía desinstalador.
  - **Lo que cambia:** cada visitante le pide las noticias a santafenoticias.gob.ar, sin la copia que el plugin guardaba en el servidor. Si ese sitio se cae, o deja de aceptar pedidos desde otros sitios, la sección dice «No pudimos cargar las noticias en este momento». Las fotos se piden en tamaño mediano (300 px) en vez de 768: alcanzan para el cuadrado de 112 px y pesan menos.
  - **Alt de las fotos.** El plugin las dejaba con `alt=""`; el widget les pone el titular. El arreglo provisorio de `inicio-agenda.html` (el que completaba el alt de `.ccn-card`) queda sin efecto y se puede borrar la próxima vez que se pegue ese widget.
- **La agenda sigue con plugin.** La API de eventos de `agenda.santafeciudad.gov.ar` (Events Manager) pide usuario, y su calendario iCal no se puede leer desde una página de otro sitio. Sólo el servidor puede pedirlo, que es lo que hace el plugin. Para sacarlo, quien administra la agenda tendría que abrir la API de eventos al público. Aun así no conviene: el iCal tarda en generarse y cada visitante lo esperaría, mientras que el plugin lo lee en segundo plano y responde con su copia.
- **El slider tiene botón de pausa**, que pide la pauta 2.2.2 de WCAG porque pasa solo cada 20 segundos. `sitio-actual/capital-cultural-slider/` es **la versión 2.3.2 publicada**, copiada del sitio el 29/10/2026 y comparada archivo por archivo: el sitio iba por delante del repo (srcset de las imágenes, botón con texto, etiquetas sugeridas). Si se vuelve a tocar el plugin en el panel, traerlo al repo antes de seguir.
- **Enlaces del menú a la home** (`#agenda`, `#espacios`, `#eventos`, `#noticias`): el tema pone `scroll-behavior: smooth` y el salto animado de la carga no siempre llegaba. `inicio-agenda.html` salta sin animación al cargar y otra vez cuando terminan de cargar las muestras, salvo que la persona ya haya movido la página.
- **La agenda se renueva sola** (plugin 1.4.0, 18/11/2026). WP-Cron vuelve a leer el iCal cada «Actualizar cada» minutos (60) y la caché dura el doble, así que no vence antes de la lectura siguiente. Si igual vence, por ejemplo porque nadie visitó el sitio y el cron no corrió, `admin-ajax.php?action=cca_eventos` responde enseguida con la última lectura correcta y la agenda se relee en segundo plano. Antes la persona que caía con la caché vencida esperaba de 22 a 40 segundos. Sólo espera quien pida la agenda por primera vez, cuando nunca se leyó. Comprobado: al borrar la caché el cron releyó la agenda solo y la respuesta tardó menos de 2 segundos. *Ajustes → Agenda cultural* muestra la próxima lectura automática.
- **Eventos en el inicio** (plugin 1.5.0, 18/11/2026). En *Ajustes → Agenda cultural → Conexión con la agenda* están todos los eventos próximos que trae la agenda, de cualquier categoría, con una casilla cada uno: se marca o desmarca y se guarda. Los eventos nuevos de Cultura aparecen marcados y los de otras categorías sin marcar, como funcionaba antes. Se guarda sólo lo que se aparta de esa regla: `ocultos` (de Cultura, desmarcados) y `mostrar` (de otras categorías, marcados), por la dirección del evento. Lo que se guardó con la 1.4.0 (títulos, uno por línea) se sigue leyendo. El plugin manda cada evento con `inicio: true/false` y ya no manda los de Cultura desmarcados. Siguen desmarcados *Tramas colectivas para una vida sin violencias*, *Redes que acompañan, por una vida libre de violencias* y *Punto Violeta en Centro de Salud Cristo Obrero*.
  - **`inicio-agenda.html` filtra por `inicio`** en lugar de por la categoría Cultura. Publicado el 18/11/2026 a las 11:32, comprobado idéntico al repo y mirado en la home: muestra sólo los eventos marcados. Antes de eso, cada publicación de la home desde la API de Elementor devolvió 503 después de unos 10 segundos, también con el widget sin cambios. Desde el botón *Actualizar* del editor anduvo. Si vuelve a pasar, revisar el registro de errores del servidor.
- **Carrusel en la home** (publicado el 18/11/2026). Con más de 4 tarjetas la fila de la Agenda pasa a carrusel, como el bloque del shortcode del plugin: 4 tarjetas a la vista en escritorio, 3 en tablet y una y algo en el celular. Tiene flechas a media altura de la foto (no en el celular, donde se desliza con el dedo), puntos y botón de pausa, y avanza sola cada 5 segundos. Se frena con el mouse encima, con el foco de teclado adentro, con el dedo en la fila o con la pestaña oculta, y arranca en pausa si el sistema pide menos movimiento. Al llegar al final vuelve al principio. No usa copias de las tarjetas como el plugin: es la fila con desplazamiento del navegador, así que también se mueve con el trackpad y con Tab. La fila no tiene relleno a los costados, así las tarjetas llenan justo el ancho y no asoma la de al lado; en el celular la separación (16 px) es igual al margen, por la misma razón. Comprobado en cada posición del carrusel a 375, 762 y 1280 px: sólo tarjetas enteras a la vista.
- **Muestras y agenda en la misma fila** (desde el 18/11/2026). Ya no hay sección «En exhibición» (`#en-exhibicion`): las muestras van en el carrusel de la Agenda, con su etiqueta «Muestra» y «Hasta el…», ordenadas con el resto por fecha de inicio, así que las abiertas quedan primero. Hasta 12 tarjetas.
- **Las propuestas de Capital Cultural llevan a su ficha** (plugin de agenda 1.6.0). Si un evento de la agenda tiene una propuesta del plugin de Programación con el mismo título (sin importar mayúsculas, tildes ni signos), la tarjeta lleva a la página del espacio con `#ccp-propuesta-ID`, que abre la ficha de la propuesta, en la misma pestaña. El resto sigue yendo a la agenda municipal, en otra pestaña. La página de cada espacio se busca por el shortcode del espacio (`[sorjosefa]`, `[cec]`…) en las páginas publicadas; si dos páginas lo tienen, gana la más vieja. La lista se arma una vez por hora y se borra al guardar una propuesta; armarla tardó 5 segundos y después la agenda responde en menos de 1. Al 18/11/2026: *Manto de pampa y cielo II* e *Instantes del Patrimonio* (`/museos-y-salas/mmav/`), *Andar la calle* (`/museos-y-salas/cec/`), *Estanislao López* (`/centroculturalmunicipal/`) y *Acerca de actores y vestimentas* (`/museos-y-salas/manzana-jesuitica/`), los mismos que da recorrer el sitio. Comprobado con un clic: abre la ficha. Si un evento no lleva a su ficha, revisar que la propuesta tenga el mismo título y su espacio cargado. Las propuestas no tienen dirección propia: `?cc_propuesta=…` muestra la home.
- **Pista para deslizar** (`pistaDeslizar`, el mismo bloque en `inicio-accesos.html` y en `festival-disena-comun.html`). En las filas que se deslizan de costado en el celular (los tres grupos de Explorá hasta 860 px y el menú de Festival Diseña hasta 700 px), el borde que tiene más botones se desvanece con un degradé, y del otro lado también cuando ya se deslizó. La primera vez que la fila entra entera en pantalla aparece una mano dibujada en SVG que toca y desliza hacia la izquierda, con una estela celeste, mientras los botones se corren 40 px y vuelven: dos veces, 3,4 segundos en total, y se va. En la home sólo en la primera fila (Eventos). Deja de aparecer en ese navegador cuando la persona toca o desliza la fila (`localStorage`: `pista-inicio-accesos`, `pista-disena-menu`) y no aparece si el sistema pide menos movimiento. Reemplaza a la animación de Lottie que se había propuesto: sin librerías ni archivos de otro sitio. Probado en la vista previa a 375 px.
- **Teatro Municipal** tiene sitio propio y lleva ↗, igual que Convocatorias vigentes, que está en otra parte del sitio de la Municipalidad. Red de bibliotecas santafesinas es de Capital Cultural: el botón viejo apuntaba a `/bibliotecaspopularessantafesinas/`, que no carga, y ahora va a `/capitalcultural/bibliotecaspopularessantafesinas/`. El botón *Centro Cultural Muttis* pasa a llamarse **Centro Cultural Municipal**, como la página; en el header apuntaba a `/bibliotecamuttis/`, que da 404.
- **Auditoría de la home** (29/10/2026, en `docs/inicio-preview.html`, a 375, 768, 1024, 1280 y 1440 px). Los bloques nuevos no tienen scroll lateral, texto de menos de 14 px, texto cortado ni fallas de contraste; hay un solo H1 y ninguna imagen sin alt. Lo que se corrigió en la auditoría:
  - Astra achica la fuente raíz en el celular (~11,9 px) y los bloques medían en `rem`: el texto bajaba a 10–12 px. Ahora van en px.
  - Los bloques arrancaban 32 px más adentro que el slider y las noticias. Ahora todo usa la misma caja de 1120 px.
  - Los enlaces del menú pasaron de 35 a 44 px de alto, y la casilla del newsletter, de 13 a 24 px.
  - Explorá es una sola región de navegación y no tres.
- **Lo que la auditoría encontró en los plugins**, que no están en este repo:
  - **Slider 2.3.2**: mide `width: 100vw` y con la barra de scroll de escritorio se pasa del ancho y aparece scroll lateral, también en el sitio publicado. La home lo recorta con `overflow-x: clip`; lo correcto es `width: 100%` en el plugin. Los puntos de navegación miden 30 × 4 px (el mínimo es 24 × 24) y los textos de la placa, 10 y 13 px.
  - **Noticias**: la fecha y el resumen van en 12 y 13 px.
  - **Agenda**: el feed da la imagen original (1200 px) para tarjetas de 254 px. Si el plugin pudiera pedir el tamaño `medium_large` de WordPress, la home bajaría bastante de peso.
- **Yoast**, cargado el 29/10/2026 en la página de inicio (frase clave: `Capital Cultural`):
  - Título SEO: `%%sitename%% %%sep%% Agenda, espacios y eventos de Santa Fe` → «Capital Cultural - Agenda, espacios y eventos de Santa Fe» (57 caracteres). Antes: «Inicio - Capital Cultural».
  - Meta descripción (145 caracteres, empieza con la frase clave): `Capital Cultural reúne la agenda cultural de Santa Fe: muestras, conciertos, eventos y convocatorias, y los museos, teatros y salas de la ciudad.` La de 2021 hablaba de «vidrieras virtuales».
  - Imagen para redes: `2026/10/capitalcultural-redes-1200x630-1.jpg` (id 33364), 1200×630 y 68 KB, recortada de la foto de Marce Pucci del Salón Anual Nacional en el MMAV. Se hizo aparte porque Yoast sirve el original de esa foto (1920 px, 1,5 MB) aunque se elija un tamaño menor, y con ese peso WhatsApp no suele mostrar la vista previa. La foto original también quedó con texto alternativo.
  - Puntajes de Yoast: SEO de −9 a 83, título de 17 a 100, meta descripción de 22 a 83. Lo que sigue marcando es de contenido: la frase clave aparece una sola vez en el texto, ningún H2 la lleva y la página tiene unas 200 palabras fijas (la agenda y las noticias se cargan después y Yoast no las cuenta).
  - Facebook y WhatsApp guardan la vista previa vieja un tiempo. Para renovarla: pegar la URL en el *Depurador de contenido compartido* de Meta y tocar *Volver a extraer*.
  - **Ojo al editar la home en Elementor**: Yoast guarda su formulario junto con la página. Si al abrir el editor la pestaña de Yoast muestra el título o la descripción vacíos, no guardar hasta que aparezcan.

### Resultados de búsqueda

Los renderiza Astra, no Elementor. Cambiado el 30/10/2026:

- **Sin autor.** *Personalizar → Blog* (`blog-meta`) pasó de autor y fecha a sólo fecha, así que el nombre sale del HTML en la búsqueda y en los archivos de entradas. Antes era un enlace a `/author/ecordoba/`, que mostraba el usuario de WordPress. **La fecha queda**, a pedido, debajo del título.
- **Diseño:** sección 18 de `sitio-actual/cssAdicionalCCAstra.css`, cargada en *CSS adicional*. Tarjetas como las de la home, toda la tarjeta es clicable, misma caja de 1120 px, medidas en px. Sólo afecta a `.search`. Es lo único que se sumó al CSS publicado: el resto del archivo del repo no coincide con el del sitio (16.119 y 15.366 caracteres antes del cambio) y conviene compararlos.
- **Textos en inglés:** fragmento de WPCode *Traducciones de Astra (paginación y buscador)*, id 33388, PHP sólo en el frontend. Traduce Next, Previous, Search... y Post pagination, y vale para todo el sitio:
  ```php
  add_filter( 'gettext_astra', function ( $traduccion, $texto ) {
  	$textos = array(
  		'Next'            => 'Siguiente',
  		'Previous'        => 'Anterior',
  		'Search...'       => 'Buscar…',
  		'Post pagination' => 'Paginación de entradas',
  	);
  	return isset( $textos[ $texto ] ) ? $textos[ $texto ] : $traduccion;
  }, 10, 2 );
  ```
- **Lo que queda:**
  - **Archivo de autor apagado** el 30/10/2026 en *Yoast SEO → Ajustes → Avanzado → Archivos de autor*: `/author/ecordoba/` redirige a la home, `?author=N` da 404 y el sitemap ya no lo lista.
  - El nombre sigue en los metadatos de Yoast de las 85 entradas, sin verse en la página: `meta author`, «Escrito por» en las vistas previas de enlaces y el JSON-LD. Sale de *Usuarios → Perfil → Mostrar este nombre públicamente*, o con filtros de Yoast.
  - Algunos resúmenes salen del texto de la página y quedan rotos: «NCHE DE LS MUSES» en Noche de los Museos, donde las O del título son gráficos.
  - La búsqueda no encuentra lo que está sólo en `agenda.santafeciudad.gov.ar`: «Hábitat» da cero resultados aunque la muestra está en la home.

### SEO de las páginas

Auditoría del 12/11/2026 sobre las 267 URL del sitemap (180 páginas, 85 entradas y 2 plantillas), como visitante. Todas responden 200, con `lang="es-AR"`, canonical propio, al menos un H1 y ninguna en `noindex`. Lo que se cambió:

- **Plantillas de cabecera y pie fuera del índice.** `/elementor-hf/header/` y `/elementor-hf/capital-cultural/` estaban en el sitemap con el título y la descripción de la home. Se apagó *Yoast → Ajustes → Tipos de contenidos → Elementor Header & Footer Builder → Mostrar en los resultados de búsqueda*, y el sitemap ya no las lista. Las dos URL siguen mostrando la home con `index` en el robots de WordPress, pero con canonical a la home, así que Google las toma como la misma página.
- **Imagen para redes por defecto**: la de la home (id 33364), en *Yoast → Ajustes → Aspectos básicos del sitio → Imagen del sitio*. La usan las 41 páginas que no tenían ninguna.
- **Meta descripciones en 155 páginas**: 237 de las 267 URL no tenían ninguna, y 6 fichas de Memorias Urbanas Feministas compartían la de «Cautivas del siglo XIX». Verificado como visitante después de purgar LiteSpeed: todas las páginas del sitemap tienen descripción, menos `/masferia/visitanos/` (vacía) y la vieja de José “Pepe” Serra (ver más arriba).
  - 65 páginas principales (hubs, espacios, ciclos, eventos, elencos, Pantalla Pública, Archivo de la Imagen) con un texto escrito a partir de lo que dice cada página.
  - 90 fichas de Huellas de mi Ciudad (la de Mujeres afrodescendientes ya tenía una propia y quedó) con `Título, dirección, Santa Fe.` y las primeras oraciones de la ficha (las de Memorias llevan el período en lugar de la dirección), hasta 158 caracteres. Los errores de tipeo del texto de origen pasan a la descripción: «entre 1905 y 1905» en el Colegio de la Inmaculada, «construído».
  - Títulos SEO distintos donde se repetían o eran genéricos: Teatro en el Mercado 2025 y 2026, Prensa de +Feria, Prensa de la Noche de los Museos, y las subpáginas de +Feria, la Feria del Libro y el Mercado Progreso que se llamaban sólo «Aliados», «Programación», etc.
  - Se guardaron sin abrir Elementor: el editor trae el formulario de Yoast (`#yoast-form`, acción `wpseo_elementor_save`) con todos los campos de la página; se cambian sólo la descripción y el título y se envía entero, así no se pisa nada más.
- **Lo que queda:**
  - **Entradas del blog (85) sin descripción.** Google arma el fragmento con el texto de cada una. Una plantilla `%%excerpt%%` en *Tipos de contenidos → Entradas* daría lo mismo.
  - **624 de 964 imágenes sin texto alternativo**, en 119 páginas. Se cargan en la biblioteca de medios y piden alguien que vea cada foto.
  - **94 páginas con menos de 150 palabras**, la mayoría de ciclos viejos. Conviene decidir cuáles se actualizan y cuáles pasan a borrador con redirección.
  - **15 títulos de más de 65 caracteres**, sobre todo fichas de patrimonio con nombres largos. Google los corta, pero la página se encuentra igual.
  - El sitio responde también sin `www`: los canonical apuntan a `www`, pero una redirección 301 en el servidor o en Cloudflare evitaría tener las dos versiones.
  - `/nochedelosmuseos/prensa/` duplica la home de la Noche de los Museos; tiene canonical a ella y conviene pasarla a borrador.
  - La home lleva como H1 «Capital Cultural» desde la edición del 5/11/2026; el H1 publicado el 29/10 decía «Agenda, espacios y eventos de la cultura de Santa Fe». No se tocó.

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
