# Capital Cultural · Plan de actualización visual (GlassMorph + marca SF 2026)

**Sitio:** https://www.santafeciudad.gov.ar/capitalcultural/
**Fuentes normativas:** `SF_Brandbook_V2_light_compressed-1_compressed-1.pdf` (V1.0 · Febrero 2026, 67 pág.) y `SF_paleta_cromática.pdf` (ficha 02.4)
**Enfoque acordado:** retematizado de WordPress (child theme), sin migrar plataforma
**Restricción de entrega:** hay acceso al admin de WP pero **no al servidor** → todo se instala por ZIP desde el panel, sin build, sin WP-CLI, sin SSH

---

## 0. Punto de partida y supuestos

| Ítem | Estado |
|---|---|
| Plataforma | WordPress en subcarpeta `/capitalcultural/` — se infiere de `/tag/…` y de páginas hijas tipo `/feriadellibro/programacion/` |
| Código del tema actual | **No relevado.** El dominio está bloqueado desde este entorno, así que el inventario de plantillas/plugins es la primera tarea real |
| Marca aplicable | Capital Cultural es un **tópico de nivel 01/A** del sistema SF: logo síntesis `SF` + palabra clave y color propio (Brandbook 04.1, pág. 48–51) |
| Color del tópico | **Rojo `#e63312`** — verificado por muestreo de píxeles sobre el lockup `SF / CULTURA` de la pág. 51 |

Todo lo que dependa del relevamiento está marcado como **[R]** y se confirma en la Fase 1.

---

## 1. Sistema de diseño

### 1.1 Paleta (ficha 02.4, valores textuales del PDF)

**Base**

| Token | Hex | RGB | Pantone |
|---|---|---|---|
| Santa Fe Azul 01 | `#002751` | 0 39 81 | 540 |
| Santa Fe Azul 02 | `#1639f1` | 22 57 241 | 286 |
| Santa Fe Verde 01 | `#00c08b` | 0 192 139 | 3405 |
| Santa Fe Verde 02 | `#00fe9b` | 0 254 155 | — |
| Santa Fe Blanco | `#ffffff` | 255 255 255 | — |

**Neutros:** `#dadad9` · `#606060` · `#1d1d1b`
**Acentos:** celeste `#57b8e9` · naranja `#fca100` · **rojo `#e63312` (Cultura)** · amarillo `#f2e61a`

### 1.2 Regla de contraste (medida, WCAG 2.1 sobre blanco)

```
#002751  14.90  AAA      texto libre
#1d1d1b  16.88  AAA      texto libre
#1639f1   7.27  AAA      links
#606060   6.29  AA       texto secundario
#e63312   4.33  AA-large sólo ≥24px, o ≥19px bold
#00c08b   2.36  FALLA    nunca como texto
#57b8e9   2.23  FALLA    nunca como texto
#fca100   2.05  FALLA    nunca como texto
#00fe9b   1.34  FALLA    nunca como texto
#f2e61a   1.30  FALLA    nunca como texto
```

**Regla operativa:** los verdes y los acentos son colores de **fondo**; el texto que va encima es Azul 01 (`#002751` sobre verde-02 da 11.11, sobre verde-01 6.33, sobre amarillo 11.43, sobre naranja 7.27, sobre celeste 6.70). El rojo de Cultura se usa en titulares grandes, filetes, bordes y fondos con texto blanco grande — **no** en texto corrido.

Esto importa además porque es un sitio de gobierno: le aplica la Ley 26.653 de accesibilidad web y la ODA 1.0 de ONTI, que toman WCAG 2.0 AA como piso.

### 1.3 Tipografía (02.1 / 02.2)

- **Geologica** — tipografía de marca, **exclusiva de signos de identidad** (el lockup `SF / CULTURA` y poco más). El Brandbook es explícito en esto.
- **Encode Sans** — tipografía de comunicación: todos los titulares, bajadas y texto corrido.

Ambas están en Google Fonts. Por el bloqueo de servidor se autoalojan como archivos del child theme (`/fonts/*.woff2` + `@font-face` con `font-display: swap`), lo que además evita la dependencia externa que suele objetar el área de sistemas del municipio.

### 1.4 Trama de marca (02.6)

La identidad se construye sobre diagonales a **15°**, derivadas del paralelogramo del logo. En web se traduce a:

- `repeating-linear-gradient` a 15° como capa de fondo al 12% de opacidad;
- esquinas y separadores en diagonal (`clip-path`) en los cortes de sección;
- el barrido de luz del hero (`transform: skewX(-15deg)`), el mismo que ya usa Freedom.

### 1.5 Material GlassMorph

Se porta el sistema ya probado en `tiendaFreedom` y `rifa-silvana` **con los mismos nombres de token** — `--glass-fill`, `--glass-blur`, `--glass-shadow`, `--noise`, `--ease-out` — y las mismas clases: `.glass`, `.glass--thin`, `.glass--thick`, `.glass--dark`, `.glass-soft`, `.glass-bar`, más el fondo vivo `.ambient` con blobs a la deriva.

Cambios respecto de Freedom/Rifa:

