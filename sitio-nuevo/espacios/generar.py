"""Genera la ficha de cada espacio de Museos y Salas: un widget HTML entero por página.

Uso: python sitio-nuevo/espacios/generar.py  (escribe <slug>.html en esta carpeta)
Los textos, horarios y contactos viven en ESPACIOS; la plantilla es la misma para los diez.
"""
import html
import json
import os

AQUI = os.path.dirname(os.path.abspath(__file__))
SITIO = 'https://www.santafeciudad.gov.ar/capitalcultural'

ESPACIOS = [
    {
        'slug': 'cec', 'pagina': 20428, 'tipo': 'ArtGallery',
        'nombre': 'Centro Experimental del Color', 'corto': 'el CEC',
        'lead': 'El Centro Experimental del Color (CEC) es un espacio de formación, producción y exhibición de artes visuales en el ala oeste de la Estación Belgrano.',
        'cuerpo': ['Fue creado en 2009 y desde 2011 funciona en la estación recuperada como una plataforma para experimentar y explorar prácticas artísticas contemporáneas. Allí se desarrollan proyectos expositivos, actividades formativas y programaciones vinculadas a otras disciplinas.'],
        'horarios': [('Mañana', 'Miércoles a viernes de 9 a 13 horas.'),
                     ('Tarde, de abril a octubre', 'Miércoles a domingos y feriados de 16 a 19 horas.'),
                     ('Tarde, de noviembre a marzo', 'Miércoles a domingos y feriados de 17 a 20 horas.')],
        'direccion': 'Bv. Gálvez 1150, Estación Belgrano, ala oeste', 'calle': 'Bv. Gálvez 1150',
        'lat': -31.638728, 'lng': -60.686618,
        'grupos': 'De lunes a viernes de 9 a 13 horas, por WhatsApp o por correo.',
        'whatsapp': '5493425118392', 'telefono_txt': '+54 9 342 511-8392',
        'correo': 'centroexperimentaldelcolor@santafeciudad.gov.ar',
        'shortcode': '[cec]', 'propuestas': 'Propuestas del CEC',
    },
    {
        'slug': 'fotogaleria', 'pagina': 24139, 'tipo': 'ArtGallery',
        'nombre': 'Fotogalería Municipal', 'corto': 'la Fotogalería',
        'lead': 'La Fotogalería Municipal es un espacio de exposición, formación e investigación de la fotografía contemporánea, en diálogo con las artes visuales.',
        'cuerpo': ['Nació a principios de los años noventa para dar lugar a la circulación y el encuentro de prácticas fotográficas de búsqueda autoral. Forma parte del <a href="/capitalcultural/centroculturalmunicipal/">Centro Cultural Municipal</a>, junto a la <a href="/capitalcultural/centroculturalmunicipal/bibliotecamuttis/">Biblioteca Profesor Enrique Muttis</a>.'],
        'horarios': [('Horario', 'Lunes a viernes de 8 a 20 horas.')],
        'direccion': 'Primera Junta 2451', 'calle': 'Primera Junta 2451',
        'lat': -31.64855, 'lng': -60.70560,
        'grupos': 'Pedí turno por correo.',
        'correo': 'fotogaleriamunicipal@gmail.com',
        'shortcode': '[fotogaleria]', 'propuestas': 'Propuestas de la Fotogalería',
    },
    {
        'slug': 'manzana-jesuitica', 'pagina': 23414, 'tipo': 'Museum',
        'nombre': 'Manzana Jesuítica y Museo Colegio Inmaculada', 'corto': 'la Manzana Jesuítica',
        'lead': 'El Museo del Colegio de la Inmaculada Concepción cuenta la obra evangelizadora y educativa de la Compañía de Jesús en Santa Fe y la región.',
        'cuerpo': ['Se inauguró en 1982 en la Manzana Jesuítica, en el casco fundacional de la ciudad. Quienes recorren el Observatorio y las salas de exposición descubren un patrimonio que sobresale por su riqueza histórica y su diversidad material.'],
        'horarios': [('De marzo a noviembre', 'Miércoles a viernes de 9 a 12:30 horas y de 15:30 a 19 horas. Sábados de 15:30 a 19 horas. Feriados de 16 a 19 horas.'),
                     ('De diciembre a febrero', 'Miércoles a viernes de 9 a 12:30 horas y de 16 a 20 horas. Sábados y feriados de 17 a 20 horas.'),
                     ('Visitas guiadas', 'De marzo a noviembre, miércoles a viernes a las 9:30 y a las 11 horas, y sábados a las 15:30 y a las 17 horas. De diciembre a febrero, miércoles a viernes a las 9 y a las 10:30 horas, y sábados a las 17 y a las 18:30 horas. Cupo de 30 personas por visita.')],
        'direccion': 'San Martín 1540', 'calle': 'San Martín 1540',
        'lat': -31.657651, 'lng': -60.709526,
        'grupos': 'Pedí turno por correo.',
        'correo': 'museo.manzanajesuitica@santafeciudad.gov.ar',
        'shortcode': '[museoinmaculada]', 'propuestas': 'Propuestas del Museo',
    },
    {
        'slug': 'museo-de-la-ciudad', 'pagina': 20091, 'tipo': 'Museum',
        'nombre': 'Museo de la Ciudad', 'corto': 'el Museo de la Ciudad',
        'lead': 'El Museo de la Ciudad investiga, colecciona, conserva y exhibe las historias cotidianas de Santa Fe y de quienes la habitan.',
        'cuerpo': ['Creado en 1972, trabaja sobre las prácticas culturales, las identidades y las memorias de la ciudad a través de proyectos colaborativos. Rescata y preserva objetos de la vida cotidiana: instrumentos, mobiliario, utensilios, herramientas, indumentaria, juegos y juguetes, fotografías, libros y documentos.',
                   'Hoy no tiene sala propia: presenta su patrimonio en distintos espacios de la ciudad. Cada propuesta indica dónde y cuándo se puede visitar.'],
        'horarios': [('Horario', 'Según cada muestra.'),
                     ('Visitas guiadas', 'Cupo de 30 personas por visita.')],
        # Itinerante: no tiene dirección propia, así que va sin dirección ni mapa.
        'grupos': 'Pedí turno por correo.',
        'correo': 'museo.ciudad@santafeciudad.gov.ar',
        'shortcode': '[museodelaciudad]', 'propuestas': 'Propuestas del Museo',
    },
    {
        'slug': 'museo-de-la-constitucion-nacional', 'pagina': 22929, 'tipo': 'Museum',
        'nombre': 'Museo de la Constitución Nacional', 'corto': 'el Museo de la Constitución',
        'lead': 'El Museo de la Constitución Nacional difunde los principios y valores de la Carta Magna con un recorrido por la historia constitucional argentina.',
        'cuerpo': ['Se inauguró el 10 de diciembre de 2018 en el Parque-Biblioteca, frente a la costa. Refleja la identidad de Santa Fe como Ciudad de la Constitución y promueve los derechos de todas las personas.'],
        'horarios': [('Martes a viernes', 'De marzo a noviembre, de 8:30 a 12:30 horas y de 14 a 18 horas. De diciembre a febrero, de 8:30 a 12:30 horas y de 16 a 20 horas.'),
                     ('Sábados', 'De marzo a agosto, de 14 a 18 horas. De septiembre a febrero, de 16 a 20 horas.'),
                     ('Feriados', 'De marzo a agosto, de 15 a 18 horas. De septiembre a febrero, de 17 a 20 horas.'),
                     ('Domingos y lunes', 'Cerrado.'),
                     ('Visitas mediadas', 'Duran una hora. Cupo de 30 personas por visita.')],
        'direccion': 'Av. Circunvalación y 1° de Mayo', 'calle': 'Av. Circunvalación y 1° de Mayo',
        'lat': -31.669238, 'lng': -60.717109,
        'grupos': 'De lunes a viernes de 8 a 18 horas, por WhatsApp o por correo.',
        'whatsapp': '5493425118351', 'telefono_txt': '+54 9 342 511-8351',
        'correo': 'turnos.museo@gmail.com',
        # Sin propuestas cargadas: debajo siguen sus secciones propias (Parque, Museo, Auditorio, material).
        'shortcode': None, 'propuestas': None,
    },
    {
        'slug': 'cesarlopezclaro', 'pagina': 27565, 'tipo': 'Museum',
        'nombre': 'Museo Municipal César López Claro', 'corto': 'el Museo López Claro',
        'lead': 'La Casa Museo César López Claro funciona en la vivienda del barrio Guadalupe donde vivieron el pintor y su compañera, María Brizzi, y exhibe el patrimonio que donaron a la ciudad.',
        'cuerpo': ['Además de la exhibición permanente, el museo investiga su vasta producción pictórica y desarrolla experiencias de formación en conservación y preservación de obra. Desde 2020, una propuesta educativa permite conocer de cerca ese trabajo: cómo se conserva, se cataloga y se manipula una obra.'],
        'horarios': [('Horario', 'Lunes a viernes de 9 a 12 horas.'),
                     ('Visitas guiadas', 'Cupo de 30 personas por visita.')],
        'direccion': 'Piedras 7352, barrio Guadalupe', 'calle': 'Piedras 7352',
        'lat': -31.604831, 'lng': -60.673588,
        'grupos': 'Pedí turno por correo o por teléfono.',
        'telefono': '+543424601575', 'telefono_txt': '(0342) 460-1575',
        'correo': 'museo.lopezclaro@santafeciudad.gov.ar',
        'shortcode': '[cesarlopezclaro]', 'propuestas': 'Propuestas del Museo',
    },
    {
        'slug': 'mmav', 'pagina': 20341, 'tipo': 'Museum',
        'nombre': 'Museo Municipal de Artes Visuales Sor Josefa Díaz y Clucellas', 'corto': 'el Museo Sor Josefa',
        'lead': 'El Museo Municipal de Artes Visuales Sor Josefa Díaz y Clucellas es una referencia de las artes visuales de la región.',
        'cuerpo': ['Fue creado en 1936 y en 1957 tomó el nombre de Sor Josefa Díaz y Clucellas, la primera pintora con firma del continente. Su patrimonio reúne más de 1100 obras de arte moderno y contemporáneo, y cuenta con salas de exhibición, un patio de esculturas y una biblioteca especializada.',
                   'Trabaja para dar a conocer prácticas artísticas de distintos lenguajes y recibe a públicos diversos, con atención a la accesibilidad.'],
        'horarios': [('Mañana', 'Miércoles a viernes de 9:30 a 12:30 horas.'),
                     ('Tarde, de abril a octubre', 'Miércoles a domingos y feriados de 16 a 19 horas.'),
                     ('Tarde, de noviembre a marzo', 'Miércoles a domingos y feriados de 17 a 20 horas.'),
                     ('Visitas guiadas', 'Cupo de 30 personas por visita.')],
        'direccion': 'San Martín 2068', 'calle': 'San Martín 2068',
        'lat': -31.651708, 'lng': -60.708076,
        'grupos': 'Pedí turno por correo.',
        'correo': 'educacion.museosorjosefa@santafeciudad.gov.ar',
        'shortcode': '[sorjosefa]', 'propuestas': 'Propuestas del Museo',
    },
    {
        'slug': 'museo-del-teatro', 'pagina': 23358, 'tipo': 'Museum',
        'nombre': 'Museo del Teatro', 'corto': 'el Museo del Teatro',
        'lead': 'El Museo del Teatro pone en valor el patrimonio del Teatro Municipal 1° de Mayo, el gran coliseo santafesino.',
        'cuerpo': ['Fue creado en 2002 y exhibe sus colecciones en la Sala Franze, donde también funcionan talleres de restauración. En 2017 recibió el fondo documental y artístico del pianista Ariel Ramírez.'],
        'horarios': [('Visitas guiadas con turno', 'Martes de 9 a 11 horas y de 14 a 15 horas. Jueves de 9 a 11 horas. Viernes de 14 a 15 horas.'),
                     ('Cupo', '30 personas por visita.')],
        'direccion': 'San Martín 2020, Teatro Municipal 1° de Mayo', 'calle': 'San Martín 2020',
        'lat': -31.652359, 'lng': -60.707893,
        'grupos': 'Pedí turno por correo.',
        'correo': 'museodelteatro@santafeciudad.gov.ar',
        'shortcode': '[museodelteatro]', 'propuestas': 'Propuestas del Museo',
    },
    {
        'slug': 'ariel-ramirez', 'pagina': 15198, 'tipo': 'MusicVenue',
        'nombre': 'Sala Ariel Ramírez', 'corto': 'la Sala Ariel Ramírez',
        'lead': 'La Sala Ariel Ramírez es el espacio de la ciudad dedicado a la obra del pianista y compositor santafesino, en la planta alta de la Estación Belgrano.',
        'cuerpo': ['Allí se presentan los conciertos del Ciclo Ariel Ramírez y de la Convocatoria Nacional que lleva su nombre, que invita a recrear, reinterpretar y preservar el patrimonio cultural inmaterial de su obra.'],
        'horarios': [('Horario', 'Abre en los conciertos de su programación.')],
        'direccion': 'Bv. Gálvez 1150, Estación Belgrano, planta alta', 'calle': 'Bv. Gálvez 1150',
        'lat': -31.638728, 'lng': -60.686618,
        'shortcode': '[salaarielramirez]', 'propuestas': 'Propuestas de la Sala',
    },
    {
        'slug': 'sala-mercado-editorial', 'pagina': 20519, 'tipo': 'Library',
        'nombre': 'Sala Mercado Editorial', 'corto': 'la Sala Mercado Editorial',
        'lead': 'La Sala Mercado Editorial acompaña y promueve la práctica editorial de la ciudad y la región.',
        'cuerpo': ['Se creó en diciembre de 2021, durante la XXVII Feria del Libro de Santa Fe, como un fondo de archivo literario y editorial: reúne, protege, difunde y da libre acceso a obras contemporáneas e históricas. Su programación incluye presentaciones de libros, exhibiciones, homenajes, celebraciones, formaciones y ferias editoriales.'],
        'horarios': [('Horario', 'Con visita programada: <a href="https://docs.google.com/forms/d/e/1FAIpQLSed-z40xN42wL9BXZsO1Du4WAioEBKS2EYabPJwNauIK4PSWg/viewform" target="_blank" rel="noopener">completá el formulario</a>.')],
        'direccion': 'Balcarce 1635, Mercado Progreso', 'calle': 'Balcarce 1635',
        'lat': -31.638875, 'lng': -60.692988,
        'grupos': 'Pedí turno por correo.',
        'correo': 'mercadoeditorial@santafeciudad.gov.ar',
        'shortcode': '[salamercadoeditorial]', 'propuestas': 'Propuestas de la Sala',
    },
]

