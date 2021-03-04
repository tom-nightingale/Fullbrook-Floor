// --------------------------------------------------------------------------------------------------
// SIMPLE CAROUSEL with left/right buttons
// Clicking right arrow button will scroll carousel onto the next carousel item
// User can also swipe/scroll
// --------------------------------------------------------------------------------------------------
var controlIndex = 0;
var autoIndex = 0;
var carouselItems = document.querySelectorAll('.carousel-item');
var totalItems = carouselItems.length;

var carouselArrows = document.querySelectorAll(".carousel-arrow");

carouselArrows.forEach((arrow) => {
	arrow.addEventListener("click", function() {
        moveCarousel(arrow, arrow.classList);
	})

    arrow.addEventListener("mousedown", function() {
        clearInterval(autoScroll);
    })
});

function moveCarousel(arrow, arrowClass) {

    arrow.parentNode.parentNode.querySelector('.carousel-arrow-left').classList.add('opacity-100');

    var carouselContainer = arrow.parentNode.parentNode.querySelector('.carousel-container');
    var carouselItem = arrow.parentNode.parentNode.querySelectorAll('.carousel-item');
    var x;

    if (arrowClass.contains('carousel-arrow-right')) {
        x = (carouselItem[0].offsetWidth) + carouselContainer.scrollLeft;
        carouselContainer.scrollTo({
            left: x,
            behavior: "smooth",
        });
        controlIndex++;
    } else {
        x = (carouselItem[0].offsetWidth) - carouselContainer.scrollLeft;
        carouselContainer.scrollTo({
            left: -x,
            behavior: "smooth",
        });
        controlIndex--;
    }
}

var autoScroll = setInterval(function() {
    if(autoIndex >= 0 && autoIndex <= totalItems) {
        document.querySelector('.carousel-arrow-right').click();
    }
    autoIndex++;
}, 5000);

