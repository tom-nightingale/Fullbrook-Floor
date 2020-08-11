var carousel = document.querySelector('.hero-carousel');
if (carousel) {
  var slides = document.querySelectorAll('.carousel-image');

  var totalSlides = slides.length;
  var currentSlide = 0;

  slides[currentSlide].classList.add('active');

  function rotateSlide() {
    slides.forEach(slide => {
      slide.classList.remove('active');
    });

    if (currentSlide < totalSlides) {
      slides[currentSlide].classList.add('active');
      currentSlide++;
    }
    else {
      currentSlide = 0;
      slides[currentSlide].classList.add('active');
      currentSlide++;
    }
  }

  setInterval(rotateSlide, 3000);
}