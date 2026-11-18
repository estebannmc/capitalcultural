document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("cca-generator");
  const output = document.getElementById("cca_g_output");
  const copyButton = document.getElementById("cca_g_copy");
  const feedback = document.getElementById("cca_g_feedback");
  const preview = document.getElementById("cca-preview");
  const template = document.getElementById("cca-preview-template");

  if (!form || !output) return;

  const defaults = JSON.parse(form.dataset.defaults || "{}");
  const fields = {
    titulo: document.getElementById("cca_g_titulo"),
    cantidad: document.getElementById("cca_g_cantidad"),
    filtro: document.getElementById("cca_g_filtro"),
    filtros: document.getElementById("cca_g_filtros"),
    imagenes: document.getElementById("cca_g_imagenes"),
    botonOn: document.getElementById("cca_g_boton_on"),
    boton: document.getElementById("cca_g_boton")
  };
  let timer = null;

  // Las comillas y los corchetes cortan el shortcode.
  function clean(text) {
    return String(text).replace(/["\[\]]/g, "").trim();
  }

  function values() {
    const categories = Array.prototype.map.call(
      form.querySelectorAll('input[name="cca_g_categorias"]:checked'),
      function (input) { return input.value; }
    );
    return {
      titulo: clean(fields.titulo.value),
      categorias: categories.join(", "),
      cantidad: fields.cantidad.value,
      filtro: fields.filtro.value,
      filtros: fields.filtros.checked ? "si" : "no",
      imagenes: fields.imagenes.checked ? "si" : "no",
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
    return "[capital_agenda" + (attrs.length ? " " + attrs.join(" ") : "") + "]";
  }

  function renderPreview(v) {
    if (!preview || !template || !window.ccaAgenda) return;

    const root = template.content.firstElementChild.cloneNode(true);
    root.dataset.categories = v.categorias;
    root.dataset.limit = v.cantidad;
    root.dataset.filter = v.filtro;
    root.dataset.filters = v.filtros === "si" ? "1" : "0";
    root.dataset.images = v.imagenes === "si" ? "1" : "0";

    const title = root.querySelector(".cca-title");
    if (title) {
      if (v.titulo) {
        title.querySelector(".cca-title-text").textContent = v.titulo;
      } else {
        title.remove();
        root.removeAttribute("aria-labelledby");
        root.setAttribute("aria-label", "Agenda");
      }
    }

    const footer = root.querySelector(".cca-footer");
    if (footer) {
      if (v.boton) {
        footer.querySelector(".cca-more-label").textContent = v.boton;
      } else {
        footer.remove();
      }
    }

    if (v.imagenes !== "si") {
      root.querySelectorAll(".cca-skeleton .cca-media").forEach(function (media) { media.remove(); });
    }

    preview.innerHTML = "";
    preview.appendChild(root);
    window.ccaAgenda.init(root);
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
