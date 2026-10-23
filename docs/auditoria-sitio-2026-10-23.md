# Auditoría del sitio publicado · 23 de octubre de 2026

Contra los criterios de `sistema-visual.html`. Se recorrieron en **Edge 155** las **593 URLs** del sitemap de Yoast (508 páginas + 85 entradas), todas con 200, a 1366 px. Las 21 páginas del top de tráfico se repitieron a 390 px (mobile). En cada una corrió `docs/auditar-en-el-navegador.js` más chequeos de los criterios que ese script no mide: rojo chico, blanco sobre celeste, links, botones, Google Fonts, blur y degradés. El foco se probó con Tab en la home.

Los contrastes se midieron sobre fondo sólido. Hay 798 textos sobre foto o degradé (en 458 páginas) a los que no se les puede dar un número, y quedan para revisar a ojo.

## Lo que está bien

- **Ningún botón rojo chico ni texto blanco sobre naranja.** El rojo aparece como barra y filete, salvo en el caso de Festival Diseña (ver abajo).
- **El foco cumple en la home:** anillo rojo de 3px en el header, el slider y los CTA. Los `.cta-capital` usan exactamente el anillo del sistema (`0 0 0 2px #fff, 0 0 0 5px #e63312`). La única excepción es el skip-link, que lleva el punteado azul de Astra.
- **Todas las páginas tienen `lang="es-AR"` y `<title>`.** Ninguna tiene scroll horizontal a 390 ni a 1366, y ninguna declara modo oscuro.
- **No hay fuentes fuera de marca** salvo en 2 páginas. Geologica sólo aparece en el lockup del header.
- **No queda `#00aaff` en ninguna página.**

## Hallazgos, por alcance

### En todo el sitio (se arregla una vez)

| # | Criterio | Hallazgo | Dónde se arregla |
|---|---|---|---|
| 1 | Autoalojadas, sin llamada a Google | Las 593 páginas cargan Encode Sans desde `fonts.googleapis.com`: lo encolan Astra y Elementor | Astra → Personalizar → Rendimiento → *Cargar fuentes de Google localmente*; Elementor → Ajustes → Avanzado → *Google Fonts: cargar localmente* |
| 2 | Contraste AA | En las migas, el ítem actual (`#6b7280`) da **4.39** sobre el fondo y el separador `/` (`#9ca3af`) da **2.31**. Pasa en 592 páginas. Son grises de Tailwind, fuera de la paleta | **Arreglado en el repo:** Gris 75 `#606060`, que da 5.71. Falta pegar el CSS adicional en Astra |
| 3 | Global Colors restringidos | La paleta global de Astra tiene 5 colores fuera de sistema: `#1e293b`, `#334155`, `#F0F5FA`, `#111111` y `#D1D5DB`, el starter de Astra. De ahí sale el texto `#1e293b` (129 páginas) y `#334155` (60) | Astra → Personalizar → Global → Colores |
| 4 | Jerarquía de títulos | El footer salta de `h2` a `h4` en las 593 páginas | **Arreglado en el repo:** pasan a `h3` en `footer.html` y en el CSS adicional, con el mismo aspecto. Falta pegar los dos en WordPress |
| 5 | La barra pegada al título | El `h1.cc-page-title` de las páginas internas no lleva la barra roja delante. Sólo la tienen los slides de la home | CSS del título de página |
| 6 | La luz del footer y el remate de esquina | El footer es Azul 01 liso: no tiene listones a 105° ni foco celeste. En ninguna página aparece el remate de ocho barras ni la trama | Pendiente de las fases 3 y 4 |
| 7 | Objetivos táctiles | Los links del footer miden 16 px de alto con 20 px de paso | CSS del footer |

### Colores elegidos a mano en widgets de Elementor (el lote de la fase 2)

Con la regla `a { color: Azul 01 }` de Astra todo esto debería heredar bien; no hereda porque son colores literales guardados en `_elementor_data`.

