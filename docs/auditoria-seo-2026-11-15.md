# Auditoría SEO, AEO y GEO · 15 de noviembre de 2026

Relevamiento del sitio publicado (`/capitalcultural/`) y de los widgets de `sitio-nuevo/`. Se rastrearon las **179 páginas del sitemap de Yoast** (`page-sitemap.xml`; el de entradas tiene 85 más) con un script propio: estado HTTP, título, meta descripción, canonical, robots, encabezados, imágenes y JSON-LD.

**Puntaje: 74/100 en SEO técnico, 55/100 en preparación para IA.** La base es buena —Yoast está bien configurado y las páginas rehechas ya traen datos estructurados—; lo que falta es que los motores puedan leer *eventos*, *horarios* y *colecciones* como datos, y que el sitemap llegue a Google por un camino seguro.

## Lo que está bien

- **179 de 179 páginas responden 200**, son indexables (`index, follow, max-snippet:-1, max-image-preview:large`), tienen canonical propio e imagen para redes. Ninguna con `noindex` por error.
- **`lang="es-AR"`** en todas, viewport correcto, HTTPS con redirección 301 de `http` y de sin-`www`.
- **Los bots de IA entran.** GPTBot, OAI-SearchBot, ClaudeBot, PerplexityBot, Google-Extended y Googlebot reciben 200: ni el `robots.txt` ni Cloudflare los bloquean.
- **LiteSpeed Cache ya sirve el HTML** (`x-litespeed-cache: hit`). Lo que decía el README sobre que el HTML no se cacheaba quedó viejo.
- **Yoast genera en todas** `WebSite` (con buscador), `WebPage`, `BreadcrumbList` y `Organization` con logo y las tres redes en `sameAs`.
- **Los widgets nuevos** no tienen ninguna imagen sin `alt` ni ningún `h1` propio (el título lo pone Astra), y 26 páginas ya publican JSON-LD de lugar: `Museum`, `Library`, `ArtGallery`, `EventVenue`, `PerformingArtsTheater`, `FAQPage` en Somos Música y Teatro de Títeres.

## Crítico

1. **El sitemap de Capital Cultural no está declarado en ningún `robots.txt`.** El de la raíz (`santafeciudad.gov.ar/robots.txt`) es el del portal municipal y sólo anuncia `santafeciudad.gov.ar/sitemap_index.xml`, que no incluye `/capitalcultural/`. Y `/capitalcultural/robots.txt` no existe (WordPress en subcarpeta no lo sirve). Hoy Google descubre las páginas sólo siguiendo enlaces.
   **Arreglo:** dar de alta `https://www.santafeciudad.gov.ar/capitalcultural/sitemap_index.xml` en Google Search Console y en Bing Webmaster Tools (Bing alimenta a ChatGPT y Copilot), y pedirle a quien administra el portal que sume esta línea a su `robots.txt`:
   `Sitemap: https://www.santafeciudad.gov.ar/capitalcultural/sitemap_index.xml`
2. **Las propuestas no son eventos para ningún buscador.** En todo el sitio hay un solo `Event` (el aniversario del Mercado). Las propuestas de *Programación Cultural* —que tienen fecha, lugar y entrada gratuita en el texto, como *Cine bajo las estrellas*— salen como `WebPage` genérica. Es lo que más se pregunta a un buscador o a un asistente («¿qué hay este fin de semana en Santa Fe?») y lo que habilita los resultados enriquecidos de eventos.
   **Arreglo:** que el plugin *Programación Cultural* (uno de los dos a medida, sin código en este repo) imprima un `Event` por propuesta con `startDate`, `endDate`, `location` (el `@id` del espacio), `isAccessibleForFree`, `eventStatus` e `image`. Hay que hacerlo en el plugin, no a mano en 500 páginas.

## Importante

