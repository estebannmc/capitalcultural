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