1. Las sombras se tiñen con Azul 01 en vez de gris frío.
2. Los blobs del fondo pasan a verde-01, verde-02, azul-02 y un toque del rojo de Cultura.
3. Se agregan dos tintes de marca: `.glass--verde` y `.glass--topico`.
4. Se mantienen **intactos** los tres bloques de accesibilidad: `@supports not (backdrop-filter)`, `prefers-reduced-transparency` y `prefers-reduced-motion`.

El archivo está listo en **`docs/design-tokens.css`** (237 líneas) — es el primer stylesheet que encola el child theme.

**Presupuesto de rendimiento:** `backdrop-filter` es caro. Regla: como máximo ~8 elementos con blur real visibles a la vez. La grilla de agenda usa `glass-soft` (translúcido sin blur); el blur real queda para header, hero, modales y tarjetas destacadas. Es exactamente el criterio que ya está comentado en `globals.css` de Freedom.

---

## 2. Arquitectura de marca aplicada

Capital Cultural es **nivel 01/A** del sistema: logo síntesis + tópico + color.

- **Header:** lockup `SF / CULTURA` con el filete rojo, enlazado a `santafeciudad.gov.ar`.
- **Nombre del sitio:** "Capital Cultural" en Encode Sans, no en Geologica (no es un signo de identidad).
- **Footer:** marca completa con Escudo Municipal + bajada "Municipalidad de Santa Fe".
- **Redes:** el Brandbook ya fija el handle `cultura.santafecapital` (pág. 51); conviene que el footer lo respete.

---

## 3. Fases

### Fase 1 · Relevamiento y setup — *2 a 3 días*
1. Inventario desde el admin: tema activo y si ya hay child theme, plugins (especialmente el de agenda/eventos), tipos de contenido y taxonomías, plantillas en uso.
2. Exportar el árbol de URLs y quedarse con el top 50 por tráfico (Analytics/Search Console) — define qué plantillas se rediseñan primero.
3. Baseline: Lighthouse móvil, axe DevTools y capturas del estado actual. Sin esto no hay forma de demostrar la mejora.
4. Entorno de prueba. Sin acceso al servidor, la opción realista es un **staging local** (LocalWP / Studio) con una copia del sitio exportada por plugin de migración, más el uso de `?preview_theme=` en producción para la validación final. **[R]**
5. Extraer del Brandbook los SVG del lockup, el escudo y el set de iconos (02.5).

**Entregables:** planilla de inventario, baseline de métricas, repo con el staging y los assets.

### Fase 2 · Fundaciones del child theme — *3 a 4 días*
1. Child theme `capitalcultural-sf` con `style.css`, `functions.php` y encolado ordenado: tokens → componentes → plantillas.
2. Incorporar `docs/design-tokens.css` y autoalojar Geologica y Encode Sans.
3. `theme.json` con la paleta y la escala tipográfica, para que el editor de bloques ofrezca **sólo** los colores de marca. Es lo que evita que el sitio se desalinee de nuevo en tres meses.
4. Capas de fondo (`.sf-ambient` + `.sf-trama`) inyectadas por `wp_body_open`.
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

**Total estimado: 19 a 27 días hábiles**, asumiendo que el relevamiento no encuentre un page builder de por medio.

---

## 4. Riesgos

| Riesgo | Impacto | Mitigación |
|---|---|---|
| El sitio usa un page builder (Elementor/WPBakery) | Alto — los estilos se guardan en la base, no en el tema | Detectar en Fase 1. Si aparece, el plan cambia: hay que trabajar con los globals del builder y la estimación sube ~40% |
| Sin acceso al servidor | Medio | Todo por ZIP desde el admin; staging local; `?preview_theme=` para validar en producción |
| `backdrop-filter` en Android de gama baja | Medio | Presupuesto de ~8 blurs simultáneos, `glass-soft` en grillas, prueba en dispositivo real |
| Contraste del rojo de Cultura | Medio — es el color identitario y falla en texto chico | Regla ya fijada en §1.2 y codificada en los tokens |
| Plugin de agenda con markup propio | Medio | Sobrescribir plantillas del plugin desde el child theme, nunca editar el plugin |
| La marca se vuelve a desalinear tras el lanzamiento | Bajo pero seguro | `theme.json` restringido + styleguide viva de la Fase 3 |

---

## 5. Decisiones pendientes

1. **¿Page builder?** Es lo único que puede invalidar la estimación. Se responde el primer día.
2. **Alcance de `/feriadellibro/`** y demás subsitios: ¿entran en esta tanda o quedan para una segunda?
3. **Modo oscuro:** el Brandbook no lo contempla. Sugiero no hacerlo ahora; el vidrio oscuro ya está resuelto vía `.glass--dark` para heros y footer.
4. **Fotografía:** el vidrio necesita imágenes debajo para lucir. ¿Hay banco de fotos de eventos con derechos resueltos?
5. **Licencias tipográficas:** Geologica y Encode Sans son de Google Fonts (OFL), así que autoalojarlas está permitido. Confirmar igual con el área de comunicación.

---

## 6. Archivos de este repo

- `docs/design-tokens.css` — tokens de marca + material GlassMorph, listos para encolar
- `docs/plan-glassmorph.md` — este documento
- Los dos PDFs normativos en la raíz
