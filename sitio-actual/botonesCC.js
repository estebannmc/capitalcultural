(function () {
  function posicionarBarrasCB() {
    document.querySelectorAll('.cb-btn').forEach(function (btn) {
      var text = btn.querySelector('span');
      if (!text) return;

      var btnRect = btn.getBoundingClientRect();
      var textRect = text.getBoundingClientRect();

      var textStart = textRect.left - btnRect.left;
      var barPosition = Math.max(12, textStart - 17);

      btn.style.setProperty('--cb-bar-left', barPosition + 'px');
    });
  }

  function recalcularBarrasCB() {
    window.requestAnimationFrame(posicionarBarrasCB);
  }

  function initBarrasCB() {
    recalcularBarrasCB();

    if (document.fonts && document.fonts.ready) {
      document.fonts.ready.then(recalcularBarrasCB);
    }

    window.addEventListener('load', recalcularBarrasCB);
    window.addEventListener('resize', recalcularBarrasCB);
    document.addEventListener('mouseenter', recalcularBarrasCB, true);

    setTimeout(recalcularBarrasCB, 300);
    setTimeout(recalcularBarrasCB, 800);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initBarrasCB);
  } else {
    initBarrasCB();
  }

  if (window.jQuery) {
    window.jQuery(window).on('elementor/frontend/init', recalcularBarrasCB);
  }
})();