(function () {
  const DAY = 86400;
  const MONTHS = ["ene", "feb", "mar", "abr", "may", "jun", "jul", "ago", "sep", "oct", "nov", "dic"];
  const WEEKDAYS = ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb"];
  // Hasta 4 tarjetas van en una fila fija; con más, el bloque pasa a carrusel.
  const PER_ROW = 4;
  const AUTOPLAY_MS = 5000;
  const SWIPE_PX = 40;

  function init(root) {
    const list = root.querySelector(".cca-list");
    const message = root.querySelector(".cca-message");
    const filtersWrap = root.querySelector(".cca-filters");
    const buttons = root.querySelectorAll(".cca-filter");
    const moreLink = root.querySelector(".cca-more");
    const limit = parseInt(root.dataset.limit, 10) || PER_ROW;
    const showImages = root.dataset.images !== "0";
    const showFilters = root.dataset.filters !== "0";
    const wanted = (root.dataset.categories || "").split(",").map(normalize).filter(Boolean);

    let events = [];
    let offset = -3 * 3600;

    if (!list) return;

    const carousel = createCarousel(root, list);

    fetch(root.dataset.endpoint, { credentials: "same-origin" })
      .then(function (response) {
        if (!response.ok) throw new Error("HTTP " + response.status);
        return response.json();
      })
      .then(function (data) {
        if (!data || !data.ok || !Array.isArray(data.events)) throw new Error("Sin datos");
        if (typeof data.offset === "number") offset = data.offset;

        const now = Date.now() / 1000;
        events = data.events.filter(function (event) {
          if (event.end <= now) return false;
          if (!wanted.length) return true;
          return (event.categories || []).some(function (category) {
            return wanted.indexOf(normalize(category)) !== -1;
          });
        });

        if (!events.length) {
          clearList();
          showMessage("No hay actividades cargadas en la agenda por ahora.");
          return;
        }

        const counts = {};
        buttons.forEach(function (button) {
          const key = button.dataset.filter;
          counts[key] = select(key).length;
          button.disabled = counts[key] === 0;
          button.addEventListener("click", function () { render(key); });
        });

        let initial = root.dataset.filter || "semana";
        // Con filtros visibles, si la pestaña elegida está vacía se pasa a la siguiente con eventos.
        if (showFilters) {
          [initial, "semana", "proximos"].some(function (key) {
            if (counts[key]) {
              initial = key;
              return true;
            }
            return false;
          });
        }

        if (filtersWrap) filtersWrap.hidden = !showFilters;
        render(initial);
      })
      .catch(function () {
        clearList();
        showMessage("No pudimos cargar la agenda en este momento.");
      });

    // Inicio del día (00:00) en la zona horaria del sitio, como timestamp.
    function dayStart(ts) {
      return Math.floor((ts + offset) / DAY) * DAY - offset;
    }

    // Fecha y hora en la zona horaria del sitio.
    function parts(ts) {
      const d = new Date((ts + offset) * 1000);
      return {
        day: d.getUTCDate(),
        month: d.getUTCMonth(),
        year: d.getUTCFullYear(),
        weekday: d.getUTCDay(),
        time: String(d.getUTCHours()).padStart(2, "0") + ":" + String(d.getUTCMinutes()).padStart(2, "0")
      };
    }

    function range(key) {
      const now = Date.now() / 1000;
      const today = dayStart(now);
      const weekday = new Date((today + offset) * 1000).getUTCDay();
      // Fin de semana: de viernes a domingo. De viernes a domingo es el actual; si no, el próximo.
      const friday = weekday === 5 || weekday === 6 || weekday === 0
        ? today - ((weekday + 2) % 7) * DAY
        : today + (5 - weekday) * DAY;

      if (key === "hoy") return [today, today + DAY];
      if (key === "finde") return [friday, friday + 3 * DAY];
      if (key === "semana") return [today, today + 7 * DAY];
      return [today, Infinity];
    }

    // Primero lo que empieza dentro del período; después lo que ya está en curso (muestras).
    function select(key) {
      const r = range(key);
      const from = Math.max(r[0], Date.now() / 1000);
      const starting = [];
      const ongoing = [];

      events.forEach(function (event) {
        if (event.start >= r[1] || event.end <= from) return;
        (event.start >= r[0] ? starting : ongoing).push(event);
      });

      starting.sort(function (a, b) { return a.start - b.start; });
      ongoing.sort(function (a, b) { return a.end - b.end; });
      return starting.concat(ongoing);
    }

    function render(key) {
      const r = range(key);
      const selected = select(key).slice(0, limit);

      buttons.forEach(function (button) {
        button.setAttribute("aria-pressed", button.dataset.filter === key ? "true" : "false");
      });

      clearList();
      if (!selected.length) {
        showMessage("No hay actividades para este período.");
        return;
      }

      hideMessage();
      selected.forEach(function (event) {
        list.appendChild(card(event, r));
      });
      if (selected.length > PER_ROW) carousel.start(selected.length);
    }

    function card(event, r) {
      const item = el("li", "cca-item");
      const href = event.url || (moreLink ? moreLink.href : "");
      const link = el(href ? "a" : "div", "cca-card");
      if (href) {
        link.href = href;
        link.target = "_blank";
        link.rel = "noopener";
      }

      // La etiqueta va sobre la imagen; sin imágenes queda arriba del título.
      const kicker = event.kicker || (event.categories || [])[0];
      const body = el("span", "cca-body");

      if (showImages) {
        const media = el("span", "cca-media");
        if (event.image) {
          const img = document.createElement("img");
          img.src = event.image;
          img.alt = "";
          img.loading = "lazy";
          img.decoding = "async";
          img.addEventListener("error", function () {
            img.remove();
            media.classList.add("cca-media--empty");
          });
          media.appendChild(img);
        } else {
          media.classList.add("cca-media--empty");
        }
        if (kicker) media.appendChild(el("span", "cca-kicker", kicker));
        link.appendChild(media);
      } else if (kicker) {
        body.appendChild(el("span", "cca-kicker", kicker));
      }

      body.appendChild(el("h3", "cca-name", event.title));
      body.appendChild(el("span", "cca-when", when(event, r)));
      if (event.venue) body.appendChild(el("span", "cca-where", event.venue));
      link.appendChild(body);

      if (href) link.appendChild(el("span", "screen-reader-text", "(se abre en una pestaña nueva)"));
      item.appendChild(link);
      return item;
    }

    // "Jue 29 oct · 20:00 h", "Hasta el 13 nov" o "29 oct al 2 nov".
    function when(event, r) {
      const lastMoment = event.allDay ? event.end - 1 : Math.max(event.start, event.end - 1);

      if (dayStart(event.start) === dayStart(lastMoment)) {
        const label = dayLabel(event.start, true);
        return event.allDay ? label : label + " · " + parts(event.start).time + " h";
      }
      if (event.start < r[0]) {
        return "Hasta el " + dayLabel(lastMoment, false);
      }
      return dayLabel(event.start, false) + " al " + dayLabel(lastMoment, false);
    }

    function dayLabel(ts, withWeekday) {
      const p = parts(ts);
      let label = p.day + " " + MONTHS[p.month];
      if (p.year !== parts(Date.now() / 1000).year) label += " " + p.year;
      return withWeekday ? WEEKDAYS[p.weekday] + " " + label : label;
    }

    function clearList() {
      carousel.stop();
      list.innerHTML = "";
      list.setAttribute("aria-busy", "false");
    }

    function showMessage(text) {
      if (!message) return;
      message.textContent = text;
      message.hidden = false;
    }

    function hideMessage() {
      if (message) message.hidden = true;
    }
  }

  // Carrusel infinito: al final de la lista van copias de las tarjetas, y al
  // llegar a ellas se vuelve sin animación al principio, que se ve igual.
  // Avanza solo y se detiene con el mouse encima, con el foco adentro, al
  // tocarlo o con el botón de pausa.
  function createCarousel(root, list) {
    const reduceMotion = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    const stage = el("div", "cca-stage");
    const viewport = el("div", "cca-viewport");
    const prev = navButton("cca-prev", "Actividad anterior", "M15 6l-6 6 6 6");
    const next = navButton("cca-next", "Actividad siguiente", "M9 6l6 6-6 6");
    const bar = el("div", "cca-bar");
    const toggle = el("button", "cca-toggle");
    const dots = el("span", "cca-dots");
    const hold = { hover: false, focus: false, touch: false };

    let count = 0;
    let index = 0;
    let timer = 0;
    let paused = reduceMotion;
    let touchX = 0;
    let touchY = 0;

    list.parentNode.insertBefore(stage, list);
    viewport.appendChild(list);
    stage.appendChild(viewport);
    stage.appendChild(prev);
    stage.appendChild(next);

    toggle.type = "button";
    dots.setAttribute("aria-hidden", "true");
    bar.appendChild(toggle);
    bar.appendChild(dots);
    stage.parentNode.insertBefore(bar, stage.nextSibling);

    prev.addEventListener("click", function () { go(-1); restart(); });
    next.addEventListener("click", function () { go(1); restart(); });
    toggle.addEventListener("click", function () {
      paused = !paused;
      updateToggle();
      restart();
    });
    dots.addEventListener("click", function (e) {
      const dot = e.target.closest(".cca-dot");
      if (!dot) return;
      index = Array.prototype.indexOf.call(dots.children, dot);
      apply(true);
      restart();
    });

    stage.addEventListener("mouseenter", function () { hold.hover = true; });
    stage.addEventListener("mouseleave", function () { hold.hover = false; });
    stage.addEventListener("focusin", function (e) {
      // Solo pausa el foco de teclado: un clic en las flechas no lo frena.
      hold.focus = focusVisible(e.target);
      // Con teclado, la tarjeta que recibe el foco se trae a la vista.
      const item = e.target.closest(".cca-item");
      const i = Array.prototype.indexOf.call(list.children, item);
      if (count && i >= 0 && i < count && !isVisible(i)) {
        index = i;
        apply(true);
      }
    });
    // Si el foco pasa a otra tarjeta, el focusin que sigue lo vuelve a marcar.
    stage.addEventListener("focusout", function () { hold.focus = false; });

    viewport.addEventListener("touchstart", function (e) {
      hold.touch = true;
      touchX = e.touches[0].clientX;
      touchY = e.touches[0].clientY;
    }, { passive: true });
    viewport.addEventListener("touchend", function (e) {
      hold.touch = false;
      const dx = e.changedTouches[0].clientX - touchX;
      const dy = e.changedTouches[0].clientY - touchY;
      if (count && Math.abs(dx) > SWIPE_PX && Math.abs(dx) > Math.abs(dy)) {
        go(dx < 0 ? 1 : -1);
        restart();
      }
    }, { passive: true });

    if (window.ResizeObserver) {
      new ResizeObserver(function () { if (count) apply(false); }).observe(viewport);
    } else {
      window.addEventListener("resize", function () { if (count) apply(false); });
    }

    function start(total) {
      count = total;
      index = 0;
      Array.prototype.slice.call(list.children, 0, total).forEach(function (item) {
        const copy = item.cloneNode(true);
        copy.classList.add("cca-clone");
        copy.setAttribute("aria-hidden", "true");
        copy.inert = true;
        copy.querySelectorAll("a").forEach(function (link) { link.tabIndex = -1; });
        list.appendChild(copy);
      });

      dots.innerHTML = "";
      for (let i = 0; i < total; i++) dots.appendChild(el("span", "cca-dot"));

      root.classList.add("cca-agenda--carousel");
      updateToggle();
      apply(false);
      restart();
    }

    function stop() {
      count = 0;
      clearInterval(timer);
      root.classList.remove("cca-agenda--carousel");
      list.style.transform = "";
      dots.innerHTML = "";
    }

    function go(delta) {
      if (!count) return;
      if (index >= count) {
        index -= count;
        apply(false);
      }
      if (delta < 0 && index === 0) {
        index = count;
        apply(false);
      }
      index += delta;
      apply(!reduceMotion);
    }

    function step() {
      const items = list.children;
      // Con decimales: offsetLeft redondea y el error se acumula tarjeta a tarjeta.
      return items.length > 1 ? items[1].getBoundingClientRect().left - items[0].getBoundingClientRect().left : 0;
    }

    function perView() {
      const s = step();
      return s ? Math.max(1, Math.floor((viewport.clientWidth + 1) / s)) : 1;
    }

    function isVisible(i) {
      const shown = perView();
      return (i >= index && i < index + shown) || (i + count >= index && i + count < index + shown);
    }

    function apply(animate) {
      if (!animate) list.classList.add("cca-still");
      list.style.transform = "translate3d(" + (-index * step()) + "px, 0, 0)";
      if (!animate) {
        void list.offsetWidth;
        list.classList.remove("cca-still");
      }

      // Las flechas quedan a media altura de la imagen.
      const media = list.querySelector(".cca-media");
      stage.style.setProperty("--cca-nav-top", media ? media.offsetHeight / 2 + "px" : "50%");

      Array.prototype.forEach.call(dots.children, function (dot, i) {
        dot.classList.toggle("is-active", i === index % count);
      });
    }

    function restart() {
      clearInterval(timer);
      if (!count || paused) return;
      timer = setInterval(function () {
        // La vista previa de Ajustes descarta el bloque en cada cambio.
        if (!root.isConnected) {
          clearInterval(timer);
          return;
        }
        if (!hold.hover && !hold.focus && !hold.touch && !document.hidden) go(1);
      }, AUTOPLAY_MS);
    }

    function updateToggle() {
      toggle.setAttribute("aria-label", paused ? "Reanudar el carrusel" : "Pausar el carrusel");
      toggle.setAttribute("aria-pressed", paused ? "true" : "false");
      toggle.innerHTML = paused
        ? '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M8 5.5v13l10.5-6.5z" fill="currentColor"/></svg>'
        : '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M7 5h3.5v14H7zM13.5 5H17v14h-3.5z" fill="currentColor"/></svg>';
    }

    return { start: start, stop: stop };
  }

  function navButton(className, label, path) {
    const button = el("button", "cca-nav " + className);
    button.type = "button";
    button.setAttribute("aria-label", label);
    button.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="' + path + '" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    return button;
  }

  function focusVisible(node) {
    try {
      return node.matches(":focus-visible");
    } catch (e) {
      return true;
    }
  }

  function normalize(text) {
    return String(text).trim().toLowerCase().normalize("NFD").replace(/[̀-ͯ]/g, "");
  }

  function el(tag, className, text) {
    const node = document.createElement(tag);
    node.className = className;
    if (text !== undefined) node.textContent = text;
    return node;
  }

  // La vista previa de Ajustes vuelve a montar el bloque con cada cambio.
  window.ccaAgenda = { init: init };

  document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".cca-agenda[data-cca-agenda]").forEach(init);
  });
})();
