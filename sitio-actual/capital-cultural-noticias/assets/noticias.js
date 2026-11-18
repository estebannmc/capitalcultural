(function () {
  const MONTHS = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];

  function init(root) {
    const wrap = root.querySelector(".ccn-pages");
    const message = root.querySelector(".ccn-message");
    const pager = root.querySelector(".ccn-pager");
    const prev = root.querySelector(".ccn-prev");
    const next = root.querySelector(".ccn-next");
    const status = root.querySelector(".ccn-status");
    const perPage = parseInt(root.dataset.limit, 10) > 3 ? 6 : 3;
    const maxPages = Math.min(3, Math.max(1, parseInt(root.dataset.pages, 10) || 1));
    const showImages = root.dataset.images !== "0";
    const showExcerpt = root.dataset.excerpt !== "0";

    let offset = -3 * 3600;
    let pages = [];
    let current = 0;

    if (!wrap) return;

    fetch(root.dataset.endpoint, { credentials: "same-origin" })
      .then(function (response) {
        if (!response.ok) throw new Error("HTTP " + response.status);
        return response.json();
      })
      .then(function (data) {
        if (!data || !data.ok || !Array.isArray(data.posts)) throw new Error("Sin datos");
        if (typeof data.offset === "number") offset = data.offset;

        const posts = data.posts.slice(0, perPage * maxPages);
        clearList();
        if (!posts.length) {
          showMessage("No hay noticias publicadas por ahora.");
          return;
        }

        // Todas las páginas van una encima de otra en la misma celda: el bloque
        // toma el alto de la más alta y no salta al cambiar.
        for (let i = 0; i < posts.length; i += perPage) {
          const page = el("ul", "ccn-list ccn-page");
          posts.slice(i, i + perPage).forEach(function (post) {
            page.appendChild(item(post));
          });
          wrap.appendChild(page);
          pages.push(page);
        }
        show(0);

        if (pages.length > 1 && pager) {
          pager.hidden = false;
          prev.addEventListener("click", function () { show(current - 1); });
          next.addEventListener("click", function () { show(current + 1); });
        }
      })
      .catch(function () {
        clearList();
        showMessage("No pudimos cargar las noticias en este momento.");
      });

    // La página que sale se desvanece y después aparece la nueva (ver CSS).
    function show(index) {
      if (index < 0 || index >= pages.length) return;
      current = index;
      pages.forEach(function (page, i) {
        const active = i === index;
        page.classList.toggle("is-active", active);
        page.setAttribute("aria-hidden", active ? "false" : "true");
        page.inert = !active;
      });
      if (prev) prev.disabled = index === 0;
      if (next) next.disabled = index === pages.length - 1;
      if (status && pages.length > 1) status.textContent = "Página " + (index + 1) + " de " + pages.length;
    }

    // Foto a la izquierda; fecha, título y resumen a la derecha.
    function item(post) {
      const li = el("li", "ccn-item");
      const link = el("a", showImages ? "ccn-card" : "ccn-card ccn-card--text");
      link.href = post.url;
      link.target = "_blank";
      link.rel = "noopener";

      if (showImages) {
        const media = el("span", "ccn-media");
        if (post.image) {
          const img = document.createElement("img");
          img.src = post.image;
          img.alt = "";
          img.loading = "lazy";
          img.decoding = "async";
          img.addEventListener("error", function () {
            img.remove();
            media.classList.add("ccn-media--empty");
          });
          media.appendChild(img);
        } else {
          media.classList.add("ccn-media--empty");
        }
        link.appendChild(media);
      }

      const body = el("span", "ccn-body");
      if (post.date) {
        const date = el("time", "ccn-date", dateLabel(post.date));
        date.dateTime = new Date(post.date * 1000).toISOString();
        body.appendChild(date);
      }
      body.appendChild(el("h3", "ccn-name", post.title));
      if (showExcerpt && post.excerpt) body.appendChild(el("span", "ccn-excerpt", post.excerpt));
      link.appendChild(body);

      link.appendChild(el("span", "screen-reader-text", "(se abre en una pestaña nueva)"));
      li.appendChild(link);
      return li;
    }

    // "25 de octubre", con el año solo si no es el actual.
    function dateLabel(ts) {
      const d = new Date((ts + offset) * 1000);
      const year = new Date((Date.now() / 1000 + offset) * 1000).getUTCFullYear();
      let label = d.getUTCDate() + " de " + MONTHS[d.getUTCMonth()];
      if (d.getUTCFullYear() !== year) label += " de " + d.getUTCFullYear();
      return label;
    }

    function clearList() {
      wrap.innerHTML = "";
      wrap.setAttribute("aria-busy", "false");
    }

    function showMessage(text) {
      if (!message) return;
      message.textContent = text;
      message.hidden = false;
    }
  }

  function el(tag, className, text) {
    const node = document.createElement(tag);
    node.className = className;
    if (text !== undefined) node.textContent = text;
    return node;
  }

  // La vista previa de Ajustes vuelve a montar el bloque con cada cambio.
  window.ccnNoticias = { init: init };

  document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".ccn-noticias[data-ccn-noticias]").forEach(init);
  });
})();
