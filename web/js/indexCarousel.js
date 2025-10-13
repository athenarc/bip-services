let currentSlide = 0;
let totalSlides = 3;
let interval;
let autoplay = true;

const updateCarousel = (index) => {
    const inner = document.querySelector('.bip-carousel-inner');
    const dots = document.querySelectorAll('.dot');

    if (!inner || dots.length === 0) return;
    
    inner.style.transform = `translateX(-${index * 100}%)`;

    dots.forEach(dot => dot.classList.remove('active'));
    if (dots[index]) {
        dots[index].classList.add('active');
    }
};

function nextSlide() {
    if (!autoplay) return;
    currentSlide = (currentSlide + 1) % totalSlides;
    updateCarousel(currentSlide);
}

document.addEventListener("DOMContentLoaded", () => {
    // Force initial alignment to the first slide
    updateCarousel(0);

    // Start autoplay
    interval = setInterval(nextSlide, 3000);

    // Dot click handlers
    document.querySelectorAll('.dot').forEach(dot => {
        dot.addEventListener('click', function () {
            const index = parseInt(this.getAttribute('data-slide'));
            currentSlide = index;
            autoplay = false;
            updateCarousel(index);
        });
    });
});
