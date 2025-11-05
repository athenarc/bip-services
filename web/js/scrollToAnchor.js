document.addEventListener("DOMContentLoaded", function () {
    // Different offsets for different pages
    const OFFSET_INDICATORS = 60; // For indicators page
    const OFFSET_PROFILE = 100; // For profile TOC - adjust this to change where profile anchors stop

    var isScrolling = false; // Flag to prevent double scrolling

    function getOffsetForTarget(target) {
        // Check if target is inside profile-content (profile TOC)
        if (target && target.closest('#profile-content')) {
            return OFFSET_PROFILE;
        }
        // Default to indicators offset
        return OFFSET_INDICATORS;
    }

    function scrollToTarget(hash, preventHashUpdate) {
        if (isScrolling) return; // Prevent double scroll
        
        var target = document.querySelector(hash);
        if (target) {
            isScrolling = true;
            const offset = getOffsetForTarget(target);
            const top = target.getBoundingClientRect().top + window.pageYOffset - offset;
            
            window.scrollTo({ top, behavior: "smooth" });
            
            // Reset flag after scroll completes (smooth scroll takes ~500ms)
            setTimeout(function() {
                isScrolling = false;
            }, 600);
            
            // Update URL without triggering native scroll (only if not prevented)
            if (!preventHashUpdate && history.pushState) {
                history.pushState(null, null, hash);
            }
        }
    }

    // Handle anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener("click", function (e) {
            e.preventDefault();
            var hash = this.hash;

            if (hash) {
                scrollToTarget(hash, false); // false = allow hash update
            }
        });
    });

    // Handle initial page load with hash
    if (window.location.hash) {
        scrollToTarget(window.location.hash, true); // true = prevent hash update on initial load
    }

    // Listen for hash changes (but only if not already scrolling)
    window.addEventListener('hashchange', function () {
        if (window.location.hash && !isScrolling) {
            scrollToTarget(window.location.hash, true); // true = hash already updated
        }
    });
});