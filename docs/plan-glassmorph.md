# Capital Cultural · Plan de actualización visual (GlassMorph + marca SF 2026)

**Sitio:** https://www.santafeciudad.gov.ar/capitalcultural/
**Fuentes normativas:** `SF_Brandbook_V2_light_compressed-1_compressed-1.pdf` (V1.0 · Febrero 2026, 67 pág.) y `SF_paleta_cromática.pdf` (ficha 02.4)
**Stack confirmado:** WordPress + **Astra 4.13.1** + **Elementor Free**
**Enfoque acordado:** retematizado por child theme, sin migrar plataforma
**Sistema cromático:** cerrado en cuatro — Azul 01, Verde 01, rojo de submarca y neutros
**Restricción de entrega:** hay acceso al admin de WP pero **no al servidor** → todo se instala por ZIP desde el panel, sin build, sin WP-CLI, sin SSH

---

## 0. Punto de partida y supuestos

| Ítem | Estado |
|---|---|
| Plataforma | **WordPress con Astra 4.13.1** (Brainstorm Force), en subcarpeta `/capitalcultural/`. Confirmado desde el admin. Astra es un tema, no un page builder: el mejor escenario posible |
| Composer | **Elementor Free.** Sin Theme Builder, que es de Pro: header, footer, archivos y entradas los sigue renderizando Astra. Elementor sólo manda dentro de las páginas armadas con él |
| Escala | **510 páginas**, no todas publicadas ni visibles. Es la variable que domina el presupuesto |
| Marca aplicable | Capital Cultural es un **tópico de nivel 01/A** del sistema SF: logo síntesis `SF` + palabra clave y color propio (Brandbook 04.1, pág. 48–51) |
| Color del tópico | **Rojo `#e63312`** — verificado por muestreo de píxeles sobre el lockup `SF / CULTURA` de la pág. 51 |

El sitio en sí no pudo auditarse: el dominio está bloqueado desde este entorno. Todo lo que dependa del relevamiento está marcado como **[R]** y se confirma en la Fase 1.

---

## 1. Sistema de diseño

### 1.1 Paleta — subconjunto cerrado de la ficha 02.4

| Token | Hex | RGB | Pantone | Rol |
|---|---|---|---|---|
| Santa Fe Azul 01 | `#002751` | 0 39 81 | 540 | Dominante, y todo el texto |
| Santa Fe Verde 01 | `#00c08b` | 0 192 139 | 3405 | Superficie |
| Rojo Cultura | `#e63312` | 230 51 18 | — | Color del tópico |

**Neutros:** `#ffffff` · `#dadad9` · `#606060` · `#1d1d1b`

**Degradé de transición:** `linear-gradient(105deg, #00c08b → #002751)`. Corre por la misma diagonal que la trama, así que degradé y trama leen como un solo gesto. Es fondo y grafismo; si lleva texto, sólo blanco y sólo sobre el tramo azul, porque el extremo verde no da contraste.

**Fuera de sistema para este sitio:** Azul 02 `#1639f1`, Verde 02 `#00fe9b`, celeste `#57b8e9`, naranja `#fca100` y amarillo `#f2e61a`. Existen en el Brandbook pero no entran acá, y `docs/design-tokens.css` directamente no los declara: lo que no existe como token no se usa por accidente.

### 1.2 Regla de contraste (medida, WCAG 2.1)

```
azul-01  / blanco    14.90  AAA       texto libre
negro    / blanco    16.88  AAA       texto libre
azul-01  / gris-20   10.65  AAA
azul-01  / verde-01   6.33  AA        texto sobre verde: SIEMPRE azul-01
gris-75  / blanco     6.29  AA        texto secundario
blanco   / rojo       4.33  AA-large  sólo ≥24px (o ≥19px bold)
azul-01  / rojo       3.44  AA-large
blanco   / verde-01   2.36  FALLA     nunca blanco sobre verde
verde-01 / blanco     2.36  FALLA     el verde nunca es texto
```

**Tres reglas salen de acá.** El verde es color de **fondo**, y el texto encima siempre es Azul 01, nunca blanco. El rojo **no alcanza AA en ningún combo**: titulares grandes, filetes y barras, nunca texto corrido. Y como Azul 02 quedó fuera, **los links se distinguen por subrayado**, no por tono — el subrayado va en rojo, que ahí funciona porque es grafismo y no texto.