3. **Diez páginas tienen dos `h1`**: el título de Astra más un encabezado de Elementor también en `h1`. *Feria del Libro* y su *Programación*, *Noche de los Museos* (y sus dos de prensa), *Tochi*, *Salón Litoral 2022*, *Salón Anual Nacional 2025*, *Cine bajo las estrellas* y *Espectáculos infantiles*. Se arregla pasando el del widget a `h2` o, si es el título real, ocultando el de Astra en esa página.
4. **El título de *Noche de los Museos* se lee «NCHE DE LS MUSES»**: las «O» son imágenes dentro del encabezado. Para Google y para un lector de pantalla el `h2` dice eso. Hay que poner el texto completo en un `aria-label` o, mejor, sacar las letras-imagen del encabezado.
5. **`/nochedelosmuseos/prensa/` está en el sitemap pero redirige** a otra URL. Una URL del sitemap tiene que responder 200: o se saca del sitemap (*Yoast → Avanzado → no mostrar en resultados*) o se borra la redirección.
6. **La ficha de cada espacio no publica horarios, descripción ni imagen como datos.** El JSON-LD de las diez fichas tiene nombre, dirección, coordenadas y contacto, pero no `description`, `image` ni `openingHoursSpecification`, que son lo que un asistente usa para responder «¿a qué hora abre el Museo Sor Josefa?». **Implementado** (ver abajo).
7. **Las páginas que son colecciones no se declaran como tales.** *Museos y Salas*, *Huellas de mi Ciudad*, *Memorias Urbanas Feministas*, *Patrimonio Arquitectónico* y *Patrimonio Artístico* son listados de lugares, pero para un motor son texto suelto. **Implementado** con `ItemList`.
8. **El Centro Cultural Municipal y el Festival Diseña no tienen JSON-LD propio.** **Implementado.**
9. **Rendimiento: 20 hojas de estilo y 9 scripts bloqueantes** en la home (sólo uno con `defer`/`async`), más Google Fonts y Font Awesome que no se usan. Ya está anotado en el README (fuentes globales de Elementor, *Inline Font Icons*); a eso se suma activar en LiteSpeed Cache *Combinar CSS*, *Diferir JS* y *Carga diferida de imágenes*, probándolo primero en una página.

## Nice-to-have

10. **`llms.txt`**: `/capitalcultural/llms.txt` da 404. Yoast lo genera solo desde la versión 25.3: *Yoast SEO → Ajustes → Funciones del sitio → llms.txt*. Es un índice en texto plano que algunos asistentes leen para entender el sitio.
11. **`og:locale` dice `es_ES`** en todas las páginas, contra `lang="es-AR"`. Sale del idioma de WordPress (*Ajustes → Generales → Idioma del sitio*: pasarlo a *Español de Argentina*) o de un filtro `wpseo_locale` en el child theme.
12. **La descripción del sitio es «Secretaria de Cultura»**, sin tilde y sin decir qué es. Es el `description` del `WebSite` de Yoast y el lema de WordPress (*Ajustes → Generales → Descripción corta*). Propuesta: *Agenda, museos, salas y programas culturales de la Municipalidad de Santa Fe*.
13. **La `Organization` de Yoast se llama «Municipalidad de la ciudad de Santa Fe»** con el logo general. Para que los asistentes asocien «Capital Cultural» con la Secretaría de Cultura, conviene sumarle en *Yoast → Ajustes → Representación del sitio* el nombre alternativo «Secretaría de Cultura de Santa Fe».
14. **Veinte títulos pasan de 60 caracteres** (casi todos fichas de Patrimonio: «Casa de Diez de Andino, Museo Histórico Provincial…») y Google los corta. Trece son muy cortos («Visitanos», «Represión», «Cartoneros»). Se corrigen en el *Título SEO* de Yoast de cada una.
15. **Dos páginas sin meta descripción**: *+Feria / Visitanos* y la vieja de *José «Pepe» Serra*, que ya está anotada para redirigir.
16. **Treinta imágenes sin `alt`** en *+Feria* (16 en Participantes, 13 en FAQs, 1 en inicio).
17. **Doble salto de redirección**: `http://santafeciudad.gov.ar/…` → `https://santafeciudad.gov.ar/…` → `https://www.…`. Lo resuelve Cloudflare con una regla que mande directo a `https://www.`.

## AEO y GEO: cómo lo ve un asistente

