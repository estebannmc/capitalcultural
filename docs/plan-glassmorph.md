# Capital Cultural · Plan de actualización visual (GlassMorph + marca SF 2026)

**Sitio:** https://www.santafeciudad.gov.ar/capitalcultural/
**Fuentes normativas:** `marca/SF_Brandbook_V2_light_compressed-1_compressed-1.pdf` (V1.0 · Febrero 2026, 67 pág.) y `marca/SF_paleta_cromática.pdf` (ficha 02.4)
**Estado actual del sitio:** `sitio-actual/` — CSS adicional, script de botones, plugins instalados y listado de páginas (ver §2.sexies)
**Stack confirmado:** WordPress + **Astra 4.13.1** + **Elementor Free**
**Enfoque acordado:** retematizado por child theme, sin migrar plataforma
**Sistema cromático:** cerrado — Azul 01, celeste, rojo o naranja de submarca y neutros. El verde institucional se abandonó por decisión política
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
| Celeste | `#57b8e9` | 87 184 233 | — (CMYK 60 10 0 0) | Superficie y acento |
| Rojo Cultura | `#e63312` | 230 51 18 | — | Color del tópico |
| Naranja Cultura | `#fca100` | 252 161 0 | — | Tópico, alternativa al rojo |

**Neutros:** `#ffffff` · `#dadad9` · `#606060` · `#1d1d1b`

**Degradé de transición:** `linear-gradient(105deg, #57b8e9 → #002751)`. Corre por la misma diagonal que la trama, así que degradé y trama leen como un solo gesto. Es fondo y grafismo; si lleva texto, sólo blanco y sólo sobre el tramo azul, porque el extremo celeste no da contraste.

**Fuera de sistema para este sitio:** Azul 02 `#1639f1`, Verde 01 `#00c08b`, Verde 02 `#00fe9b` y amarillo `#f2e61a`. Existen en el Brandbook pero no entran acá, y `docs/design-tokens.css` directamente no los declara: lo que no existe como token no se usa por accidente.

### 1.2 Regla de contraste (medida, WCAG 2.1)

```
azul-01  / blanco    14.90  AAA       texto libre
negro    / blanco    16.88  AAA       texto libre
azul-01  / gris-20   10.65  AAA
azul-01  / celeste    6.70  AA        texto sobre celeste: SIEMPRE azul-01
azul-01  / naranja    7.27  AA        texto sobre naranja: SIEMPRE azul-01
gris-75  / blanco     6.29  AA        texto secundario
blanco   / rojo       4.33  AA-large  sólo ≥24px (o ≥19px bold)
azul-01  / rojo       3.44  AA-large
blanco   / celeste    2.23  FALLA     nunca blanco sobre celeste
celeste  / blanco     2.23  FALLA     el celeste nunca es texto sobre claro
blanco   / naranja    2.05  FALLA     nunca blanco sobre naranja
```

**Tres reglas salen de acá.** Celeste y naranja son color de **fondo**, y el texto encima siempre es Azul 01, nunca blanco. El rojo **no alcanza AA en ningún combo**: titulares grandes, filetes y barras, nunca texto corrido. Y como Azul 02 quedó fuera, **los links se distinguen por subrayado**, no por tono — el subrayado va en rojo, que ahí funciona porque es grafismo y no texto.

Al reducir la paleta el rojo queda más expuesto, porque ya no hay Azul 02 para descargarle trabajo. Conviene tenerlo presente al redactar piezas.

Esto no es preferencia estética: es un sitio de gobierno, le aplica la Ley 26.653 y la ODA 1.0 de ONTI, que toman WCAG AA como piso obligatorio.

### 1.3 Tipografía (02.1 / 02.2)

- **Geologica** — tipografía de marca, **exclusiva de signos de identidad** (el lockup `SF / CULTURA` y poco más). El Brandbook es explícito en esto.
- **Encode Sans** — tipografía de comunicación: todos los titulares, bajadas y texto corrido.

Ambas están en Google Fonts. Por el bloqueo de servidor se autoalojan como archivos del child theme (`/fonts/*.woff2` + `@font-face` con `font-display: swap`), lo que además evita la dependencia externa que suele objetar el área de sistemas del municipio.

### 1.4 Trama de marca (02.6)

La identidad se construye sobre diagonales a **15°**, derivadas del paralelogramo del logo. El Brandbook dice que la trama propone «acción, dinamismo, transformación y movimiento permanente».

