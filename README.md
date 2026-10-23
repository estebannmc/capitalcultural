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
| `sitio-nuevo/fichas-memorias-feministas.html` | El código de las 13 fichas de baldosas, con *Copiar código* y vista previa. Abrilo en el navegador |

### Huellas de mi Ciudad: lo que queda a mano

- **La página vieja de José “Pepe” Serra** (`/marcas-de-la-memoria/jose-pepe-serra/`, página 20751) sigue publicada, pero ya nada la enlaza. Para no dejar una URL huérfana, pasala a borrador y redirigila a `/huellas-de-mi-ciudad/marcas-de-la-memoria/#jose-pepe-serra`. Yoast SEO gratis no hace redirecciones: hace falta Yoast Premium o un plugin como *Redirection*.
- **Las 13 fichas de baldosas usan la plantilla de ancho completo de Redux**; las de Patrimonio, *Elementor ancho completo* (`elementor_header_footer`) con una sección de ancho por defecto. Con la de Redux la ficha se ensancha y cambia la proporción entre texto y foto. Antes de pegar el código, cambiarla en lote: *Páginas → tildar las 13 → Acciones en lote → Editar → Plantilla*.
- **Siete baldosas no tienen foto** en el sitio (Trata, Unidades básicas, Sindicalistas, Orígenes, Sociabilidad lésbica, Desocupadas y Guardianas). En el listado llevan el remate de barras sobre azul, chico y en la esquina inferior derecha; en la ficha van sólo con texto, a una columna.
- **Se corrigieron erratas** del texto publicado: *resstringia*, *pais*, *entre e las calles*, *serian*, *Paulo Freyre*, *éranos* y el guion de *“Basta” - La huelga*. El botón viejo decía *Pepe Sierra*: el nombre es Serra.

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