La pregunta no es si el sitio aparece en Google, sino si ChatGPT, Gemini o Perplexity lo citan cuando alguien pregunta por cultura en Santa Fe. Lo que más pesa, en orden:

| Señal | Estado | Nota |
|---|---|---|
| Acceso de los bots de IA | ✅ | Todos reciben 200 |
| Datos de lugares (dirección, geo, contacto) | ✅ | 26 páginas |
| Horarios como datos | ⚠️ → ✅ | Faltaban en las fichas; ahora donde son fijos |
| Eventos como datos | ❌ | Ver punto 2: el hueco más grande |
| Preguntas y respuestas | ⚠️ | Sólo Somos Música y Títeres. Al ser sitio de gobierno, Google todavía muestra `FAQPage` como resultado enriquecido |
| Colecciones como listas | ❌ → ✅ | `ItemList` en los listados |
| Texto que responde solo | ✅ | Los *lead* de las fichas son definiciones de una oración («El Museo de la Ciudad investiga…»), el formato que mejor citan los asistentes |
| Entidad clara de la marca | ⚠️ | «Capital Cultural» no se vincula a «Secretaría de Cultura» (punto 13) |
| `llms.txt` | ❌ | Punto 10 |
| Presencia fuera del sitio | ? | No auditable desde acá. Wikipedia tiene artículos del Museo Sor Josefa, la Biblioteca Muttis y la Manzana Jesuítica: conviene que enlacen a la ficha nueva de cada uno |

**Contenido que conviene sumar** (no se escribió: requiere datos que sólo tiene la Secretaría):

- Una página **«Museos de Santa Fe: horarios y cómo visitarlos»** con una tabla de los diez espacios. Es la respuesta exacta a la pregunta más frecuente y el formato que los asistentes copian.
- **Preguntas frecuentes en la Noche de los Museos y en la Feria del Libro**: cuándo es, si es gratis, cómo moverse entre sedes. Son las dos páginas con más búsquedas estacionales.
- **Fechas en los títulos de las ediciones** (*Feria del Libro 2026*, *Noche de los Museos 2026*) para que la página no parezca vieja cuando se pregunta por la edición actual.

## Lo que se implementó en este repo

Todo es JSON-LD dentro de los widgets: no cambia nada visible. Para que llegue al sitio hay que volver a pegar cada widget.

| Archivo | Qué se sumó |
|---|---|
| `espacios/generar.py` → las 10 fichas | `description` (el lead), `image` (la imagen para redes cargada en Yoast), `openingHoursSpecification` donde el horario es fijo (Fotogalería y López Claro), `containedInPlace` para los que están dentro de otro edificio (Estación Belgrano, Teatro, Mercado Progreso, Centro Cultural) y `@id` estable para que otras páginas los referencien |
| `museos-y-salas.html` | `CollectionPage` con `ItemList` de los diez espacios, apuntando a esos `@id` |
| `centro-cultural-municipal.html` | `CivicStructure` con dirección y los dos espacios que contiene |
| `huellas-de-mi-ciudad.html` | `ItemList` de los tres recorridos |
| `memorias-urbanas-feministas.html` (13), `patrimonio-arquitectonico.html` (36), `patrimonio-artistico.html` (42) | `ItemList` de cada ficha del recorrido, con su URL. *Marcas de la Memoria* no lo lleva: tiene una sola marca |
| `festival-disena-inicio.html` | `EventSeries` del festival: desde 2008, en la Estación Belgrano, gratuito, organizado por la Secretaría de Cultura |
| `listas_jsonld.py` (nuevo) | Escribe los `ItemList` leyendo las tarjetas de cada archivo. Se corre después de sumar o sacar una tarjeta, así los datos no quedan distintos de lo que se ve |

**Cómo verificarlo después de pegar:** pasar cada URL por la [Prueba de resultados enriquecidos](https://search.google.com/test/rich-results) y el [validador de schema.org](https://validator.schema.org/). Ya se comprobó acá que los 22 bloques del repo son JSON válido y que los `@id` que se cruzan entre páginas existen.

Quedan fuera, porque se hacen en el panel de WordPress o en el plugin y no en este repo: los puntos 1 a 5 y 9 a 17.
