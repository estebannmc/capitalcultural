"""Arma docs/inicio-preview.html: la home publicada con los widgets de sitio-nuevo/ en su lugar.

Baja la home, la agenda y las noticias del día y deja todo en un solo archivo que se abre
en cualquier navegador. Uso, desde la raíz del repo: python docs/build-inicio-preview.py
"""
import re
import urllib.request
from pathlib import Path

SITIO = 'https://www.santafeciudad.gov.ar/capitalcultural/'
repo = Path(__file__).resolve().parent.parent


def bajar(ruta):
    pedido = urllib.request.Request(SITIO + ruta, headers={'User-Agent': 'Mozilla/5.0'})
    with urllib.request.urlopen(pedido, timeout=30) as r:
        return r.read().decode('utf-8')


nuevo = repo / 'sitio-nuevo'
html = bajar('')
cca = bajar('wp-admin/admin-ajax.php?action=cca_eventos')
ccn = bajar('wp-admin/admin-ajax.php?action=ccn_noticias')
leer = lambda n: (nuevo / n).read_text(encoding='utf-8')

# Rocket Loader de Cloudflare no corre fuera del sitio: los scripts vuelven a ser normales.
html = re.sub(r'<script[^>]*rocket-loader[^>]*></script>', '', html)
html = re.sub(r'type="[0-9a-f]+-text/javascript"', 'type="text/javascript"', html)


def secciones(doc):
    """Posición [inicio, fin) de cada sección de primer nivel de Elementor, por data-id."""
    out = {}
    for m in re.finditer(r'<section class="elementor-section elementor-top-section[^>]*data-id="([0-9a-f]+)"', doc):
        depth, i = 0, m.start()
        for t in re.finditer(r'<(/?)section\b', doc[i:]):
            depth += -1 if t.group(1) else 1
            if depth == 0:
                out[m.group(1)] = (i, i + t.end() + doc[i + t.end():].index('>') + 1)
                break
    return out


def widget(contenido):
    return ('<section class="elementor-section elementor-top-section elementor-section-boxed">'
            '<div class="elementor-container elementor-column-gap-default">'
            '<div class="elementor-column elementor-col-100 elementor-top-column">'
            '<div class="elementor-widget-wrap elementor-element-populated">'
            '<div class="elementor-element elementor-widget elementor-widget-html">'
            f'<div class="elementor-widget-container">{contenido}</div></div></div></div></div></section>')



s = secciones(html)
# Secciones de la home publicada el 29/10/2026: header, intro, slider, Explorá, agenda, noticias y footer.
ids = ['b74bfbd', '5380a0a', '86b3116', '605bd05', '732d9a5', '78fc948', '7beab50']
assert all(i in s for i in ids), [i for i in ids if i not in s]
trozo = lambda i: html[s[i][0]:s[i][1]]
noticias = trozo('78fc948')
if 'id="noticias"' not in noticias[:300]:
    noticias = noticias.replace('<section ', '<section id="noticias" ', 1)

# Los widgets salen de sitio-nuevo/, así la vista previa muestra lo que todavía no se cargó.
cuerpo = ''.join([
    widget(leer('header.html')),
    widget(leer('inicio-intro.html')),
    trozo('86b3116'),
    widget(leer('inicio-accesos.html')),
    widget(leer('inicio-agenda.html')),
    noticias,
    trozo('7beab50'),
])
ini, fin = s['b74bfbd'][0], s['7beab50'][1]
html = html[:ini] + cuerpo + html[fin:]

# Las llamadas a admin-ajax devuelven la foto de la agenda y las noticias del día en que se armó.
shim = f'''<base href="https://www.santafeciudad.gov.ar/capitalcultural/">
<script>
(function () {{
  var datos = {{ cca_eventos: {cca}, ccn_noticias: {ccn} }};
  var original = window.fetch;
  window.fetch = function (url, opts) {{
    var m = String(url).match(/action=(cca_eventos|ccn_noticias)/);
    if (m) return Promise.resolve(new Response(JSON.stringify(datos[m[1]]), {{ headers: {{ 'Content-Type': 'application/json' }} }}));
    return original.apply(this, arguments);
  }};
}})();
</script>'''
html = html.replace('<head>', '<head>\n' + shim, 1)

meta = 'Agenda cultural de Santa Fe: muestras, conciertos, eventos y convocatorias de la Secretaría de Cultura, y los museos, teatros y salas de la ciudad.'
html, n = re.subn(r'(<meta (?:name="description"|property="og:description") content=")[^"]*', lambda m: m.group(1) + meta, html)
assert n == 2, n

aviso = ('<div style="position:fixed;left:12px;bottom:12px;z-index:99999;padding:8px 12px;border-radius:6px;'
         'background:#e63312;color:#fff;font:600 13px/1.2 system-ui,sans-serif;box-shadow:0 6px 18px rgba(0,0,0,.25)">'
         'Vista previa · no publicada</div>')
html = html.replace('</body>', aviso + '\n</body>', 1)

salida = repo / 'docs' / 'inicio-preview.html'
salida.write_text(html, encoding='utf-8')
print(salida, len(html))