PLANTILLA = open(os.path.join(AQUI, 'plantilla.html'), encoding='utf-8').read()


def datos_visita(e):
    filas = [f'<div><dt>{k}</dt><dd>{v}</dd></div>' for k, v in e['horarios']]
    filas.append('<div><dt>Entrada</dt><dd>Libre y gratuita.</dd></div>')
    if e.get('direccion'):
        filas.append(f'<div><dt>Dirección</dt><dd>{e["direccion"]}</dd></div>')
    if e.get('correo') or e.get('whatsapp') or e.get('telefono'):
        medios = []
        if e.get('whatsapp'):
            medios.append(f'<a href="https://wa.me/{e["whatsapp"]}" target="_blank" rel="noopener">WhatsApp {e["telefono_txt"]}</a>')
        if e.get('telefono'):
            medios.append(f'<a href="tel:{e["telefono"]}">{e["telefono_txt"]}</a>')
        if e.get('correo'):
            medios.append(f'<a href="mailto:{e["correo"]}">{e["correo"]}</a>')
        filas.append(f'<div><dt>Visitas educativas en grupo</dt><dd>{e["grupos"]}<br>{" · ".join(medios)}</dd></div>')
    return '\n          '.join(filas)


def json_ld(e):
    d = {
        '@context': 'https://schema.org', '@type': e['tipo'], 'name': e['nombre'],
        'url': f'{SITIO}/museos-y-salas/{e["slug"]}/',
        'publicAccess': True, 'isAccessibleForFree': True,
        'parentOrganization': {'@type': 'GovernmentOrganization', 'name': 'Municipalidad de Santa Fe', 'url': 'https://www.santafeciudad.gov.ar/'},
    }
    if e.get('direccion'):
        d['address'] = {'@type': 'PostalAddress', 'streetAddress': e['calle'], 'addressLocality': 'Santa Fe',
                        'addressRegion': 'Santa Fe', 'postalCode': '3000', 'addressCountry': 'AR'}
        d['geo'] = {'@type': 'GeoCoordinates', 'latitude': e['lat'], 'longitude': e['lng']}
    if e.get('correo'):
        d['email'] = e['correo']
    if e.get('telefono'):
        d['telephone'] = e['telefono_txt']
    return json.dumps(d, ensure_ascii=False, indent=2)


