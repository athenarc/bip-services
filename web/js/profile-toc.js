if ('scrollRestoration' in history) history.scrollRestoration = 'manual';

document.addEventListener('DOMContentLoaded', function () {
    var profileContent = document.getElementById('profile-content');
    var tocList = document.getElementById('profile-toc');
    if (!profileContent || !tocList) return;

    /**
     * Handles responsive behavior for TOC sidebar
     * Hides sidebar when window is too narrow to prevent overlap with content
     */
    function updateSidebarVisibility() {
        var tocWrapper = document.getElementById('profile-toc-wrap');
        var sidebar = tocWrapper && tocWrapper.querySelector('.sidebar');
        if (!sidebar) return;

        var hasItems = tocList.querySelectorAll('li').length > 0;
        var windowWidth = window.innerWidth || document.documentElement.clientWidth;
        var isWideEnough = windowWidth >= 1200;

        sidebar.style.display = (hasItems && isWideEnough) ? '' : 'none';
    }

    /**
     * Builds the table of contents from section divider headings
     * Creates nested lists based on heading levels (h1, h2, h3)
     */
    function buildProfileToc() {
        tocList.innerHTML = '';
        var headings = profileContent.querySelectorAll('.section-divider h1, .section-divider h2, .section-divider h3');
        if (!headings.length) return;

        var currentLevel = 1;
        var ulStack = [tocList];

        headings.forEach(function(heading) {
            var divider = heading.closest('.section-divider');
            if (!divider || !divider.id) return;

            // Extract title: prefer tooltip title, fallback to text content
            var span = heading.querySelector('span[role="button"][data-toggle="popover"]');
            var title = span ? (span.getAttribute('title') || '').trim() : '';
            if (!title) title = (heading.textContent || '').replace(/\s+/g, ' ').trim();
            if (!title || /\bmissing\s+works\b/i.test(title)) return;

            // Calculate heading level (1-3, clamped)
            var level = Math.min(3, Math.max(1, parseInt(heading.tagName.replace('H', ''), 10) || 1));

            // Create nested <ul> elements when going deeper
            while (currentLevel < level) {
                var ul = document.createElement('ul');
                ulStack[ulStack.length - 1].appendChild(ul);
                ulStack.push(ul);
                currentLevel++;
            }
            // Close nested <ul> elements when going shallower
            while (currentLevel > level) {
                ulStack.pop();
                currentLevel--;
            }

            // Create TOC item with link
            var li = document.createElement('li');
            li.className = 'toc-item level-' + level;
            var link = document.createElement('a');
            link.className = 'toc-link';
            link.textContent = title;
            link.href = '#' + divider.id;
            li.appendChild(link);
            ulStack[ulStack.length - 1].appendChild(li);
        });
    }

    // Build TOC on page load
    buildProfileToc();
    updateSidebarVisibility();

    // Watch for DOM changes and rebuild TOC
    var scheduled = false;
    var observer = new MutationObserver(function() {
        if (scheduled) return;
        scheduled = true;
        setTimeout(function() {
            scheduled = false;
            buildProfileToc();
            updateSidebarVisibility();
        }, 150);
    });
    observer.observe(profileContent, { childList: true, subtree: true, characterData: true });

    // Handle resize events with debouncing
    var resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(updateSidebarVisibility, 100);
    });
});
