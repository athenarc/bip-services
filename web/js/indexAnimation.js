function animateCounter(el, target, duration = 2100) {
    let start = 0;
    const stepTime = Math.max(Math.floor(duration / target), 20);
    const increment = Math.ceil(target / (duration / stepTime));

    const counter = setInterval(() => {
        start += increment;
        if (start >= target) {
            start = target;
            clearInterval(counter);
        }
        el.innerText = start.toLocaleString(); // formatted with commas
    }, stepTime);
}

document.addEventListener("DOMContentLoaded", () => {
    // Animate blocks
    const animatedBlocks = document.querySelectorAll('.bip-animate');
    animatedBlocks.forEach((block, index) => {
        setTimeout(() => {
            block.classList.add('visible');
        }, index * 200); // staggered animation
    });

    // Animate counter
    const counterEl = document.getElementById('counter-number');
    if (counterEl) {
        const target = parseInt(counterEl.getAttribute('data-target'));
        animateCounter(counterEl, target);
    }
});

