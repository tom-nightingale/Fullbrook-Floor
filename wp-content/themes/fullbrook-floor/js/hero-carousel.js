const carousel = document.querySelector('.carousel');
const carouselRod = document.querySelector('.carousel-1');
const carouselRene = document.querySelector('.carousel-2');

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


if (carouselRod) {
  var rodSlides = carouselRod.querySelectorAll('.carousel-image');

  var rodTotalSlides = rodSlides.length;
  var rodSlide = 0;
  // console.log(totalSlides);

  rodSlides[rodSlide].classList.add('active');

  function rotateRod() {
    rodSlides.forEach(slide => {
      slide.classList.remove('active');
    });

    if (rodSlide < rodTotalSlides) {
      rodSlides[rodSlide].classList.add('active');
      rodSlide++;
    }
    else {
      rodSlide = 0;
      rodSlides[rodSlide].classList.add('active');
      rodSlide++;
    }
  }

  setInterval(rotateRod, 4000);
}

if (carouselRene) {
  var reneSlides = carouselRene.querySelectorAll('.carousel-image');

  var reneTotalSlides = reneSlides.length;
  var reneSlide = 0;
  // console.log(totalSlides);

  reneSlides[reneSlide].classList.add('active');

  function rotateRene() {
    reneSlides.forEach(slide => {
      slide.classList.remove('active');
    });

    if (reneSlide < reneTotalSlides) {
      reneSlides[reneSlide].classList.add('active');
      reneSlide++;
    }
    else {
      reneSlide = 0;
      reneSlides[reneSlide].classList.add('active');
      reneSlide++;
    }
  }

  setInterval(rotateRene, 5000);
}