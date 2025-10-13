document.addEventListener("DOMContentLoaded", () => {
    const animatedBlocks = document.querySelectorAll('.bip-animate');
    animatedBlocks.forEach((block, index) => {
        setTimeout(() => {
            block.classList.add('visible');
        }, index * 200);
    });

    if (typeof animateIndicators === 'function') {
        animateIndicators(); // Trigger counter animation
    }
});
