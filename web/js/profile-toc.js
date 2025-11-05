document.addEventListener('DOMContentLoaded', function () {
    var container = document.getElementById('profile-content');
    var tocRoot = document.getElementById('profile-toc');
    if (!container || !tocRoot) { return; }

    function slugify(text) {
        return String(text || '')
            .toLowerCase()
            .replace(/\s+/g, '-')
            .replace(/[^\w\-]+/g, '')
            .replace(/\-\-+/g, '-')
            .replace(/^-+/, '')
            .replace(/-+$/, '');
    }

    var idCounts = Object.create(null);
    function ensureId(headingEl, titleText) {
        if (headingEl.id) return headingEl.id;
        var base = slugify(titleText || headingEl.textContent || 'section') || 'section';
        var unique = base;
        var i = 2;
        while (document.getElementById(unique) || idCounts[unique]) {
            unique = base + '-' + i++;
        }
        idCounts[unique] = true;
        headingEl.id = unique;
        return unique;
    }

    function levelFromTag(tagName) {
        var n = parseInt(String(tagName).replace('H', ''), 10);
        if (isNaN(n)) n = 1;
        return Math.min(3, Math.max(1, n));
    }

    function getDividerTitle(h) {
        // When tooltip is enabled, heading contains a span[role=button] with title attr
        var span = h.querySelector('span[role="button"][data-toggle="popover"]');
        var byAttr = span && span.getAttribute('title');
        var text = (h.textContent || '').replace(/\s+/g,' ').trim();
        return (byAttr && byAttr.trim()) || text;
    }

    function buildProfileToc() {
        tocRoot.innerHTML = '';
        idCounts = Object.create(null);
        var headings = container.querySelectorAll('.section-divider h1, .section-divider h2, .section-divider h3');
        if (!headings.length) { return; }

        var currentLevel = 1;
        var ulStack = [tocRoot];

        headings.forEach(function(h) {
            var title = getDividerTitle(h);
            if (!title) return;
            if (/\bmissing\s+works\b/i.test(title)) return;

            var l = levelFromTag(h.tagName);
            var id = ensureId(h, title);

            while (currentLevel < l) {
                var newUl = document.createElement('ul');
                ulStack[ulStack.length - 1].appendChild(newUl);
                ulStack.push(newUl);
                currentLevel++;
            }
            while (currentLevel > l) {
                ulStack.pop();
                currentLevel--;
            }

            var li = document.createElement('li');
            li.className = 'toc-item level-' + l;
            var a = document.createElement('a');
            a.className = 'toc-link';
            a.textContent = title;
            a.href = '#' + id;
            li.appendChild(a);
            ulStack[ulStack.length - 1].appendChild(li);
        });
    }

    // Build now and on subsequent DOM changes
    buildProfileToc();
    var scheduled = false;
    var observer = new MutationObserver(function() {
        if (scheduled) return; scheduled = true;
        setTimeout(function(){ scheduled = false; buildProfileToc(); }, 150);
    });
    observer.observe(container, { childList: true, subtree: true, characterData: true });
});