- **Texto y links en celeste**: dan 2.23 sobre blanco y 2.02 sobre el fondo, y el sistema dice *nunca*. Pasa en **116 páginas**. De los links en texto, 234 no llevan subrayado y 213 de esos son celestes. Son sobre todo fichas de artesanos de +Feria, Música en vivo, Museos y Salas, Masferia y Festival Diseña.
- **Botón verde Bootstrap `#5cb85c`** con texto blanco a **2.48** en **43 páginas**: los botones de aporte (*$ 100 / $ 250 / $ 500*) de artes escénicas, expresiones, música en vivo, palabras y producciones independientes.
- **Blanco sobre celeste**, el mismo botón de aporte: **2.23** en **25 páginas**: `/himnos/`, `/musica-en-vivo/*` (23) y `/usinadepensamiento/pensar-el-diseno/`.
- **Verde abandonado `#00c08b`** en **8 páginas**: `/archivoimagen/100-anos-100-imagenes/`, `/archivoimagen/homenaje-a-zapata-gollan/`, `/bibliotecamuttis/cursosytalleres/`, `/mercadoprogreso/musicamercado/`, `/mercadoprogreso/teatromercado25/`, `/museos-y-salas/fotogaleria/talleres/`, `/museos-y-salas/sala-mercado-editorial/muestras/` y `/vacacionesdeinvierno/`.
- **Rojo en texto chico** en las 4 páginas de `/festivaldisena/`: el menú activo a 14 px (4.33), los `summary` a 12 px y *Ver CV* a 13 px.
- **Otros contrastes puntuales**:
  - en `/musica-en-vivo/concurso-de-musica/`, blanco sobre `#ffe459` (1.27) y sobre `#d37dbd` (2.82);
  - en las dos páginas de `/artes-visuales/salon-primavera/`, un `h2` blanco sobre el fondo claro (1.1) y verde `#12bc8e` (2.22);
  - en `/feria-del-sol-y-de-la-luna/`, títulos que dan 1.0;
  - en `/himnos/` y `/museos-y-salas/ariel-ramirez/ariel-va-a-la-escuela/`, créditos en `#cccccc` a 10 px (1.46).

### Componentes puntuales

- **Home, slider "Propuestas activas"**: las flechas `‹ ›` son glifos blancos sobre botón blanco (1.0), así que se ven como dos cuadrados vacíos. Además miden 34 px de alto, por debajo de 44. En las tarjetas, el kicker (*CONVOCATORIA*, *MUESTRA*) y el separador `·` van en `#8a9298`, que da **3.16**: es el gris inventado que el sistema ya reemplazó por Gris 75. Pasa en 8 páginas que usan el mismo slider. El plugin no está en el repo.
- **Home**: no tiene `h1`. Los puntos del slider del hero miden 4 px de alto.
- **Huellas de mi Ciudad (`sitio-nuevo/`)**: cada `.pa-card` lleva `backdrop-filter` real. Patrimonio artístico suma **43** elementos con blur, arquitectónico 37, Memorias feministas 14 y Homenaje a diez maestros 10. El presupuesto es de unos 8, y las grillas deberían usar `glass-soft`, sin blur. **Arreglado en el repo para las dos grillas de Patrimonio:** artístico baja de 43 a 2 y arquitectónico de 38 a 2. Los dos que quedan son el header y el menú. Quedan con blur Memorias feministas (14) y la ficha de Homenaje a diez maestros (10).
- **Noche de los Museos** (`/nochedelosmuseos/*`, `/ndlm26/`): usa una identidad propia en rosa `#ec008c` sobre `#203a66`, fuera de la paleta. El lockup *N CHE* da 2.66 a 62 px y los botones rosas con texto blanco a 11–12 px dan 4.25. Hay que decidir si el ciclo tiene excepción de marca.
- **+Feria y Diseña**: el menú activo usa `#e6007e`, que da 4.09.
- **Mercado Progreso** (`/mercado-progreso/`): el hero queda corrido a la izquierda, con 20 px de margen a un lado y unos 180 px al otro, y el texto corrido no tiene ancho máximo.
- **Más de un `h1`** en 31 páginas: las de Noche de los Museos, Feria del Libro y las del plugin de eventos.
- **Imágenes sin `alt`**: 53 en total, repartidas así: `/somosmusica/` 20, `/masferia/participantes/` 16, `/masferia/faqs/` 13, `/mercadoprogreso/festivaltrotamundos/` 3 y `/masferia/` 1.
- **Fuentes fuera de marca**: Nunito en `/artes-visuales/mmav/` y Alegreya Sans en `/feriadellibro/`.

### Para el triage de la fase 1

El sitemap publica páginas internas del plugin de eventos: `/event-dashboard/`, `/events/my-bookings/`, `/organizer-dashboard/`, `/post-an-event/`, `/submit-venue-form/`, `/submit-organizer-form/` y `/venue-dashboard/`. No deberían estar indexadas.
