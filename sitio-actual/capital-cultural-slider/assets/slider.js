document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".cc-slider[data-slider]").forEach(function (slider) {
    const slides = slider.querySelectorAll(".cc-slide");
    const prev = slider.querySelector(".cc-prev");
    const next = slider.querySelector(".cc-next");
    const dotsWrap = slider.querySelector(".cc-dots");

    if (!slides.length) return;

    let index = 0;
    const duration = 20000;
    let interval = null;

    if (dotsWrap) {
      dotsWrap.innerHTML = "";
      slides.forEach(function (_, i) {
        const dot = document.createElement("button");
        dot.type = "button";
        dot.className = "cc-dot";
        dot.setAttribute("aria-label", "Ir al slide " + (i + 1));
        dot.addEventListener("click", function () {
          goToSlide(i);
        });
        dotsWrap.appendChild(dot);
      });
    }

    const dots = slider.querySelectorAll(".cc-dot");

    function startProgress() {
      slides.forEach(function (slide) {
        const prog = slide.querySelector(".cc-progress");
        if (prog) {
          prog.style.transition = "none";
          prog.style.width = "0%";
        }
      });

      const progress = slides[index].querySelector(".cc-progress");
      if (progress) {
        setTimeout(function () {
          progress.style.transition = "width " + duration + "ms linear";
          progress.style.width = "100%";
        }, 50);
      }
    }

    function setActive(i) {
      slides.forEach(function (slide) { slide.classList.remove("active"); });
      dots.forEach(function (dot) { dot.classList.remove("active"); });

      index = i;
      slides[index].classList.add("active");
      if (dots[index]) dots[index].classList.add("active");
      startProgress();
    }

    function goToSlide(i) {
      setActive(i);
      restartAuto();
    }

    function nextSlide() { goToSlide((index + 1) % slides.length); }
    function prevSlide() { goToSlide((index - 1 + slides.length) % slides.length); }

    function startAuto() {
      if (slides.length < 2) return;
      stopAuto();
      interval = setInterval(nextSlide, duration);
    }

    function stopAuto() {
      if (interval) clearInterval(interval);
      interval = null;
    }

    function restartAuto() {
      stopAuto();
      startAuto();
    }

    if (next) next.addEventListener("click", nextSlide);
    if (prev) prev.addEventListener("click", prevSlide);

    let startX = 0;
    slider.addEventListener("touchstart", function (e) {
      startX = e.touches[0].clientX;
    }, { passive: true });

    slider.addEventListener("touchend", function (e) {
      const diff = startX - e.changedTouches[0].clientX;
      if (Math.abs(diff) > 50) {
        diff > 0 ? nextSlide() : prevSlide();
      }
    }, { passive: true });

    setActive(0);
    startAuto();
  });
});