**El signo del ángulo importa.** La barra del logo se inclina hacia la **derecha**: subiendo, la línea se corre a la derecha (`/`). Para `repeating-linear-gradient` eso es **105deg**, la perpendicular. `75deg` produce la trama **espejada** (`\`), que invierte esa lectura de movimiento hacia adelante. Es un carácter de diferencia en el CSS y cambia el sentido de toda la página.

**El remate no es una textura.** La primera versión de este plan proponía dos `repeating-linear-gradient(105deg, …)` cubriendo todo el fondo. Se lee como papel tapiz: la línea siempre igual, siempre al mismo paso, sin jerarquía. El remate real de la marca es una **pieza compuesta** anclada abajo a la derecha: catorce barras a 15° donde cada una **arranca gruesa en la base, se corta, y sigue fina hacia arriba**. Celestes en distintas intensidades, un par en Azul 01, y las dos últimas en rojo de Cultura.

**Cada barra es un solo polígono**, no dos piezas con un espacio en el medio. El **borde derecho es continuo** de la base al tope; el izquierdo sube por el tramo grueso y ahí **se inclina** hasta fundirse con el borde del tramo fino. Esa diagonal es el escalón: no un corte recto, sino el merge entre los dos anchos, y deja el tramo fino alineado a la derecha de la misma línea.

Arriba **cierra en punta**, no en corte plano.

Tres cosas son fáciles de arruinar, y las tres están anotadas en los tokens con su porqué:

- **Largo de la diagonal** — larga, la barra se lee como una hoja que se afila; corta, como una línea gruesa que se afina de golpe, que es lo que pide la marca. Quedó en unos 30 de los 440 del alto.
- **Dónde arranca** — a un tercio del alto, no a la mitad: al medio el tramo grueso manda demasiado y la pieza se apelmaza abajo.
- **Largo de la punta** — el hilo corre paralelo y sólo converge en los últimos 46. Si converge desde el escalón, las ocho barras se leen como briznas de pasto en vez de líneas.
- **Ancho del hilo** — 2 sobre una base de 16, casi un hilo. Más grueso y el escalón deja de leerse como un cambio de registro.

**La distribución es uniforme, y no sólo por estética.** Mismo paso, mismo ancho de base, misma inclinación y misma opacidad hacen que la separación entre barras se mantenga en 18 **a cualquier altura**; si los anchos variaran, las barras se abrirían o se juntarían hacia arriba. Lo único que cambia es el alto, que crece parejo de 150 a 420, y por eso los finales **suben en línea recta hacia el borde derecho**.

Son ocho: siete celestes y una en el tópico de Cultura. La geometría está en `docs/barras.svg`: 8 polígonos, viewBox `0 0 395 440`, con los `fill` en `var(--sf-celeste)` y `var(--topico)` para que se re-tiña con los mismos tokens. Pesa menos que una imagen. Va como partial del child theme, inyectada por `astra_body_top`, al 30% de opacidad y anclada a la esquina: más grande o más opaca cruza el texto a media página, que es exactamente el problema que tenía la trama repetida.

Traducción a web:

- el **remate de barras** (`.sf-barras`) en la esquina inferior derecha;
- la barra del logo como marcador, **siempre pegada al título y del tamaño del título**, nunca como filete del bloque entero. Las medidas van en `em`, así toma el cuerpo del texto que encabeza sin ajustes: `<h2><i class="sf-barra"></i>Título</h2>`. Reemplaza a los puntos redondos genéricos como indicador de estado y severidad;
- el barrido de luz del hero y de los botones (`transform: skewX(-15deg)`), el mismo que ya usa Freedom y que venía con el ángulo correcto;
- los listones del footer y el degradé, los dos a 105°.

### 1.5 El lockup y sus esquinas

Verificado contra las páginas 12, 28 y 51 del Brandbook: **el bloque redondea sólo sus dos esquinas extremas opuestas** — arriba a la izquierda y abajo a la derecha. Las que caen sobre la diagonal quedan **en punta**. El radio es ~13% del alto del bloque.

En la **marca completa** (SF + escudo) el redondeo salta a los extremos del conjunto: arriba-izquierda del bloque SF y abajo-derecha del panel del escudo. Los cuatro cortes de la diagonal interna van en punta.

**Sobre fondo oscuro el bloque SF va calado, no macizo.** Es lo que trae el asset oficial (`marca/logoFooter.svg`): contorno blanco de 1px y relleno del fondo, con las letras SF en blanco; el panel del escudo sí va lleno de blanco, con el emblema en Azul 01. No es lo mismo que el lockup chico de cabecera, que sí es un bloque macizo de Azul 01 — y es la diferencia que el footer del sistema visual tenía mal hasta que llegó el SVG.

En CSS el `skew` se aplica al mismo elemento que el `border-radius`, así que la curva se inclina junto con la forma, como en el original. La construcción está en `docs/design-tokens.css` como `.sf-lockup`, para cuando no convenga cargar el SVG; para la marca completa sobre oscuro va directo `marca/logoFooter.svg`.

En el lockup de tópico la palabra va en **Encode Sans Bold con tracking casi nulo** (`.005em`), no espaciada, y la barra del tópico no lleva redondeo: es un corte limpio.

### 1.6 Botones

Radio **10px** — más cerrado que los 24px de los paneles, porque un botón es un objeto y un panel es una superficie. Alto mínimo **44px** por objetivo táctil. La barra del logo entra como marcador y el barrido de hover corre a 15°.

| Variante | Combinación | Contraste | Veredicto |
|---|---|---|---|
| Primario | blanco / Azul 01 | 14.90 | AAA |
| Línea | Azul 01 / fondo | 13.54 | AAA |
| Celeste | Azul 01 / celeste | 6.70 | AA |
| Desactivado | Gris 75 / Gris 20 | 4.50 | AA |
| Tópico | blanco / rojo | 4.33 | **sólo texto grande** |
| — | Azul 01 / rojo | 3.44 | **sólo texto grande** |
| — | blanco / celeste | 2.23 | **nunca** |

**El rojo es la restricción que manda en el sistema.** No existe botón rojo en tamaño normal que sea accesible sin salirse del color de marca. La salida no es achicar el problema sino ubicarlo: el rojo es el **CTA grande de un hero**, uno por pantalla, y en todo el resto vive como la barra dentro de un botón azul. Por eso `.sf-btn--topico` viene forzado a 19px y no existe en tamaño normal.

En el primario la barra va en rojo sobre el relleno Azul 01: replica literalmente el lockup SF/CULTURA.

**Foco:** un anillo, no un cambio de relleno — `box-shadow: 0 0 0 2px halo, 0 0 0 5px rojo`. El anillo rojo contra el fondo claro da 3.94, por encima del 3:1 de WCAG 1.4.11. En el botón celeste el halo se invierte a Azul 01, porque el halo blanco sobre celeste da 2.23 y no separa. **Desactivado** no usa `opacity`, que hunde el contraste justo cuando menos conviene.

**Que Elementor los respete:** el sistema se entrega dos veces — como clases del child theme para lo que renderiza Astra, y cargado en *Site Settings → Theme Style → Buttons* para que los botones de Elementor hereden. Encima va un override de `.elementor-button` como red de seguridad.

### 1.7 La lámina de luz (footer)

El footer recrea la lámina de la página 90 del Brandbook — el haz celeste sobre Azul 01 — **en CSS y SVG, sin imagen**. Tres piezas, ninguna con gradientes repetidos:

1. **El foco celeste** sobre la base Azul 01. Su centro va **fuera del lienzo** (`74% 118%`): adentro se le ve el círculo, y las barras encima quedarían celeste sobre celeste y desaparecen.
2. **El mismo remate de ocho barras** que va en la esquina de la página, reusado con `<use href="#sf-barras">`. No hay un segundo juego: **una sola pieza para toda la identidad**. Acá son la luz sobre el Azul 01 — más chicas y alineadas al mismo margen que el contenido, con un `filter` de realce que les devuelve presencia sobre el fondo oscuro sin tocar la geometría.
3. **El bloque de texto apoyado encima** con `.glass--luz`, un vidrio oscuro translúcido que esmerila la luz y las barras que pasan por detrás. Es el caso de uso que justifica todo el sistema: vidrio sobre algo que vale la pena esmerilar.

**Es el vidrio el que carga con el contraste del texto**, así que el velo oscuro quedó liviano — sólo protege a la marca, que sí se apoya directo sobre la lámina. En una versión anterior el velo tenía que hacer todo el trabajo y se comía la mitad de la pieza.

---

## 2. Arquitectura de marca aplicada

Capital Cultural es **nivel 01/A** del sistema: logo síntesis + tópico + color.

- **Header:** lockup `SF / CULTURA` con el filete rojo, enlazado a `santafeciudad.gov.ar`.
- **Nombre del sitio:** "Capital Cultural" en Encode Sans, no en Geologica (no es un signo de identidad).
- **Footer:** marca completa con Escudo Municipal + bajada "Municipalidad de Santa Fe".
- **Redes:** el Brandbook ya fija el handle `cultura.santafecapital` (pág. 51); conviene que el footer lo respete.

---

## 2.0 Relevamiento del sitio real — correcciones al plan

Hasta acá el plan se escribió sin poder ver el sitio: el dominio estaba bloqueado por la política de red del entorno. Con el HTML de **Inicio** y **Museos y Salas** a la vista, **cinco premisas del plan eran falsas**. Quedan corregidas abajo; el resto del documento ya refleja lo corregido.

### 1. Astra NO renderiza el header ni el footer

Está instalado **`header-footer-elementor`** (HFE). El `<body>` trae `ehf-header ehf-footer page-template-elementor_header_footer ast-page-builder-template`. Header y footer son **bloques HTML de Elementor** con clases propias (`cc-header`, `cc-menu`, `footer-cc`).

Esto invalida el argumento con el que dimensioné el proyecto: *«sin Theme Builder, Astra cubre la mayor parte del sitio»*. **No la cubre.** El child theme sigue siendo necesario para tokens y base, pero header, footer y el grueso del contenido se tocan en Elementor.

### 2. El contenido no usa widgets de Elementor

De los seis widgets de la home: **cuatro `html` y dos `shortcode`**. Nada de widgets con sus paneles de color.

Eso rompe la estrategia de la Fase 2 tal como estaba escrita. El escaneo de `_elementor_data` sigue sirviendo —el HTML crudo se guarda ahí dentro— pero **no hay "ajustes de color de widget" que reemplazar por globals**: los colores están en HTML y CSS escritos a mano. El reemplazo por lote pasa a ser sobre cadenas de color dentro de bloques HTML, que es más delicado y necesita revisión humana por página tocada.

### 3. Hay dos plugins a medida, sin documentar

- **`capital-cultural-programacion-v330`** — el slider de «Propuestas activas» (clases `ccp-*`), con su propio `frontend.css` y `frontend.js`.
- **`capital-cultural-slider`** — otro slider (`cc-slider`), con `slider.css` y `slider.js`.

Son superficie nueva que el plan no contemplaba. Hay que leer su CSS antes de estimar: si traen colores fijos, se tocan ahí y no desde el tema. ~~El sufijo `v330` sugiere versionado a mano, así que conviene averiguar quién los mantiene.~~ **Resuelto:** la lista de plugins (§2.sexies) los da como propios — *Capital Cultural Slider* 2.1.8 y *Capital Cultural – Programación de Museos* 3.3.0, los dos de Esteban. Como el código es nuestro, los colores fijos se corrigen en el plugin mismo, sin sobrescribir desde el tema.

### 4. Los Global Colors ya están puenteados — pero a medias

Astra tiene `astglobalcolor0 = #002751` (Azul 01) y `astglobalcolor1 = #00c08b` (el Verde 01 que se abandonó), y Elementor los lee como `--e-global-color-astglobalcolor*`. **Pendiente:** pasar `astglobalcolor1` a `#57b8e9` (celeste) en Astra → Personalizar → Global, así todo lo que Elementor pinta con ese global cambia de una vez.

Lo que falta: los slots 2, 3, 5, 7 y 8 siguen con los grises de Astra (`#1e293b`, `#334155`, `#F0F5FA`, `#D1D5DB`, `#111111`) en vez de los neutros SF. Y el **rojo de Cultura `#e63312` no aparece ni una vez en todo el HTML**. ~~El color del tópico hoy no se usa.~~ **Corregido en §2.sexies:** sí se usa, pero desde el CSS adicional del Customizer, como la barra de hover de los botones `.cb-btn`.

### 5. No hay agenda, y el sitio se organiza por destino

Cero ocurrencias de «evento», «calendario» o «cartelera». El menú es **plano, de 16 entradas** (el relevamiento decía 17; `sitio-actual/header.html` da 16), y son lugares y programas: + Feria, Festival Diseña, Noche de los Museos, Feria del Libro, Anfiteatro del Sur, Estación Belgrano, Mercado Progreso, Teatro Municipal (externo), Centro Cultural Muttis, Museos y Salas, Somos Música, Elenco y Organismos, Red de bibliotecas santafesinas, Huellas de mi Ciudad, Caminos Culturales, Convocatorias vigentes.

**No es un sitio de agenda sino un directorio de espacios y programas.** Cualquier propuesta de agenda unificada es *alcance nuevo*, no rediseño — y hay que presupuestarla aparte.

### Lo que sí confirma el relevamiento

- **Encode Sans ya está cargada**, por Astra y por Elementor. La tipografía de marca ya está en el sitio. (También carga **Alegreya Sans**, que no es de marca y habría que sacar.)
- **Las publicaciones en PDF ya son un patrón existente** y no sólo de Feria: Museos y Salas lista 14 PDFs por año y número. El componente de descarga sirve para todo el sitio, no para un caso.
- **Museos y Salas es un hub** con 10 espacios bajo `/museos-y-salas/<slug>/`: CEC, Fotogalería Municipal, Manzana Jesuítica y Colegio Inmaculada, Museo de la Ciudad, Museo de la Constitución Nacional, Museo Municipal César López Claro, Museo Municipal de Artes Visuales Sor Josefa Díaz y Clucellas, Museo del Teatro, Sala Ariel Ramírez, Sala Mercado Editorial.
- El logo en uso es el **anterior** (`legado1recursoLogoCult_1@2x-8.png`).
- Elementor **4.3.3**, WordPress **6.9.9**, Astra **4.13.1**. Elementor Free confirmado: no hay plugin `elementor-pro`. (La lista de plugins posterior ya da Elementor **4.3.4**.)

---

## 2.quinquies Alcance, decidido

> **El rediseño conserva la estructura por espacios. No se agrega agenda.**

La agenda de la ciudad ya existe y vive en **otro dominio**
(`agenda.santafeciudad.gov.ar`), que es el que la mantiene actualizada.
Duplicarla acá sería mantener dos calendarios en desacuerdo, y el que
perdería sería el nuestro: Capital Cultural no tiene quién le cargue
eventos todos los días.

Así que el sitio sigue siendo lo que ya es —**un directorio de destinos
culturales**— y el rediseño lo hace mejor en eso, en vez de convertirlo
en otra cosa:

| Decisión | Qué implica |
|---|---|
| El menú plano de 16 entradas se agrupa, no se recorta | Espacios · Ciclos y programas · Institucional. Las 16 siguen alcanzables; dejan de competir entre sí en una sola fila. |
| La cabecera muestra cuatro destinos y un disparador «Todos los destinos» | Es el patrón que el sitio ya tiene (`cc-menu-toggle` / `cc-menu-panel`); se le da forma, no se lo reemplaza. |
| El home destaca **muestras vigentes**, no fechas | Una muestra dura meses. Una agenda se vence sola en una semana, y entonces el home miente. |
| Hay un enlace saliente a la agenda, visible y rotulado | En el pie y en la ficha de espacio. Se declara que está en otro dominio en lugar de aparentar que es local. |
| La ficha de espacio no lleva «agenda del espacio» | Lleva datos prácticos (horario, dirección, entrada, accesibilidad), las muestras en curso y galería. |

**Lo que esto saca del presupuesto:** no hay tipo de contenido «evento»,
ni vistas de calendario, ni filtros por fecha, ni sincronización con el
dominio de la agenda. El trabajo es de plantillas y sistema visual.

**Lo que esto mete:** el hub de Museos y Salas y la ficha de espacio
pasan a ser las dos plantillas críticas del sitio, porque son el
recorrido principal. Ya estaban priorizadas por tráfico (§2.ter); la
decisión de alcance lo confirma.

---

## 2.sexies Lo que ya está cargado en el sitio — `sitio-actual/`

El relevamiento de §2.0 se hizo desde el HTML publicado. Estos seis archivos salen del **panel**, y muestran lo que el HTML no deja ver: qué CSS se suma por fuera del tema, qué script mueve los botones, cómo están escritos el header y el footer, qué plugins están activos y qué páginas importan.

### El CSS adicional es una cuarta capa

`cssAdicionalCCAstra.css` son **626 líneas** cargadas en *Personalizar → CSS adicional*. A las tres capas de §2.bis —Astra, Elementor, child theme— se suma ésta, que hoy es la que estila header, menú, footer, título, breadcrumb y botones. El child theme la **absorbe y la vacía**: no conviven, porque el CSS adicional se imprime después de todo y le ganaría al tema.

Lo que hay adentro, y qué implica:

1. **Encode Sans por `@import` a Google Fonts, con `!important` sobre todos los elementos** —`div` y `span` incluidos—. Es exactamente la dependencia externa que §1.3 saca al autoalojar, y además bloquea Geologica: con ese `!important` sobre `span` y `div`, el lockup no puede tomar la tipografía de marca sin otro `!important` encima. Se borra al activar el child theme, no antes.
2. **Bloques repetidos con valores distintos.** El menú está dos veces (secciones 05 y 08) y título + breadcrumb también (04 y 06); la numeración salta de 11 a 13. Gana el segundo de cada par, pero **no del todo**: el primero declara `position: fixed; top: 50px; right: 50px` en `.cc-menu-toggle` y el segundo no lo pisa, así que el botón del menú sigue fijo por el bloque que parece muerto. **Borrar "el duplicado" cambia el header.** Hay que fusionar los dos a mano, propiedad por propiedad.
3. **Los colores de marca ya están**: Azul 01 y Verde 01 en todo (el verde ya pasó a celeste en el CSS adicional), y el rojo `#e63312` en la barra de `.cb-btn` — lo que corrige §2.0.4. Los que sobran son dos grises de Tailwind en el breadcrumb: `#6b7280` (4.83 sobre blanco, pasa AA) y `#9ca3af` en el separador (2.54, sólo decorativo). Se reemplazan por los neutros SF.
4. **Dos fallas de accesibilidad** que el rediseño tiene que cerrar:
   - el hover de `.cc-menu-toggle` pasaba el fondo a Verde 01 con las tres líneas en blanco: **2.36**, por debajo del 3:1 que WCAG 1.4.11 pide a un control. Ya corregido: el fondo pasa a celeste y las líneas a Azul 01 (6.70).
   - `.cb-btn:focus-visible` es igual al hover: fondo blanco **con borde blanco**. Sobre fondo claro el botón enfocado se borra entero. Es el caso que el anillo de foco de §1.6 resuelve.
5. **El footer ya tiene la estructura** de §1.7: `.footer-cc` en Azul 01, grilla de dos columnas, hover en celeste (6.70 sobre Azul 01, pasa). Las clases (`footer-cc`, `footer-inner`, `footer-left`, `footer-right`, `footer-bottom`) las usa el bloque HTML de Elementor, así que el child theme **las conserva** y les cambia el estilo: renombrarlas obliga a editar el bloque.

### `.cb-btn` es el botón de §1.6 a medio camino

La grilla de botones (`.cb-grid` / `.cb-btn`) ya hace lo que pide el sistema: relleno Azul 01, **barra roja a 15° inclinada a la derecha** (`rotate(15deg)`, el signo correcto según §1.4) y el texto que se corre al entrar el hover. Le falta lo que §1.6 agrega: el anillo de foco, el radio de 10px —hoy es recto—, el respeto a `prefers-reduced-motion` y un hover que no desaparezca sobre fondo claro. **Se evoluciona, no se reemplaza**: el markup que ya está en las páginas sigue sirviendo.

### `botonesCC.js` sobra

El script mide con `getBoundingClientRect` dónde empieza el texto de cada `.cb-btn` y ubica la barra 17px antes. Recalcula en `load`, `resize`, `fonts.ready`, el init de Elementor, a los 300 y 800 ms, y en **cada `mouseenter` del documento en fase de captura**: pasar el mouse sobre cualquier elemento de la página vuelve a medir todos los botones. Es lectura de layout constante para resolver algo que CSS hace solo.

La barra se cuelga del `span` (`.cb-btn span::before`, con `right: 100%` y el margen que hoy calcula el script) y viaja con el texto sin medir nada. Si se quiere conservar el efecto actual —barra quieta, texto que se corre—, el `::before` compensa el desplazamiento con un `translateX(-12px)` en hover. El child theme lo resuelve así y el script se retira, junto con el lugar desde donde se inyecta (probablemente WPCode, ver abajo) **[R]**.

### Header y footer: el markup de los bloques de UAE

`header.html` y `footer.html` son el contenido de los dos bloques HTML que UAE monta como cabecera y pie. Es el markup exacto que estila el CSS adicional, y el que el rediseño tiene que reemplazar **del lado de Elementor**: el child theme no lo genera.

**El header** es el logo y un botón de menú, nada más. Las 16 entradas viven en un panel a pantalla completa que se abre con el botón, también en escritorio: hoy el sitio **no muestra ningún destino** sin un clic. Es lo que corrige la decisión de §2.quinquies (cuatro destinos a la vista y «Todos los destinos»). Lo que hay que resolver al rehacerlo:

1. **El panel cerrado sigue en el recorrido del teclado.** Se esconde con `transform: translateY(-100%)`, sin `visibility: hidden` ni `inert`: quien navega con Tab pasa por 16 links invisibles antes de llegar al contenido, y un lector de pantalla los lee.
2. **El botón no dice su estado.** Siempre es «Abrir menú», sin `aria-expanded` ni `aria-controls`. Tampoco cierra con Escape ni devuelve el foco al botón.
3. **El script depende de `DOMContentLoaded`.** Si el bloque se ejecuta después de ese evento —que es lo que pasa cuando LiteSpeed demora o combina el JS—, el listener nunca corre y **el menú no abre**. Hay que confirmar la configuración de LiteSpeed **[R]**; el reemplazo se inicializa sin esperar el evento.
4. El logo es el **anterior** (`legado1recursoLogoCult_1@2x-8.png`, §2.0), en PNG. El lockup nuevo va en SVG.
5. Teatro Municipal sale a otro dominio sin aviso. Se rotula como externo, igual que la agenda.

Cada link envuelve el texto en un `<span>`: el mismo enganche que usa `.cb-btn`, útil para la barra a 15° delante del destino activo.

**El footer** ya tiene casi todo lo que pide §2:

- el enlace saliente a la **Agenda** y a **Convocatorias vigentes**, que §2.quinquies pedía — falta rotularlos como externos;
- Instagram con el handle **`cultura.santafecapital`**, el que fija el Brandbook (pág. 51) — ya está bien;
- la marca de la Municipalidad, pero como PNG (`logoMuniPNG-8.png`): se reemplaza por la marca completa en SVG de §1.7.

Y tiene cinco detalles para corregir de paso: dos `style="color:#ffffff"` inline en los títulos, que le ganan a cualquier hoja y hay que sacar; títulos que saltan de `h2` a `h4`; el teléfono `4571609` sin característica ni enlace `tel:` (va `tel:+543424571609`); el año del © escrito a mano; y la sección de redes con YouTube `@SantaFeCapital`, que parece la cuenta general de la ciudad y no la de Cultura — confirmar si es la intención.

**Cómo se entrega:** el header y el footer nuevos se escriben como HTML en este repo, en el mismo formato que estos dos archivos, y se pegan en los bloques de UAE. Así quedan versionados, y la vuelta atrás es pegar el anterior.

### Plugins activos

`pluginsCC.txt` cierra el inventario que pedía la Fase 1. Los marcados con *¿es necesario?* son los que el propio equipo puso en duda:

| Plugin | Versión | Qué implica para el rediseño |
|---|---|---|
| **Ultimate Addons for Elementor (UAE)** · *¿es necesario?* | 2.9.4 | **Sí, es necesario.** Es el nombre actual de `header-footer-elementor` (§2.0.1): arma el header y el footer. Desactivarlo deja el sitio sin cabecera ni pie |
| Elementor | 4.3.4 | Free, como estaba relevado |
| Capital Cultural Slider | 2.1.8 | Propio. Shortcode `[capital_slider]` en el home. Se retematiza en el plugin |
| Capital Cultural – Programación de Museos | 3.3.0 | Propio. Muestras, actividades y talleres de los museos: alimenta el hub y la ficha de espacio, las dos plantillas críticas de §2.quinquies |
| Breadcrumb NavXT · *¿es necesario?* | 7.5.2 | Hay un `.cc-breadcrumb` armado a mano en el CSS adicional, y Yoast también genera migas. Si las migas visibles son las de `.cc-breadcrumb` o las de Yoast, NavXT sobra **[R]** |
| Contact Form 7 + ReCaptcha v2 · *¿es necesario?* | 6.1.6 | Depende de si queda algún formulario. Convocatorias vigentes vive **fuera** del sitio (ver abajo), así que el «formulario de convocatorias» de la Fase 4 probablemente no exista **[R]** |
| Post Type Switcher · *¿es necesario?* | 4.0.1 | Herramienta de admin, no toca el front. Neutral para el rediseño |
| Redux Framework · *¿es necesario?* | 4.5.15 | Framework de opciones que suele instalarse como dependencia de otro plugin. Antes de sacarlo, confirmar que nadie lo requiere **[R]** |
| LiteSpeed Cache | 7.9.1 | **Purgar la caché después de cada cambio de CSS**, o se sigue sirviendo el viejo. Si combina o minifica CSS, verificar que respete el orden de carga de §2.bis. Su optimización de imágenes genera WebP: candidato para la decisión pendiente 5 |
| Wordfence Security | 9.0.1 | Puede frenar la subida de ZIPs o el escaneo largo del plugin de auditoría de la Fase 2. Avisar antes de correrlo |
| WPCode Lite | — | Inserta fragmentos de código: otra capa más, fuera del tema. Inventariar sus fragmentos en la Fase 1 — es el lugar probable de `botonesCC.js` |
| WP Statistics | 14.16.13 | Analítica propia del sitio: fuente para el corte por tráfico del triage |
| WP Rollback | 3.1.2 | Sirve para volver atrás plugins de wordpress.org, **no** el child theme propio: para eso el respaldo es el ZIP anterior |
| Yoast SEO | 28.5 | Sin impacto visual. Conservar títulos y metadatos al tocar plantillas |
| ImageMagick Engine | 2.0.0 | Mejor redimensionado de imágenes. Neutral |

### Las páginas que importan: 71 de 510

`paginasUtilCC.docx` lista **79 entradas con 75 enlaces**: 71 páginas del sitio (Inicio incluido), 2 PDFs de Caminos Culturales y 3 destinos externos.

| Sección | Páginas |
|---|---|
| Huellas de mi Ciudad | 18 — 14 son de *Memorias Urbanas Feministas* |
| Museos y Salas | 11 — el hub y los 10 espacios de §2.0 |
| Mercado Progreso | 9 |
| Elenco y Organismos, + Feria | 5 cada una |
| Festival Diseña, Biblioteca Muttis | 4 cada una |
| Feria del Libro, Anfiteatro, Noche de los Museos, Caminos Culturales | 2 cada una |
| Estación Belgrano, Somos Música, Capacitaciones 4×4, Día del Folclore, Banda Sinfónica, Bibliotecas Populares, Inicio | 1 cada una |

Tres cosas salen de acá:

1. **El trabajo manual tiene techo conocido.** El reemplazo por lote sigue cubriendo las 510 (§2.ter, por la cola gorda), pero lo que se revisa a mano con criterio son estas 71: a 10 minutos cada una, **1,8 días**. Las 439 restantes son las candidatas a archivar en el triage.
2. **Aparecen páginas por año.** `teatromercado25` y `teatromercado26` son el mismo ciclo en dos ediciones: el mismo patrón que la Feria. Las ediciones viejas van al triage como archivables.
3. **Tres destinos ya viven afuera:** la agenda (`agenda.santafeciudad.gov.ar`), el Teatro Municipal (`teatromunicipal.santafeciudad.gov.ar`) y **Convocatorias vigentes**, que está en `/secretaria-de-cultura/`, fuera de `/capitalcultural/`. Los tres se enlazan hacia afuera con rótulo, como decidió §2.quinquies para la agenda.

---

## 2.bis Trabajar sobre Astra + Elementor Free

Son **tres capas de estilo peleando por el mismo píxel**: Astra, Elementor y el child theme. En el sitio real hay dos más, fuera del tema: el **CSS adicional** del Customizer y los fragmentos de **WPCode** (§2.sexies). El child theme absorbe las dos.

**~~Lo que Elementor Free no toca.~~ Corregido en §2.0:** el sitio tiene `header-footer-elementor`, así que header y footer los arma Elementor igual, sin necesidad de Pro. Astra aporta la base y los tokens; el child theme sigue siendo necesario, pero **no cubre la mayor parte del sitio** como decía esta versión del plan.

**Los Global Colors van dos veces.** Los cuatro colores se cargan en la Global Color Palette de Astra **y** en Site Settings → Global Colors de Elementor. Además hay que activar *Disable Default Colors* y *Disable Default Fonts* en Elementor → Settings, para que deje de imponer los suyos sobre el tema.

**El costo real son los colores escritos a mano.** El relevamiento (§2.0) muestra que no son ajustes de widget sino **HTML y CSS crudos dentro de bloques `html`**, más el CSS de los dos plugins a medida. Siguen guardados en el `_elementor_data` de cada página y le ganan a cualquier global, pero el reemplazo es sobre cadenas de color en HTML, más delicado y con revisión humana por página tocada — ver §2.ter.

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
- **4 son ciclos o festivales**: Noche de los Museos, Festival Diseña, Somos Música, Aniversario del Mercado.
- **4 son Feria del Libro** (incluida Prensa), con el **20%** — pero son un caso aparte, ver abajo.

### La Feria no es una plantilla

**Cada edición se rediseña de cero.** De las anteriores sólo se aloja el **PDF de programación** para consulta. Eso cambia dos cosas:

1. **No hay plantilla de Feria que mantener.** Lo que se construye es un **archivo de programaciones en PDF**: un listado por edición con su descarga, título, año y peso del archivo. Es un componente chico y reusable, muy distinto de rediseñar páginas.
2. **La edición vigente lleva sólo una pasada de marca.** Está viva y concentra el 20% del top 25, así que no puede quedar con la paleta anterior — pero no tiene sentido invertir en una plantilla reusable de algo que se rehace el año que viene.

Y para el triage: **las ediciones anteriores de Feria son candidatas directas a archivar**, reemplazadas por su PDF. De las 510 eso saca un bloque conocido sin discusión.

### El triage sigue valiendo

Cada página que se saca del universo son minutos que no se pagan. El orden es: descartar por **estado** (borrador, papelera, privada), después **huérfanas** (sin enlaces ni visitas), después priorizar por tráfico. Con publicaciones que van de 2019 a 2026 hay candidatas claras a archivar, como las ediciones viejas de la Feria.

---

## 3. Fases

### Fase 1 · Relevamiento y triage — *3 a 4 días*
1. **Reducir las 510 a un universo manejable**, por el orden de §2.ter: estado, huérfanas, tráfico. La analítica del top 25 ya está; falta el corte por estado. El listado de 71 páginas útiles y el inventario de plugins ya están (§2.sexies); faltan los fragmentos de WPCode, tipos de contenido y taxonomías, plantillas en uso, y si hay Astra Pro.
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
4. Capas de fondo (`.sf-ambient` + `.sf-barras`) inyectadas por el hook `astra_body_top`.
5. Estilos base: tipografía, links, foco visible, `skip-link`.

**Entregable:** ZIP instalable del child theme, con el sitio ya en paleta correcta aunque sin componentes nuevos.

### Fase 4 · Biblioteca de componentes — *5 a 7 días*
Header glass sticky, **entregado como HTML para el bloque de UAE** (sobre `sitio-actual/header.html`) · menú mobile, con el panel fuera del recorrido de teclado cuando está cerrado (el patrón `.glass-bar::before` de Freedom, que existe justamente para que el menú desplegable pueda esmerilar el contenido) · hero con barrido a 15° · tarjeta de evento/agenda · tarjeta de nota · **galería de fotos** (ahora que hay banco propio) · **ítem de descarga de PDF** (para el archivo de programaciones de Feria) · filtros y chips · paginación · formulario de convocatorias (probablemente sale: Convocatorias vigentes vive fuera del sitio, §2.sexies) · **grilla de botones `.cb-btn`**, evolucionada desde la actual y sin `botonesCC.js` · footer con la lámina de luz y marca completa.

Cada componente se entrega con estado *hover*, *focus-visible*, vacío y de carga. Se documentan en una página oculta del propio WP que funcione como styleguide viva.

**Entregable:** biblioteca completa + styleguide.

### Fase 5 · Plantillas Astra — *5 a 7 días*
En el orden que marca la analítica: **Inicio**, **ficha de espacio cultural** (13 del top 25, 42% del tráfico), **ciclo o festival**, y después archivo de agenda con filtros, nota individual, convocatorias, búsqueda y 404. Más el **archivo de programaciones en PDF** de la Feria.

**Entregable:** todo lo que renderiza Astra, retematizado en staging.

### Fase 6 · Páginas Elementor — *4 a 7 días*
Reemplazo por lote de los colores literales que encontró el escaneo, más las páginas del top trabajadas a mano, incluida una pasada de marca sobre la Feria vigente.

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
Ventana de bajo tráfico, backup por plugin, subida del ZIP, activación, **vaciar el CSS adicional del Customizer** (guardado en `sitio-actual/` como respaldo) y desactivar el fragmento de `botonesCC.js`, *Regenerate CSS & Data* de Elementor, **purgar LiteSpeed Cache**, verificación de las 50 URLs del inventario, 48 h de monitoreo.

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
| Se desactiva UAE creyendo que sobra | Alto — el sitio queda sin header ni footer | Está marcado *¿es necesario?* en la lista de plugins, pero es `header-footer-elementor` con otro nombre. Dejarlo anotado en el inventario |
| CSS adicional y WPCode le ganan al child theme | Medio — se imprimen después del tema | Vaciarlos en la publicación, con el respaldo de `sitio-actual/`. Al limpiar los duplicados del CSS adicional, fusionar propiedad por propiedad: el bloque «muerto» del menú todavía fija el botón |
| LiteSpeed sirve CSS viejo | Bajo pero confunde en QA | Purgar después de cada subida; revisar si combina o minifica CSS |
| El menú no abre si LiteSpeed demora el JS | Alto — sin menú no se llega a ningún destino | El script del header espera `DOMContentLoaded`; el nuevo se inicializa sin depender del evento. Probar con la optimización de JS de LiteSpeed activa |
| La marca se vuelve a desalinear tras el lanzamiento | Bajo pero seguro | Global Color Palette de Astra + `theme.json` restringidos a cuatro colores, más la styleguide viva de la Fase 3 |

---

## 5. Decisiones pendientes

1. **Cuántas de las 510 están publicadas** y cuántas quedaron huérfanas. El top 25 por tráfico ya lo tenemos.
2. ~~**¿Se suma una agenda?**~~ — **decidido: no.** Ya existe en `agenda.santafeciudad.gov.ar`. Se conserva la estructura por espacios y se enlaza hacia afuera. Ver §2.quinquies.
3. ~~**¿Cuántas ediciones de Feria se conservan?**~~ — **resuelto: ninguna.** Ver §2.quater.
4. ~~**Modo oscuro**~~ — **decidido: no.** El sitio va en tema claro únicamente. El child theme no lleva bloques de `prefers-color-scheme` ni stamps de `data-theme`, y de paso evita el problema de que el rojo de Cultura da 4.30 sobre fondo azul profundo y no llega a AA.
5. ~~**Fotografía**~~ — **resuelto: hay banco propio.** Los derechos no son un problema. Con tres colores el peso visual recae en la foto, y es lo que le da al vidrio algo que esmerilar. Queda por definir dirección de arte: relaciones de aspecto por plantilla, tratamiento (¿fotos a sangre o dentro del vidrio?) y el flujo de conversión a WebP, que sin acceso al servidor tiene que resolverse con un plugin. LiteSpeed Cache, que ya está instalado, tiene optimización de imágenes con WebP: probarlo antes de sumar otro plugin.
7. **Plugins en duda.** El equipo marcó cinco con *¿es necesario?*. UAE se queda (arma header y footer). Breadcrumb NavXT, Contact Form 7 + reCaptcha, Post Type Switcher y Redux Framework se deciden con lo relevado en §2.sexies.
6. **Licencias tipográficas:** Geologica y Encode Sans son de Google Fonts (OFL), así que autoalojarlas está permitido. Confirmar igual con el área de comunicación.

---

## 6. Archivos de este repo

- `docs/design-tokens.css` — tokens de marca + material GlassMorph, listos para encolar
- `docs/barras.svg` — el remate de barras de esquina, partial para el child theme
- `docs/plan-glassmorph.md` — este documento
- `marca/logoFooter.svg` — la marca completa para fondo oscuro (bloque SF **calado** + panel del escudo), asset oficial sin modificar
- `marca/` — los dos PDFs normativos
- `sitio-actual/` — lo que hoy está cargado en el sitio, analizado en §2.sexies: `cssAdicionalCCAstra.css`, `botonesCC.js`, `header.html`, `footer.html`, `pluginsCC.txt` y `paginasUtilCC.docx`
