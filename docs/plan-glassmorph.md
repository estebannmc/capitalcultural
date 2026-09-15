# Capital Cultural · Plan de actualización visual (GlassMorph + marca SF 2026)

**Sitio:** https://www.santafeciudad.gov.ar/capitalcultural/
**Fuentes normativas:** `SF_Brandbook_V2_light_compressed-1_compressed-1.pdf` (V1.0 · Febrero 2026, 67 pág.) y `SF_paleta_cromática.pdf` (ficha 02.4)
**Enfoque acordado:** retematizado de WordPress (child theme sobre **Astra 4.13.1**), sin migrar plataforma
**Sistema cromático:** cerrado en cuatro — Azul 01, Verde 01, rojo de submarca y neutros
**Restricción de entrega:** hay acceso al admin de WP pero **no al servidor** → todo se instala por ZIP desde el panel, sin build, sin WP-CLI, sin SSH

---

## 0. Punto de partida y supuestos

| Ítem | Estado |
|---|---|
| Plataforma | **WordPress con Astra 4.13.1** (Brainstorm Force), en subcarpeta `/capitalcultural/`. Confirmado desde el admin. Astra es un tema, no un page builder: el mejor escenario posible |
| Plugins | **No relevados.** Es lo único que todavía puede mover la estimación: Astra es el tema más emparejado con Elementor, y Brainstorm Force además empuja Spectra |
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
- la barra del logo reusada como marcador de sección, viñeta e indicador de estado (`.sf-barra`, con `skewX(-15deg)`): la geometría operando como código visual, en vez de puntos redondos genéricos;
- el barrido de luz del hero (`transform: skewX(-15deg)`), el mismo que ya usa Freedom y que venía con el ángulo correcto.

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

## 2.bis Trabajar sobre Astra 4.13.1

Astra **no es un page builder**, y eso es lo mejor que podía pasar: el plan se sostiene tal cual. Cuatro cosas concretas que cambian la implementación:

1. **Orden de carga.** Astra arma CSS dinámico desde el Customizer y lo inyecta *inline* con `wp_add_inline_style()` sobre el handle `astra-theme-css`. Una hoja del child theme encolada sin dependencia declarada puede imprimirse antes y perder. La hoja va encolada **con `astra-theme-css` como dependencia**, y lo que necesite ganar sí o sí se inyecta por el filtro `astra_dynamic_theme_css`, que corre después de todo lo de Astra.
2. **La paleta vive en el Customizer.** Astra guarda su *Global Color Palette* como opción y la emite en todo el sitio. Si no se cambia ahí, sigue pintando con sus defaults y el child theme queda peleando especificidad para siempre. Los cuatro colores SF se cargan en los slots de Astra **primero**; recién después se estiliza.
3. **Header y footer.** Astra 4 trae Header Footer Builder en el Customizer. Conviene **estilar su salida**, no reemplazarla por hooks: el equipo de Cultura sigue editando menús desde donde ya sabe. El vidrio entra como `.glass-bar` sobre el contenedor de Astra.
4. **Capas de fondo.** El fondo vivo y la trama se inyectan por el hook `astra_body_top`, sin tocar plantillas. Base del child theme: el `astra-child` oficial de Brainstorm Force, que ya resuelve el encolado.

Queda por confirmar si hay **Astra Pro** y, sobre todo, **qué plugins acompañan** — Elementor o Spectra son la única variable que todavía mueve la estimación.

---

## 3. Fases

### Fase 1 · Relevamiento y setup — *2 a 3 días*
1. Inventario desde el admin: el tema ya lo sabemos, falta si hay child theme y Astra Pro, los plugins (especialmente el de agenda/eventos y si hay Elementor o Spectra), tipos de contenido y taxonomías, plantillas en uso.
2. Exportar el árbol de URLs y quedarse con el top 50 por tráfico (Analytics/Search Console) — define qué plantillas se rediseñan primero.
3. Baseline: Lighthouse móvil, axe DevTools y capturas del estado actual. Sin esto no hay forma de demostrar la mejora.
4. Entorno de prueba. Sin acceso al servidor, la opción realista es un **staging local** (LocalWP / Studio) con una copia del sitio exportada por plugin de migración, más el uso de `?preview_theme=` en producción para la validación final. **[R]**
5. Extraer del Brandbook los SVG del lockup, el escudo y el set de iconos (02.5).

**Entregables:** planilla de inventario, baseline de métricas, repo con el staging y los assets.

### Fase 2 · Fundaciones del child theme — *3 a 4 días*
1. Child theme `capitalcultural-sf` sobre el `astra-child` oficial, con encolado declarando `astra-theme-css` como dependencia: tokens → componentes → plantillas.
2. Incorporar `docs/design-tokens.css` y autoalojar Geologica y Encode Sans.
3. **Global Color Palette de Astra** cargada con los cuatro colores SF, más `theme.json` con esa misma paleta y la escala tipográfica, para que el editor de bloques ofrezca **sólo** esos colores. Es lo que evita que el sitio se desalinee de nuevo en tres meses.
4. Capas de fondo (`.sf-ambient` + `.sf-trama`) inyectadas por el hook `astra_body_top`.
5. Estilos base: tipografía, links, foco visible, `skip-link`.

