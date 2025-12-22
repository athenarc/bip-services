// Only define the function once per page load
if (typeof switchAnnotationTab === 'undefined') {
    function switchAnnotationTab(containerId, tabId, contentId) {
        var container = document.getElementById(containerId);
        var tabElement = document.getElementById(tabId);
        var content = document.getElementById(contentId);
        
        if (!container || !tabElement || !content) {
            return;
        }
        
        // Check if this tab is already active
        var isAlreadyActive = tabElement.classList.contains('active');
        
        // Hide all content with fade-out animation
        var allContents = container.querySelectorAll('.annotation-content');
        allContents.forEach(function(contentEl) {
            contentEl.classList.remove('show');

            if (!contentEl.classList.contains('show')) {
                contentEl.style.display = 'none';
            }
        });
        
        // Remove active class from all tabs - CSS will handle the default state
        var allTabs = tabElement.parentElement.querySelectorAll('.annotation-tab');
        allTabs.forEach(function(tab) {
            tab.classList.remove('active');
            // Clear any inline styles to let CSS take over
            tab.style.color = '';
            tab.style.backgroundColor = '';
            tab.style.borderColor = '';
            
            // Reset icon and span colors to default
            var icon = tab.querySelector('i.fa-tag');
            if (icon) {
                var tagColor = tab.getAttribute('data-tag-color');
                if (tagColor) {
                    icon.style.color = tagColor;
                } else {
                    icon.style.color = '';
                }
            }
            var spans = tab.querySelectorAll('span');
            spans.forEach(function(span) {
                // Reset count span to #666, other spans to default
                if (span.textContent.match(/^\(\d+\)$/)) {
                    span.style.color = '#666';
                } else {
                    span.style.color = '';
                }
            });
        });
        
        // If clicking the same tab, toggle it off (already cleared above)
        if (isAlreadyActive) {
            return;
        }
        
        // Show selected content with fade-in animation
        content.style.display = 'inline-block';
        // Use requestAnimationFrame to ensure display is set before adding class
        requestAnimationFrame(function() {
            content.classList.add('show');
        });
        
        // Mark tab as active with smooth transition
        tabElement.classList.add('active');
    }
}

