"""Escribe el JSON-LD ItemList de las páginas que son listados, leyendo sus propias tarjetas.

Uso: python sitio-nuevo/listas_jsonld.py
Correrlo después de sumar o sacar una tarjeta: reemplaza el bloque <script data-lista> del final
de cada archivo, así la lista de datos nunca queda distinta de la que se ve.
"""
import json
import os
import re

AQUI = os.path.dirname(os.path.abspath(__file__))
DOMINIO = 'https://www.santafeciudad.gov.ar'

# archivo: nombre de la lista
LISTAS = {
    'museos-y-salas.html': 'Museos y salas municipales de Santa Fe',
    'huellas-de-mi-ciudad.html': 'Recorridos de Huellas de mi Ciudad',
    'memorias-urbanas-feministas.html': 'Baldosas de Memorias Urbanas Feministas',
    'patrimonio-arquitectonico.html': 'Patrimonio arquitectónico de Santa Fe',
    'patrimonio-artistico.html': 'Patrimonio artístico en el espacio público de Santa Fe',
}

# Una tarjeta: el enlace interno a una página y, dentro del mismo <a>, su span de título.
TARJETA = re.compile(r'<a class="[^"]*" href="(/capitalcultural/[^"]*/)">(?:(?!</a>).)*?'
                     r'class="[a-z]+-[a-z]+__(?:titulo|nombre)">([^<]+)<', re.S)
BLOQUE = re.compile(r'\n*<script type="application/ld\+json" data-lista>.*?</script>\n?', re.S)


def item_list(nombre, tarjetas):
    return {
        '@context': 'https://schema.org', '@type': 'ItemList', 'name': nombre,
        'numberOfItems': len(tarjetas),
        'itemListElement': [{'@type': 'ListItem', 'position': i, 'name': t.strip(), 'url': DOMINIO + h}
                            for i, (h, t) in enumerate(tarjetas, 1)],
    }


for archivo, nombre in LISTAS.items():
    ruta = os.path.join(AQUI, archivo)
    texto = BLOQUE.sub('', open(ruta, encoding='utf-8').read()).rstrip('\n')
    tarjetas = TARJETA.findall(texto)
    assert tarjetas, f'{archivo}: no encontré tarjetas'
    assert len({h for h, _ in tarjetas}) == len(tarjetas), f'{archivo}: hay enlaces repetidos'
    datos = json.dumps(item_list(nombre, tarjetas), ensure_ascii=False, indent=2)
    texto += f'\n\n<script type="application/ld+json" data-lista>\n{datos}\n</script>\n'
    open(ruta, 'w', encoding='utf-8', newline='\n').write(texto)
    print(archivo, len(tarjetas))
