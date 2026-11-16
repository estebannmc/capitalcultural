"""Copia base.css y el script del video en cada subpágina del Archivo de la Imagen.

Las páginas tienen el estilo común entre /* base */ y /* fin base */, y las que llevan
video, el script entre <!-- video --> y <!-- fin video -->. Se edita base.css o VIDEO
acá, se corre `python sitio-nuevo/archivo-imagen/generar.py` y se pegan los .html.
"""
import pathlib, re

AQUI = pathlib.Path(__file__).parent

VIDEO = """<script>
  document.querySelectorAll('#ai a.ai-video[data-youtube]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      e.preventDefault();
      var f = document.createElement('iframe');
      f.src = 'https://www.youtube-nocookie.com/embed/' + a.dataset.youtube + '?autoplay=1&rel=0';
      f.title = a.dataset.titulo;
      f.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
      f.allowFullscreen = true;
      var caja = document.createElement('div');
      caja.className = 'ai-video';
      caja.appendChild(f);
      a.replaceWith(caja);
    });
  });
</script>"""


def generar():
    base = (AQUI / 'base.css').read_text(encoding='utf-8').rstrip()
    for pagina in sorted(AQUI.glob('*.html')):
        html = pagina.read_text(encoding='utf-8')
        nuevo, n = re.subn(r'/\* base \*/.*?/\* fin base \*/', lambda m: '/* base */\n' + base + '\n  /* fin base */', html, flags=re.S)
        assert n == 1, f'{pagina.name}: falta /* base */ … /* fin base */'
        nuevo = re.sub(r'<!-- video -->.*?<!-- fin video -->', lambda m: '<!-- video -->\n' + VIDEO + '\n<!-- fin video -->', nuevo, flags=re.S)
        if ('class="ai-video"' in nuevo) != ('<!-- video -->' in nuevo):
            raise SystemExit(f'{pagina.name}: el video y su script tienen que ir juntos')
        if nuevo != html:
            pagina.write_text(nuevo, encoding='utf-8')
        print(pagina.name)


if __name__ == '__main__':
    generar()
