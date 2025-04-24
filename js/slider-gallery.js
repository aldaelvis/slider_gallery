(function () {
  // Función que inicializa cada slider.

  const sliderInstances = {};

  function initSlider(slider_id) {
    const slidesContainer = document.getElementById('slides-' + slider_id);
    const slides = slidesContainer.querySelectorAll('.slide-module');
    const thumbnailsContainer = document.getElementById('thumbnails-' + slider_id);
    let currentIndex = 0;

    // slidesContainer.style.width = (100 * slides.length) + '%';
    slides.forEach(slide => slide.style.flex = '0 0 100%');

    const thumbDivs = thumbnailsContainer.querySelectorAll('.thumbnail');
    thumbDivs.forEach((thumb, index) => {
      thumb.addEventListener('click', function () {
        currentIndex = index;
        updateSlider();
      });
    });

    function updateSlider() {
      const offset = -currentIndex * 100;
      slidesContainer.style.transform = 'translateX(' + offset + '%)';
      thumbDivs.forEach((thumb, index) => {
        thumb.classList.toggle('active', index === currentIndex);
      });
    }

    // Guarda el control de cada slider por su ID
    sliderInstances[slider_id] = {
      next() {
        currentIndex = (currentIndex + 1) % slides.length;
        updateSlider();
      },
      prev() {
        currentIndex = (currentIndex - 1 + slides.length) % slides.length;
        updateSlider();
      }
    };

    updateSlider();
  }

  document.addEventListener('DOMContentLoaded', function () {
    const sliders = document.querySelectorAll('[id^="slider-"]');
    sliders.forEach(function (slider) {
      const slider_id = slider.id.replace('slider-', '');
      initSlider(slider_id);
    });

    // Delegación de eventos para botones de navegación
    document.body.addEventListener('click', function (event) {
      const button = event.target.closest('[data-action][data-slider-id]');
      if (!button) return;

      const action = button.getAttribute('data-action');
      const slider_id = button.getAttribute('data-slider-id');

      const slider = sliderInstances[slider_id];
      if (!slider) return;

      if (action === 'next') {
        slider.next();
      } else if (action === 'prev') {
        slider.prev();
      }
    });
  });
})();
