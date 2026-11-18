document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("ccn-generator");
  const output = document.getElementById("ccn_g_output");
  const copyButton = document.getElementById("ccn_g_copy");
  const feedback = document.getElementById("ccn_g_feedback");
  const preview = document.getElementById("ccn-preview");
  const template = document.getElementById("ccn-preview-template");

  if (!form || !output) return;

  const defaults = JSON.parse(form.dataset.defaults || "{}");
  const fields = {
    titulo: document.getElementById("ccn_g_titulo"),
    cantidad: document.getElementById("ccn_g_cantidad"),
    paginas: document.getElementById("ccn_g_paginas"),
    imagenes: document.getElementById("ccn_g_imagenes"),
    resumen: document.getElementById("ccn_g_resumen"),
    botonOn: document.getElementById("ccn_g_boton_on"),
    boton: document.getElementById("ccn_g_boton")
  };
  let timer = null;

  // Las comillas y los corchetes cortan el shortcode.
  function clean(text) {
    return String(text).replace(/["\[\]]/g, "").trim();
  }

  function values() {
    return {
      titulo: clean(fields.titulo.value),
      cantidad: fields.cantidad.value,
      paginas: fields.paginas.value,
      imagenes: fields.imagenes.checked ? "si" : "no",
      resumen: fields.resumen.checked ? "si" : "no",
      boton: fields.botonOn.checked ? clean(fields.boton.value) : ""
    };
  }

  // Solo se escriben las opciones que cambian respecto de los valores por defecto.
  function shortcode(v) {
    const attrs = [];
    Object.keys(v).forEach(function (key) {
      if (String(v[key]) !== String(defaults[key])) {
        attrs.push(key + '="' + v[key] + '"');
      }
    });
    return "[capital_noticias" + (attrs.length ? " " + attrs.join(" ") : "") + "]";
  }

  function renderPreview(v) {
    if (!preview || !template || !window.ccnNoticias) return;

    const root = template.content.firstElementChild.cloneNode(true);
    const size = parseInt(v.cantidad, 10) > 3 ? 6 : 3;
    root.dataset.limit = size;
    root.dataset.pages = v.paginas;
    root.dataset.images = v.imagenes === "si" ? "1" : "0";
    root.dataset.excerpt = v.resumen === "si" ? "1" : "0";

    const title = root.querySelector(".ccn-title");
    if (title) {
      if (v.titulo) {
        title.querySelector(".ccn-title-text").textContent = v.titulo;
      } else {
        title.remove();
        root.removeAttribute("aria-labelledby");
        root.setAttribute("aria-label", "Noticias");
      }
    }

    const more = root.querySelector(".ccn-more");
    if (more) {
      if (v.boton) {
        more.querySelector(".ccn-more-label").textContent = v.boton;
      } else {
        more.remove();
      }
    }

    // El esqueleto de la plantilla viene con la cantidad por defecto.
    const list = root.querySelector(".ccn-pages .ccn-list");
    const skeleton = list.firstElementChild;
    if (v.imagenes !== "si") {
      const media = skeleton.querySelector(".ccn-media");
      if (media) media.remove();
      skeleton.querySelector(".ccn-card").classList.add("ccn-card--text");
    }
    list.innerHTML = "";
    for (let i = 0; i < size; i++) list.appendChild(skeleton.cloneNode(true));

    preview.innerHTML = "";
    preview.appendChild(root);
    window.ccnNoticias.init(root);
  }

  function update(delay) {
    fields.boton.disabled = !fields.botonOn.checked;
    const v = values();
    output.value = shortcode(v);
    if (feedback) feedback.textContent = "";

    clearTimeout(timer);
    timer = setTimeout(function () { renderPreview(v); }, delay || 0);
  }

  form.addEventListener("change", function () { update(0); });
  form.addEventListener("input", function (e) {
    if (e.target.type === "text") update(350);
  });

  copyButton.addEventListener("click", function () {
    const done = function () { feedback.textContent = "Shortcode copiado. Pegalo en un widget «Shortcode» de Elementor."; };
    const fallback = function () {
      output.focus();
      output.select();
      try {
        document.execCommand("copy");
        done();
      } catch (e) {
        feedback.textContent = "Seleccioná el texto y copialo con Ctrl+C.";
      }
    };

    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(output.value).then(done, fallback);
    } else {
      fallback();
    }
  });

  output.addEventListener("focus", function () { output.select(); });

  update(0);
});