**Entregable:** ZIP instalable del child theme, con el sitio ya en paleta correcta aunque sin componentes nuevos.

### Fase 3 · Biblioteca de componentes — *5 a 7 días*
Header glass sticky · menú mobile (el patrón `.glass-bar::before` de Freedom, que existe justamente para que el menú desplegable pueda esmerilar el contenido) · hero con barrido a 15° · tarjeta de evento/agenda · tarjeta de nota · filtros y chips · paginación · formulario de convocatorias · footer con marca completa.

Cada componente se entrega con estado *hover*, *focus-visible*, vacío y de carga. Se documentan en una página oculta del propio WP que funcione como styleguide viva.

**Entregable:** biblioteca completa + styleguide.

### Fase 4 · Plantillas — *5 a 7 días*
Home · archivo de agenda con filtros · evento individual · categoría/tag · nota individual · página genérica (Feria del Libro y similares) · convocatorias · resultados de búsqueda · 404.

Orden: primero las que concentran tráfico según la Fase 1.

**Entregable:** sitio completo retematizado en staging.

### Fase 5 · Accesibilidad, performance y QA — *3 a 4 días*
1. Auditoría AA: contraste, navegación por teclado, landmarks, `alt`, foco en el menú mobile.
2. Verificar los tres modos degradados del vidrio (sin `backdrop-filter`, transparencia reducida, movimiento reducido).
3. Lighthouse móvil contra el baseline. CLS es el riesgo típico con fuentes autoalojadas y blur.
4. Matriz: Chrome, Firefox, Safari (iOS incluido — es donde `backdrop-filter` más varía), Edge, y un Android de gama baja real.
5. Repaso editorial: que el tono siga el "pulso / impulso" del Brandbook, en minúsculas y con barras.

**Entregable:** informe antes/después con métricas.

### Fase 6 · Publicación — *1 a 2 días*
Ventana de bajo tráfico, backup por plugin, subida del ZIP, activación, verificación de las 50 URLs del inventario, 48 h de monitoreo.

**Total estimado: 19 a 27 días hábiles**, asumiendo que el relevamiento no encuentre Elementor de por medio.

---

## 4. Riesgos

| Riesgo | Impacto | Mitigación |
|---|---|---|
| Elementor o Spectra sobre Astra | Alto — los estilos de cada página viven en la base, no en el tema | Detectar en Fase 1. Si aparece Elementor, hay que trabajar con sus globals y la estimación sube ~40%. Spectra es más liviano pero trae sus propios estilos de bloque |
| El CSS inline de Astra gana | Medio — aparecen colores viejos en lugares sueltos | Encolar con `astra-theme-css` como dependencia y usar el filtro `astra_dynamic_theme_css`; verificar plantilla por plantilla |
| Sin acceso al servidor | Medio | Todo por ZIP desde el admin; staging local; `?preview_theme=` para validar en producción |
| `backdrop-filter` en Android de gama baja | Medio | Presupuesto de ~8 blurs simultáneos, `glass-soft` en grillas, prueba en dispositivo real |
| El rojo no da AA en ningún combo | Alto — es el color identitario y su mejor contraste es 4.33 | Restringido a texto grande y grafismos. Con la paleta reducida pesa más, porque ya no hay Azul 02 para descargarle trabajo |
| Plugin de agenda con markup propio | Medio | Sobrescribir plantillas del plugin desde el child theme, nunca editar el plugin |
| La marca se vuelve a desalinear tras el lanzamiento | Bajo pero seguro | Global Color Palette de Astra + `theme.json` restringidos a cuatro colores, más la styleguide viva de la Fase 3 |

---

## 5. Decisiones pendientes

1. **¿Qué plugins acompañan a Astra?** Concretamente: ¿hay Elementor o Spectra? Es lo único que todavía puede invalidar la estimación. Una captura de la lista de plugins alcanza.
2. **Alcance de `/feriadellibro/`** y demás subsitios: ¿entran en esta tanda o quedan para una segunda?
3. **Modo oscuro:** el Brandbook no lo contempla. Sugiero no hacerlo ahora. Si se hace, el rojo tiene que aclararse a `#ff7f63` sobre fondo azul profundo — `#e63312` ahí da 4.30 y no llega.
4. **Fotografía:** el vidrio necesita imágenes debajo para lucir, y con tres colores el peso visual recae más en la fotografía. ¿Hay banco de fotos de eventos con derechos resueltos?
5. **Licencias tipográficas:** Geologica y Encode Sans son de Google Fonts (OFL), así que autoalojarlas está permitido. Confirmar igual con el área de comunicación.

---

## 6. Archivos de este repo

- `docs/design-tokens.css` — tokens de marca + material GlassMorph, listos para encolar
- `docs/plan-glassmorph.md` — este documento
- Los dos PDFs normativos en la raíz
