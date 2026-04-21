document.addEventListener('DOMContentLoaded', () => {
    // Different offsets for different pages
    // IMPORTANT: Keep these in sync with:
    //  - scroll-margin-top in fixed-sidebar.css (indicators)
    //  - scroll-margin-top in profile-toc.css (profile) if you decide to match it
    const OFFSET_INDICATORS = 60; // Indicators page (keep legacy behavior)
    const OFFSET_DEFAULT = 70; // Data, Help, About, etc. (match "main" working behavior)
    const OFFSET_PROFILE = 100; // Scholar profile TOC (original behavior)

    // Delay is important on pages with heavy widgets (e.g. Swagger UI on Data page)
    // so that layout has stabilized before we compute target positions.
    const DEFAULT_DELAY_MS = document.getElementById('swagger-ui') ? 300 : 0;

    let isScrolling = false; // Flag to prevent double scrolling

    function getOffsetForTarget(target) {
        if (!target) { return OFFSET_DEFAULT; }

        // Scholar profile page
        if (target.closest('#profile-content')) {
            return OFFSET_PROFILE;
        }

        // Indicators page (keep legacy behavior)
        if (target.closest('#indicators')) {
            return OFFSET_INDICATORS;
        }

        // Help page subsections use same behavior as indicators items
        if (target.closest('#help') && target.tagName === 'H4') {
            return OFFSET_INDICATORS;
        }

        // All other pages (Data, Help, About, etc.)
        return OFFSET_DEFAULT;
    }

    function scrollToTarget(hash, options) {
        if (isScrolling) { return; } // Prevent double scroll

        const target = document.querySelector(hash);
        if (!target) { return; }

        const preventHashUpdate = options && options.preventHashUpdate;
        const delayMs = (options && typeof options.delayMs === 'number')
            ? options.delayMs
            : DEFAULT_DELAY_MS;

        isScrolling = true;

        setTimeout(() => {
            const offset = getOffsetForTarget(target);
            const top = target.getBoundingClientRect().top + window.pageYOffset - offset;

            window.scrollTo({ top, behavior: 'smooth' });

            // Reset flag after scroll completes (smooth scroll takes ~500ms)
            setTimeout(() => {
                isScrolling = false;
            }, 600);

            // Update URL without triggering native scroll (only if not prevented)
            if (!preventHashUpdate && history.pushState) {
                history.pushState(null, null, hash);
            }
        }, delayMs);
    }

    // Handle anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const hash = this.hash;

            if (hash) {
                // Clicks: allow hash update, apply delay when needed (e.g. Swagger)
                scrollToTarget(hash, { preventHashUpdate: false });
            }
        });
    });

    // Handle initial page load with hash (skip for profile pages)
    if (window.location.hash && !document.getElementById('profile-content')) {
        // Initial load: prevent hash re-update, apply delay if needed
        scrollToTarget(window.location.hash, { preventHashUpdate: true });
    }

    // Listen for hash changes (but only if not already scrolling)
    window.addEventListener('hashchange', () => {
        if (window.location.hash && !isScrolling) {
            // Hash already updated; just scroll (with possible delay)
            scrollToTarget(window.location.hash, { preventHashUpdate: true });
        }
    });
});
