const modalTrigger = document.querySelectorAll('.modal-trigger');
const modalClose = document.querySelector('.modal-close');

const modalOverlay = document.querySelector('.modal-overlay');
const modalContent = document.querySelector('.modal-content');

function toggleModal(event, element) {
    event.preventDefault();
    modalOverlay.classList.toggle('active');
    modalContent.classList.toggle('active');
}

modalTrigger.forEach( element => {
    // console.log(element);
    element.addEventListener('click', toggleModal);
});

modalClose.addEventListener('click', toggleModal);