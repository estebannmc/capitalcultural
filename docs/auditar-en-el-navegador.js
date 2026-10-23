/* =====================================================================
   Capital Cultural · auditor de página, para pegar en la consola
   ---------------------------------------------------------------------
   Edge/Chrome: F12 → Consola → pegar → Enter. Si pide habilitar el pegado,
   escribir "allow pasting" primero.

   Audita LA PÁGINA QUE ESTÉS VIENDO contra el sistema de docs/sistema-visual.html.
   No manda nada a ningún lado: corre entero en tu navegador.

   Para recorrer varias páginas: abrilas en pestañas y repetí.
   ===================================================================== */
(() => {
  const PALETA = {
    '#002751':'Azul 01', '#57b8e9':'Celeste', '#e63312':'Rojo Cultura',
    '#fca100':'Naranja Cultura', '#ffffff':'Blanco', '#dadad9':'Gris 20',
    '#606060':'Gris 75', '#1d1d1b':'Negro SF', '#f4f4f3':'Fondo',
  };
  const FUENTES = ['geologica', 'encode sans'];
  const VIEJOS = { '#00c08b':'Verde 01 (abandonado)', '#00fe9b':'Verde 02 (fuera)',
                   '#1639f1':'Azul 02 (fuera)', '#f2e61a':'Amarillo (fuera)',
                   '#00aaff':'celeste viejo, debería ser #57b8e9' };

  const lin = c => (c/=255, c<=.04045 ? c/12.92 : ((c+.055)/1.055)**2.4);
  const lum = ([r,g,b]) => .2126*lin(r)+.7152*lin(g)+.0722*lin(b);
  const cr  = (a,b) => { const x=lum(a), y=lum(b), h=Math.max(x,y), l=Math.min(x,y); return (h+.05)/(l+.05); };
  const nums = s => (String(s).match(/[\d.]+/g)||[]).map(Number);
  const hex = ([r,g,b]) => '#'+[r,g,b].map(v=>Math.round(v).toString(16).padStart(2,'0')).join('');
  const sobre = (fg,bg) => { const a = fg[3] ?? 1; return [0,1,2].map(i => fg[i]*a + bg[i]*(1-a)); };

  const visible = el => { const r = el.getBoundingClientRect(), s = getComputedStyle(el);
    return r.width>1 && r.height>1 && s.visibility!=='hidden' && s.display!=='none' && +s.opacity>.1; };

  // Fondo efectivo. Devuelve [color, seguro]: seguro=false cuando atrás hay una
  // imagen o un degradé, porque ahí el número no se puede dar por bueno y lo
  // honesto es marcarlo para mirarlo, no inventar un ratio.
  const fondoDe = el => {
    let n = el;
    while (n && n.nodeType === 1) {
      const s = getComputedStyle(n);
      const bi = s.backgroundImage;
      if (bi && bi !== 'none') {
        if (/url\(/.test(bi)) return [null, false];
        const stops = bi.match(/rgba?\([^)]+\)/g);
        if (stops && stops.length) {                       // degradé: promedio de sus paradas
          const cs = stops.map(nums).filter(c => c.length>=3);
          const op = cs.filter(c => (c[3] ?? 1) > .5);
          if (op.length) return [[0,1,2].map(i => op.reduce((a,c)=>a+c[i],0)/op.length), false];
        }
        return [null, false];
      }
      const c = nums(s.backgroundColor);
      if (c.length>=3 && (c[3] ?? 1) > .85) return [c.slice(0,3), true];
      n = n.parentElement;
    }
    return [[255,255,255], true];
  };

  const bajos = [], revisar = [], colores = {}, fuentes = {}, viejos = {};
  for (const el of document.querySelectorAll('*')) {
    if (!visible(el)) continue;
    const s = getComputedStyle(el);

    for (const prop of ['color','backgroundColor','borderTopColor']) {
      const c = nums(s[prop]);
      if (c.length>=3 && (c[3] ?? 1) > .05) {
        const h = hex(c);
        if (VIEJOS[h]) viejos[h] = (viejos[h]||0)+1;
        if (prop==='backgroundColor') colores[h] = (colores[h]||0)+1;
      }
    }

    const txt = [...el.childNodes].filter(n=>n.nodeType===3&&n.textContent.trim())
                  .map(n=>n.textContent.trim()).join(' ');
    if (!txt) continue;
    fuentes[s.fontFamily.split(',')[0].replace(/["']/g,'').toLowerCase()] =
      (fuentes[s.fontFamily.split(',')[0].replace(/["']/g,'').toLowerCase()]||0)+1;

    const fg = nums(s.color); if (fg.length<3) continue;
    const [bg, seguro] = fondoDe(el);
    const px = parseFloat(s.fontSize), peso = parseInt(s.fontWeight)||400;
    const minimo = (px>=24 || (px>=18.66 && peso>=700)) ? 3 : 4.5;
    const fila = { texto: txt.slice(0,44), px:+px.toFixed(1), peso,
                   color: hex(fg), elemento: el };
    if (!seguro) { revisar.push({ ...fila, nota:'fondo con imagen o degradé' }); continue; }
    const ratio = +cr(sobre(fg,bg), bg).toFixed(2);
    if (ratio < minimo) bajos.push({ ...fila, ratio, minimo, fondo: hex(bg) });
  }

  const sinAlt = [...document.images].filter(i => !i.hasAttribute('alt'));
  const hs = [...document.querySelectorAll('h1,h2,h3,h4,h5,h6')];
  const saltos = hs.map(h=>+h.tagName[1]).reduce((a,n,i,v)=> i&&n-v[i-1]>1 ? [...a,`h${v[i-1]}→h${n}`] : a, []);
  const chicos = [...document.querySelectorAll('a,button,[role=button],input,select,summary')]
    .filter(el => { if (!visible(el)) return false; const r = el.getBoundingClientRect();
                    return r.height < 24 || r.width < 24; });
  const fueraPaleta = Object.entries(colores).filter(([h]) => !PALETA[h]);
  const fueraFuente = Object.entries(fuentes).filter(([f]) => !FUENTES.some(k=>f.includes(k)));

  const t = (n,s) => `%c${n}%c  ${s}`;
  const E = 'font-weight:700', N = 'font-weight:400';
  console.log('%c Capital Cultural · auditoría ', 'background:#002751;color:#fff;padding:3px 8px;font-weight:700');
  console.log(location.href);
  console.log(t('Documento', `lang=${document.documentElement.lang||'SIN LANG'} · ${document.querySelectorAll('h1').length} h1 · title ${document.title?'sí':'NO'}`), E, N);

  if (Object.keys(viejos).length) { console.warn('COLORES ABANDONADOS EN USO'); console.table(
    Object.entries(viejos).map(([h,n]) => ({ color:h, qué:VIEJOS[h], veces:n }))); }
  else console.log('%c✓ sin colores abandonados', 'color:#0a7');

  if (bajos.length) { console.warn(`CONTRASTE POR DEBAJO DE AA: ${bajos.length}`);
    console.table(bajos.sort((a,b)=>a.ratio-b.ratio).slice(0,25)); }
  else console.log('%c✓ contraste: sin hallazgos sobre fondo sólido', 'color:#0a7');

  if (revisar.length) console.log(`%c${revisar.length} textos sobre imagen o degradé — el número no es fiable, hay que mirarlos`, 'color:#b70');
  if (sinAlt.length)  { console.warn(`IMÁGENES SIN alt: ${sinAlt.length}`); console.log(sinAlt); }
  if (saltos.length)  console.warn('SALTOS DE TÍTULO: ' + saltos.join(', '));
  if (chicos.length)  { console.warn(`OBJETIVOS TÁCTILES < 24px: ${chicos.length}`); console.log(chicos); }
  if (fueraPaleta.length) { console.warn('FONDOS FUERA DE PALETA'); console.table(
    fueraPaleta.sort((a,b)=>b[1]-a[1]).slice(0,15).map(([h,n])=>({color:h, veces:n}))); }
  if (fueraFuente.length) { console.warn('FUENTES FUERA DE MARCA'); console.table(
    fueraFuente.sort((a,b)=>b[1]-a[1]).map(([f,n])=>({fuente:f, nodos:n}))); }

  console.log('%cTip: las filas de las tablas traen el nodo en la columna "elemento" — clic para resaltarlo en el Inspector.', 'color:#666');
  return { bajos, revisar, sinAlt, chicos, fueraPaleta, fueraFuente, viejos };
})();