Al reducir la paleta el rojo queda más expuesto, porque ya no hay Azul 02 para descargarle trabajo. Conviene tenerlo presente al redactar piezas.

Esto no es preferencia estética: es un sitio de gobierno, le aplica la Ley 26.653 y la ODA 1.0 de ONTI, que toman WCAG AA como piso obligatorio.

### 1.3 Tipografía (02.1 / 02.2)

- **Geologica** — tipografía de marca, **exclusiva de signos de identidad** (el lockup `SF / CULTURA` y poco más). El Brandbook es explícito en esto.
- **Encode Sans** — tipografía de comunicación: todos los titulares, bajadas y texto corrido.

Ambas están en Google Fonts. Por el bloqueo de servidor se autoalojan como archivos del child theme (`/fonts/*.woff2` + `@font-face` con `font-display: swap`), lo que además evita la dependencia externa que suele objetar el área de sistemas del municipio.

### 1.4 Trama de marca (02.6)

La identidad se construye sobre diagonales a **15°**, derivadas del paralelogramo del logo. El Brandbook dice que la trama propone «acción, dinamismo, transformación y movimiento permanente».

**El signo del ángulo importa.** La barra del logo se inclina hacia la **derecha**: subiendo, la línea se corre a la derecha (`/`). Para `repeating-linear-gradient` eso es **105deg**, la perpendicular. `75deg` produce la trama **espejada** (`\`), que invierte esa lectura de movimiento hacia adelante. Es un carácter de diferencia en el CSS y cambia el sentido de toda la página.

Traducción a web:

- dos `repeating-linear-gradient(105deg, …)` superpuestos como capa de fondo al 14% — el paso fino marca el ritmo, el ancho respira;
- la barra del logo como marcador, **siempre pegada al título y del tamaño del título**, nunca como filete del bloque entero. Las medidas van en `em`, así toma el cuerpo del texto que encabeza sin ajustes: `<h2><i class="sf-barra"></i>Título</h2>`. Reemplaza a los puntos redondos genéricos como indicador de estado y severidad;
- el barrido de luz del hero (`transform: skewX(-15deg)`), el mismo que ya usa Freedom y que venía con el ángulo correcto.

### 1.5 Botones

Radio **10px** — más cerrado que los 24px de los paneles, porque un botón es un objeto y un panel es una superficie. Alto mínimo **44px** por objetivo táctil. La barra del logo entra como marcador y el barrido de hover corre a 15°.

| Variante | Combinación | Contraste | Veredicto |
|---|---|---|---|
| Primario | blanco / Azul 01 | 14.90 | AAA |
| Línea | Azul 01 / fondo | 13.54 | AAA |
| Verde | Azul 01 / Verde 01 | 6.33 | AA |
| Desactivado | Gris 75 / Gris 20 | 4.50 | AA |
| Tópico | blanco / rojo | 4.33 | **sólo texto grande** |
| — | Azul 01 / rojo | 3.44 | **sólo texto grande** |
| — | blanco / Verde 01 | 2.36 | **nunca** |

**El rojo es la restricción que manda en el sistema.** No existe botón rojo en tamaño normal que sea accesible sin salirse del color de marca. La salida no es achicar el problema sino ubicarlo: el rojo es el **CTA grande de un hero**, uno por pantalla, y en todo el resto vive como la barra dentro de un botón azul. Por eso `.sf-btn--topico` viene forzado a 19px y no existe en tamaño normal.

En el primario la barra va en rojo sobre el relleno Azul 01: replica literalmente el lockup SF/CULTURA.

**Foco:** un anillo, no un cambio de relleno — `box-shadow: 0 0 0 2px halo, 0 0 0 5px rojo`. El anillo rojo contra el fondo claro da 3.94, por encima del 3:1 de WCAG 1.4.11. En el botón verde el halo se invierte a Azul 01, porque el halo blanco sobre verde da 2.36 y no separa. **Desactivado** no usa `opacity`, que hunde el contraste justo cuando menos conviene.

**Que Elementor los respete:** el sistema se entrega dos veces — como clases del child theme para lo que renderiza Astra, y cargado en *Site Settings → Theme Style → Buttons* para que los botones de Elementor hereden. Encima va un override de `.elementor-button` como red de seguridad.

### 1.6 La lámina de luz (footer)

El footer recrea la lámina de la página 90 del Brandbook — el haz verde entrando por listones sobre Azul 01 — **en CSS puro, sin imagen**: pesa cero, escala a cualquier ancho y se re-tiñe cambiando dos valores. Son cinco capas de `background` sobre un solo elemento (`.sf-luz`), de arriba hacia abajo:

1. un velo oscuro en degradé hacia la derecha;
2. el filo brillante de cada listón;
3. los listones a 105°, con borde suave;
4. el foco de luz: núcleo blanco sobre Verde 01;
5. la base Azul 01.

**El velo no es decorativo, es el que hace legible el footer.** Toda la tipografía vive en el tercio oscuro y la luz ocupa la derecha. Sin él queda texto blanco sobre verde, que es 2.36 y lo prohíbe la regla de §1.2 — me pasó en el primer render y se ve feo además de ilegible.

### 1.5 Material GlassMorph

Se porta el sistema ya probado en `tiendaFreedom` y `rifa-silvana` **con los mismos nombres de token** — `--glass-fill`, `--glass-blur`, `--glass-shadow`, `--noise`, `--ease-out` — y las mismas clases: `.glass`, `.glass--thin`, `.glass--thick`, `.glass--dark`, `.glass-soft`, `.glass-bar`, más el fondo vivo `.ambient` con blobs a la deriva.

Cambios respecto de Freedom/Rifa:

1. Las sombras se tiñen con Azul 01 en vez de gris frío.
2. Los blobs del fondo quedan en verde-01 y rojo sobre el gris del sistema, sin azules vibrantes.
3. Los tintes se reducen a tres: `.glass--dark` (Azul 01), `.glass--verde` y `.glass--topico`.
4. Se mantienen **intactos** los tres bloques de accesibilidad: `@supports not (backdrop-filter)`, `prefers-reduced-transparency` y `prefers-reduced-motion`.

El archivo está listo en **`docs/design-tokens.css`** — es el primer stylesheet que encola el child theme.

**Presupuesto de rendimiento:** `backdrop-filter` es caro. Regla: como máximo ~8 elementos con blur real visibles a la vez. La grilla de agenda usa `glass-soft` (translúcido sin blur); el blur real queda para header, hero, modales y tarjetas destacadas. Es exactamente el criterio que ya está comentado en `globals.css` de Freedom.

---

## 2. Arquitectura de marca aplicada

Capital Cultural es **nivel 01/A** del sistema: logo síntesis + tópico + color.

- **Header:** lockup `SF / CULTURA` con el filete rojo, enlazado a `santafeciudad.gov.ar`.
- **Nombre del sitio:** "Capital Cultural" en Encode Sans, no en Geologica (no es un signo de identidad).
- **Footer:** marca completa con Escudo Municipal + bajada "Municipalidad de Santa Fe".
- **Redes:** el Brandbook ya fija el handle `cultura.santafecapital` (pág. 51); conviene que el footer lo respete.

---

## 2.bis Trabajar sobre Astra + Elementor Free

Son **tres capas de estilo peleando por el mismo píxel**: Astra, Elementor y el child theme.

**Lo que Elementor Free no toca.** Theme Builder es de Pro. Sin él, header, footer, archivos de agenda, entradas y búsqueda los sigue renderizando **Astra**. La mayor parte del sitio se rediseña por child theme, como estaba planeado; Elementor sólo manda dentro de las páginas que alguien armó con él.

**Los Global Colors van dos veces.** Los cuatro colores se cargan en la Global Color Palette de Astra **y** en Site Settings → Global Colors de Elementor. Además hay que activar *Disable Default Colors* y *Disable Default Fonts* en Elementor → Settings, para que deje de imponer los suyos sobre el tema.

**El costo real son los estilos por widget.** Cada color elegido a mano dentro de un widget queda guardado en el `_elementor_data` de esa página y **le gana a cualquier global**. Con 510 páginas eso deja de ser una tarea y pasa a ser el proyecto entero — ver §2.ter.

**Al publicar:** *Regenerate CSS & Data* desde Elementor → Tools, que se hace desde el admin y no necesita servidor.

Y del lado de Astra, cuatro cosas más:

1. **Orden de carga.** Astra arma CSS dinámico desde el Customizer y lo inyecta *inline* con `wp_add_inline_style()` sobre el handle `astra-theme-css`. Una hoja del child theme encolada sin dependencia declarada puede imprimirse antes y perder. La hoja va encolada **con `astra-theme-css` como dependencia**, y lo que necesite ganar sí o sí se inyecta por el filtro `astra_dynamic_theme_css`, que corre después de todo lo de Astra.
2. **La paleta vive en el Customizer.** Astra guarda su *Global Color Palette* como opción y la emite en todo el sitio. Si no se cambia ahí, sigue pintando con sus defaults y el child theme queda peleando especificidad para siempre. Los cuatro colores SF se cargan en los slots de Astra **primero**; recién después se estiliza.
3. **Header y footer.** Astra 4 trae Header Footer Builder en el Customizer. Conviene **estilar su salida**, no reemplazarla por hooks: el equipo de Cultura sigue editando menús desde donde ya sabe. El vidrio entra como `.glass-bar` sobre el contenedor de Astra.
4. **Capas de fondo.** El fondo vivo y la trama se inyectan por el hook `astra_body_top`, sin tocar plantillas. Base del child theme: el `astra-child` oficial de Brainstorm Force, que ya resuelve el encolado.

Queda por confirmar si hay **Astra Pro**, y sobre todo **cuántas páginas Elementor hay**.

---

## 2.ter El problema de escala: 510 páginas

La versión anterior de este plan decía «se auditan página por página desde el editor». Con 510 páginas esa frase deja de ser una tarea y pasa a ser el proyecto entero.

### Costo de auditar a mano

Días hábiles de 6,5 horas productivas, sólo de abrir el editor y mirar — sin arreglar nada:

| Min. por página | 510 pág. | 250 pág. | 80 pág. | 40 pág. |
|---|---|---|---|---|
| 5 min | **6,5 d** | 3,2 d | 1,0 d | 0,5 d |
| 10 min | **13,1 d** | 6,4 d | 2,1 d | 1,0 d |
| 20 min | **26,2 d** | 12,8 d | 4,1 d | 2,1 d |

### La alternativa

| Enfoque | Costo |
|---|---|
| Abrir las 510 a mano, 10 min c/u | 13,1 días |
| Escaneo por script + top 40 a mano | 4,6 días |
| **Ahorro** | **8,5 días** |

Y el escaneo **no crece** con la cantidad de páginas; la auditoría manual sí.

### La herramienta, sin tocar el servidor

Un plugin chico, subido por *Plugins → Añadir nuevo → Subir*, que recorre el `_elementor_data` de los 510 posts y reporta cuáles traen colores **literales** en vez de referencias a globals. La distinción es exacta y verificable: un widget atado a un global guarda `"__globals__"`, uno pinchado a mano guarda el hex crudo. Con ese listado, el reemplazo por lote es una corrida más.

**Confirmado:** se pueden subir plugins por ZIP, así que este camino va y el escenario de +8 días sale de la mesa.

### La forma real de la cola — corrección

En la versión anterior escribí que «el 80% del tráfico suele concentrarse en el 5–10% de las URLs» y lo usé para justificar trabajar sólo el top 40. **Con los datos reales eso es falso para este sitio.**

Ajustando una Zipf a las 25 páginas más visitadas de 2026 el exponente da **−0,79**; ajustando sólo la cola (ranks 6–25) da **−0,92**. Las dos describen una cola **gorda**:

| Corte | Ajuste 25 puntos | Ajuste sólo cola |
|---|---|---|
| top 25 | 38,7% | 45,4% |
| top 40 | **45,8%** | **53,0%** |
| top 60 | 52,6% | 59,9% |
| top 100 | 62,1% | 68,8% |

Para llegar al 80% habría que tocar más de 200 páginas, no 40. Trabajar sólo el top 40 dejaría cerca de la mitad del tráfico en páginas sin revisar.

Sigue siendo una proyección — las colas reales caen más rápido que cualquier ajuste sobre la cabeza — pero la dirección invierte la recomendación anterior: **el reemplazo por lote tiene que cubrir las 510, no sólo el top.** Con el plugin habilitado eso no cuesta más.

El sitio entero proyecta unos **12.000 a 14.000 visitantes al año**, así que no es un problema de escala de tráfico sino de prolijidad: que no queden páginas huérfanas con la paleta vieja.

### Qué plantillas priorizar, según los datos

De las 25 más visitadas:

- **13 son fichas de espacio cultural** — museos, mercados, bibliotecas, el Anfiteatro, la Manzana Jesuítica, el Molino — y juntan el **42% del tráfico del top 25**. Es, por lejos, la plantilla más valiosa después de Inicio.
- **4 son Feria del Libro** (incluida Prensa), con el **20%**. Esto contesta una pregunta que estaba abierta: `/feriadellibro/` entra en el alcance.
- **4 son ciclos o festivales**: Noche de los Museos, Festival Diseña, Somos Música, Aniversario del Mercado.

### El triage sigue valiendo

Cada página que se saca del universo son minutos que no se pagan. El orden es: descartar por **estado** (borrador, papelera, privada), después **huérfanas** (sin enlaces ni visitas), después priorizar por tráfico. Con publicaciones que van de 2019 a 2026 hay candidatas claras a archivar, como las ediciones viejas de la Feria.

---

## 3. Fases

### Fase 1 · Relevamiento y triage — *3 a 4 días*
1. **Reducir las 510 a un universo manejable**, por el orden de §2.ter: estado, huérfanas, tráfico. La analítica del top 25 ya está; falta el corte por estado. Más el inventario de plugins, tipos de contenido y taxonomías, plantillas en uso, y si hay Astra Pro.
2. Exportar el árbol de URLs y quedarse con el top 50 por tráfico (Analytics/Search Console) — define qué plantillas se rediseñan primero.
3. Baseline: Lighthouse móvil, axe DevTools y capturas del estado actual. Sin esto no hay forma de demostrar la mejora.
4. Entorno de prueba. Sin acceso al servidor, la opción realista es un **staging local** (LocalWP / Studio) con una copia del sitio exportada por plugin de migración, más el uso de `?preview_theme=` en producción para la validación final. **[R]**
5. Extraer del Brandbook los SVG del lockup, el escudo y el set de iconos (02.5).

**Entregables:** planilla de triage con el universo reducido, baseline de métricas, repo con el staging y los assets.

### Fase 2 · Herramienta de auditoría — *2 a 3 días*
Plugin propio, subido por ZIP, que escanea el `_elementor_data` de las 510 y reporta colores literales contra referencias a globals. Incluye el reemplazo por lote **sobre todas las páginas**, no sólo el top — por lo de la cola gorda.

**Entregable:** el plugin, más el informe de qué páginas tienen color pinchado a mano.

### Fase 3 · Fundaciones del child theme — *4 a 5 días*
1. Child theme `capitalcultural-sf` sobre el `astra-child` oficial, con encolado declarando `astra-theme-css` como dependencia: tokens → componentes → plantillas.
2. Incorporar `docs/design-tokens.css` y autoalojar Geologica y Encode Sans.
3. **Global Colors cargados en los dos lados** — la paleta de Astra y la de Elementor — con *Disable Default Colors* y *Disable Default Fonts* activados. Más `theme.json` con esa misma paleta y la escala tipográfica. Es lo que evita que el sitio se desalinee de nuevo en tres meses.
4. Capas de fondo (`.sf-ambient` + `.sf-trama`) inyectadas por el hook `astra_body_top`.
5. Estilos base: tipografía, links, foco visible, `skip-link`.

**Entregable:** ZIP instalable del child theme, con el sitio ya en paleta correcta aunque sin componentes nuevos.

### Fase 4 · Biblioteca de componentes — *5 a 7 días*
Header glass sticky · menú mobile (el patrón `.glass-bar::before` de Freedom, que existe justamente para que el menú desplegable pueda esmerilar el contenido) · hero con barrido a 15° · tarjeta de evento/agenda · tarjeta de nota · filtros y chips · paginación · formulario de convocatorias · footer con marca completa.

Cada componente se entrega con estado *hover*, *focus-visible*, vacío y de carga. Se documentan en una página oculta del propio WP que funcione como styleguide viva.

**Entregable:** biblioteca completa + styleguide.

### Fase 5 · Plantillas Astra — *5 a 7 días*
En el orden que marca la analítica: **Inicio**, **ficha de espacio cultural** (13 del top 25, 42% del tráfico), **Feria del Libro** (4 del top 25, 20%), **ciclo o festival**, y después archivo de agenda con filtros, nota individual, convocatorias, búsqueda y 404.

**Entregable:** todo lo que renderiza Astra, retematizado en staging.

### Fase 6 · Páginas Elementor — *4 a 7 días*
Reemplazo por lote de los colores literales que encontró el escaneo, más las páginas del top trabajadas a mano. Incluye `/feriadellibro/` si entra en el alcance.

**La duración la fija el triage de la Fase 1**, no la cantidad total.

**Entregable:** sitio completo retematizado en staging.

### Fase 7 · Accesibilidad, performance y QA — *3 a 4 días*
1. Auditoría AA: contraste, navegación por teclado, landmarks, `alt`, foco en el menú mobile.
2. Verificar los tres modos degradados del vidrio (sin `backdrop-filter`, transparencia reducida, movimiento reducido).
3. Lighthouse móvil contra el baseline. CLS es el riesgo típico con fuentes autoalojadas y blur.
4. Matriz: Chrome, Firefox, Safari (iOS incluido — es donde `backdrop-filter` más varía), Edge, y un Android de gama baja real.
5. Repaso editorial: que el tono siga el "pulso / impulso" del Brandbook, en minúsculas y con barras.

**Entregable:** informe antes/después con métricas.

### Fase 8 · Publicación — *1 a 2 días*
Ventana de bajo tráfico, backup por plugin, subida del ZIP, activación, *Regenerate CSS & Data* de Elementor, verificación de las 50 URLs del inventario, 48 h de monitoreo.

**Total estimado: 28 a 36 días hábiles.**

Historial del número, porque cambió cuatro veces y siempre por dato nuevo, no por recalibrar:

| Momento | Rango | Qué lo movió |
|---|---|---|
| Page builder como hipótesis | 19–27 | — |
| Se confirma Elementor Free | 24–34 | Theme Builder es de Pro: Astra cubre más de lo que pensaba |
| Aparecen las 510 páginas | 27–39 | La auditoría manual deja de escalar |
| Plugin habilitado + analítica | **28–36** | Techo baja (no hay plan B), piso sube (el lote cubre las 510) |

La variación que queda es la normal de plantillas y QA, no la del recuento de páginas.

---

## 4. Riesgos

| Riesgo | Impacto | Mitigación |
|---|---|---|
| 510 páginas con estilos por widget | Medio — a mano serían 13 días sólo de mirar | Baja de alto a medio: se pueden subir plugins, así que el escaneo y el reemplazo por lote lo resuelven en 4,6 días |
| El CSS inline de Astra gana | Medio — aparecen colores viejos en lugares sueltos | Encolar con `astra-theme-css` como dependencia y usar el filtro `astra_dynamic_theme_css`; verificar plantilla por plantilla |
| Sin acceso al servidor | Medio | Todo por ZIP desde el admin; staging local; `?preview_theme=` para validar en producción |
| `backdrop-filter` en Android de gama baja | Medio | Presupuesto de ~8 blurs simultáneos, `glass-soft` en grillas, prueba en dispositivo real |
| El rojo no da AA en ningún combo | Alto — es el color identitario y su mejor contraste es 4.33 | Restringido a texto grande y grafismos. Con la paleta reducida pesa más, porque ya no hay Azul 02 para descargarle trabajo |
| Plugin de agenda con markup propio | Medio | Sobrescribir plantillas del plugin desde el child theme, nunca editar el plugin |
| La marca se vuelve a desalinear tras el lanzamiento | Bajo pero seguro | Global Color Palette de Astra + `theme.json` restringidos a cuatro colores, más la styleguide viva de la Fase 3 |

---

## 5. Decisiones pendientes

1. **Cuántas de las 510 están publicadas** y cuántas quedaron huérfanas. El top 25 por tráfico ya lo tenemos.
2. **¿Cuántas ediciones de Feria se conservan?** La XXXII está en el top 4, así que la Feria entra; la pregunta es si las anteriores se rediseñan o se archivan.
3. **Modo oscuro:** el Brandbook no lo contempla. Sugiero no hacerlo ahora. Si se hace, el rojo tiene que aclararse a `#ff7f63` sobre fondo azul profundo — `#e63312` ahí da 4.30 y no llega.
4. **Fotografía:** el vidrio necesita imágenes debajo para lucir, y con tres colores el peso visual recae más en la fotografía. ¿Hay banco de fotos de eventos con derechos resueltos?
5. **Licencias tipográficas:** Geologica y Encode Sans son de Google Fonts (OFL), así que autoalojarlas está permitido. Confirmar igual con el área de comunicación.

---

## 6. Archivos de este repo

- `docs/design-tokens.css` — tokens de marca + material GlassMorph, listos para encolar
- `docs/plan-glassmorph.md` — este documento
- Los dos PDFs normativos en la raíz