def mapa(e):
    if not e.get('direccion'):
        return ''
    nombre = html.escape(e['nombre'], quote=True)
    osm = f'https://www.openstreetmap.org/?mlat={e["lat"]}&amp;mlon={e["lng"]}#map=18/{e["lat"]}/{e["lng"]}'
    llegar = f'https://www.openstreetmap.org/directions?to={e["lat"]}%2C{e["lng"]}'
    return (f'      <div class="me-mapa">\n'
            f'        <div class="me-mapa__lienzo" data-me-mapa data-lat="{e["lat"]}" data-lng="{e["lng"]}" data-nombre="{nombre}" role="region" aria-label="Mapa de ubicación de {nombre}"></div>\n'
            f'        <p class="me-mapa__enlaces">\n'
            f'          <a href="{osm}" target="_blank" rel="noopener">Ver en OpenStreetMap</a>\n'
            f'          <a href="{llegar}" target="_blank" rel="noopener">Cómo llegar</a>\n'
            f'        </p>\n'
            f'      </div>\n')


for e in ESPACIOS:
    reemplazos = {
        '{{LEAD}}': e['lead'],
        '{{CUERPO}}': '\n      '.join(f'<p>{p}</p>' for p in e['cuerpo']),
        '{{VISITA_TITULO}}': 'Visitá ' + e['corto'],
        '{{DATOS}}': datos_visita(e),
        '{{MAPA}}': mapa(e),
        '{{VISITA_CLASE}}': '' if e.get('direccion') else ' me-visita--solo',
        '{{PROPUESTAS_BLOQUE}}': '' if not e['propuestas'] else (
            '\n\n    <section class="me-propuestas" aria-labelledby="me-propuestas-titulo">\n'
            f'      <h2 class="me-titulo" id="me-propuestas-titulo"><i class="me-barra"></i>{e["propuestas"]}</h2>\n'
            '    </section>'),
        # Con propuestas, el shortcode cierra la sección; sin ellas, los 50 px van en la última sección de la página.
        '{{PIE}}': ('  .elementor-section:has(#me) { padding-bottom: 50px; }' if e['propuestas'] else
                    '  .elementor:has(#me) > .elementor-section:has(#me) + .elementor-section { margin-top: clamp(40px, 6vw, 72px); }\n'
                    '  .elementor:has(#me) > .elementor-section:last-child { padding-bottom: 50px; margin-bottom: 0; }\n'
                    '  .elementor:has(#me) > .elementor-section:last-child .elementor-widget-wrap { padding-bottom: 0; }'),
        '{{JSONLD}}': json_ld(e),
    }
    salida = PLANTILLA
    for k, v in reemplazos.items():
        salida = salida.replace(k, v)
    assert '{{' not in salida, e['slug']
    open(os.path.join(AQUI, f'{e["slug"]}.html'), 'w', encoding='utf-8', newline='\n').write(salida)
    print(e['slug'], len(salida))
