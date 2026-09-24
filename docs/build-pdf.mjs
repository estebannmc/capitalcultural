import { chromium } from 'playwright';
import fs from 'node:fs';

const W = 1240, H = 1754, PAD = 36;

const b = await chromium.launch({ executablePath: '/opt/pw-browsers/chromium' });
const p = await b.newPage({ viewport: { width: W, height: H }, deviceScaleFactor: 2 });
await p.goto('file:///home/user/capitalcultural/docs/sistema-visual.html');
await p.emulateMedia({ media: 'screen' });
await p.waitForTimeout(3000);

// Las manchas del fondo están animadas: cada captura las tomaría en otra
// posición y el verde variaría de página a página. Se congelan y se bajan,
// porque a página completa el lavado verde pesa más que en pantalla.
await p.addStyleTag({ content: `
  *, *::before, *::after { animation: none !important; transition: none !important; }
  .blob.b1{opacity:.55} .blob.b2{opacity:.5} .blob.b3{opacity:.5} .blob.b4{opacity:.45}
` });
await p.waitForTimeout(400);

const res = await p.evaluate(({ H, PAD }) => {
  const MAXH = H - PAD * 2;
  const rect = el => { const r = el.getBoundingClientRect();
                       return { top: r.top + scrollY, bot: r.top + scrollY + r.height, h: r.height }; };

  // Unidad = el bloque más grande que entra en una página. Si no entra, se
  // baja un nivel. Así no se pierden los wrappers altos, que antes se
  // saltaban enteros y terminaban cruzando el corte igual.
  const unidades = el => {
    if (rect(el).h <= MAXH) return [el];
    const hijos = [...el.children].filter(k => rect(k).h > 0);
    return hijos.length ? hijos.flatMap(unidades) : [el];
  };

  const page = document.querySelector('.page');
  let us = [document.querySelector('header.masthead'),
            ...[...page.querySelectorAll(':scope > section')].flatMap(s => [...s.children]),
            document.querySelector('footer.foot')]
           .filter(Boolean).flatMap(unidades);

  const esItemDeGrilla = el => {
    const d = getComputedStyle(el.parentElement).display;
    return d.includes('grid') || d.includes('flex');
  };

  const empujar = (el, salto) => {
    if (esItemDeGrilla(el)) {
      // Un <div> espaciador dentro de una grilla se vuelve UN ÍTEM MÁS y
      // ocupa una celda en vez de empujar: era el bug que partía tarjetas.
      // El empuje va como margin-top, y a TODA la fila para no desalinearla.
      const t = rect(el).top;
      for (const k of [...el.parentElement.children].filter(k => Math.abs(rect(k).top - t) < 4))
        k.style.marginTop = (parseFloat(getComputedStyle(k).marginTop) || 0) + salto + 'px';
    } else {
      const sp = document.createElement('div');
      sp.style.cssText = `height:${salto}px`;
      sp.setAttribute('aria-hidden', 'true');
      el.parentNode.insertBefore(sp, el);
    }
  };
  const pagina = el => Math.floor(rect(el).top / H);

  // Un título de sección y su primer bloque viajan JUNTOS: se agrupan en un
  // bloque y se miden como uno solo. Así se resuelve en UNA pasada en orden
  // de documento —cada empuje sólo afecta lo de abajo, que todavía no se
  // procesó— y no hace falta iterar. Iterar era el problema: al reubicar un
  // título quedaba el espaciador viejo empujando su contenido, el huérfano
  // volvía y el bucle sumaba una página por vuelta.
  const bloques = [];
  for (let i = 0; i < us.length; i++) {
    const el = us[i], sig = us[i + 1];
    if (el.classList.contains('sec-head') && sig && rect(el).h + 28 + rect(sig).h <= MAXH) {
      bloques.push([el, sig]); i++;
    } else bloques.push([el]);
  }

  let movidos = 0;
  for (const bl of bloques) {
    const top = rect(bl[0]).top, bot = rect(bl[bl.length - 1]).bot;
    if (bot - top > MAXH) continue;
    const borde = Math.ceil(top / H) * H;
    if (bot + PAD > borde && top < borde) { empujar(bl[0], borde - top + PAD); movidos++; }
  }

  const partidas = us.filter(el => {
    const r = rect(el);
    if (r.h > MAXH) return false;
    return Math.floor(r.top / H) !== Math.floor((r.bot - 1) / H);
  }).length;
  const huerfanos = us.filter((el, i) =>
    el.classList.contains('sec-head') && us[i + 1] && pagina(us[i + 1]) > pagina(el)).length;

  document.body.style.paddingBottom =
    (H - (document.documentElement.scrollHeight % H)) + 'px';
  return { paginas: Math.ceil(document.documentElement.scrollHeight / H),
           unidades: us.length, bloques: bloques.length, movidos, partidas, huerfanos };
}, { H, PAD });

console.log(`unidades ${res.unidades} · bloques ${res.bloques} · movidos ${res.movidos} · PARTIDAS ${res.partidas} · HUERFANOS ${res.huerfanos} · páginas ${res.paginas}`);
if (res.partidas > 0) { await b.close(); process.exit(1); }

fs.rmSync('/tmp/claude-0/-home-user-capitalcultural/f58a74a9-fc28-5d14-9a91-93454ed33844/scratchpad/art/pdf/pages', { recursive: true, force: true });
fs.mkdirSync('/tmp/claude-0/-home-user-capitalcultural/f58a74a9-fc28-5d14-9a91-93454ed33844/scratchpad/art/pdf/pages', { recursive: true });
for (let i = 0; i < res.paginas; i++) {
  await p.evaluate(y => scrollTo(0, y), i * H);
  await p.waitForTimeout(260);
  await p.screenshot({ path: `/tmp/claude-0/-home-user-capitalcultural/f58a74a9-fc28-5d14-9a91-93454ed33844/scratchpad/art/pdf/pages/p${String(i).padStart(2, '0')}.png` });
}
await b.close();
