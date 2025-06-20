document.addEventListener("DOMContentLoaded", function () {
    const OFFSET = 70; // Adjust this value to your needs

    function scrollToTarget(hash) {
        var target = document.querySelector(hash);
        if (target) {
            setTimeout(function () {
                const top = target.getBoundingClientRect().top + window.pageYOffset - OFFSET;
                window.scrollTo({ top, behavior: "smooth" });
            }, 300); // Add delay to ensure Swagger UI is loaded
        }
    }

    // Handle anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener("click", function (e) {
            e.preventDefault();
            var hash = this.hash;

            if (hash) {
                scrollToTarget(hash);

                // Update URL without triggering scroll
                if (history.pushState) {
                    history.pushState(null, null, hash);
                } else {
                    location.hash = hash;
                }
            }
        });
    });

    // Handle initial page load with hash
    if (window.location.hash) {
        scrollToTarget(window.location.hash);
    }

    // Listen for hash changes
    window.addEventListener('hashchange', function () {
        if (window.location.hash) {
            scrollToTarget(window.location.hash);
        }
    });
});