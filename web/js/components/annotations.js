// Storage key for annotation expansion state
var ANNOTATION_EXPANDED_STORAGE_KEY = 'bip_annotations_expanded';

// Function to save annotation expansion state to localStorage
function saveAnnotationExpansionState(isExpanded) {
    try {
        localStorage.setItem(ANNOTATION_EXPANDED_STORAGE_KEY, isExpanded ? 'true' : 'false');
    } catch (e) {
        // localStorage might not be available, ignore
    }
}

// Function to get annotation expansion state from localStorage
function getAnnotationExpansionState() {
    try {
        var stored = localStorage.getItem(ANNOTATION_EXPANDED_STORAGE_KEY);
        return stored === 'true';
    } catch (e) {
        // localStorage might not be available, default to false
        return false;
    }
}

// Function to restore annotation expansion state on page load
function restoreAnnotationExpansionState() {
    var isExpanded = getAnnotationExpansionState();
    if (!isExpanded) {
        return; // Default state is collapsed, so nothing to do
    }
    
    // Find all annotation tab containers on the page
    var allContainers = document.querySelectorAll('[id^="res_"][id$="_annot_tabs"]');
    if (!allContainers || allContainers.length === 0) {
        return;
    }
    
    // Expand all annotation tabs
    allContainers.forEach(function(container) {
        var allContents = container.querySelectorAll('.annotation-content');
        var parentElement = container.parentElement;
        var tabsContainer = parentElement ? parentElement.querySelector('.annotation-tabs') : null;
        var allTabs = tabsContainer ? tabsContainer.querySelectorAll('.annotation-tab') : [];
        
        // Expand all content
        allContents.forEach(function(contentEl) {
            contentEl.style.display = 'inline-block';
            requestAnimationFrame(function() {
                contentEl.classList.add('show');
            });
        });
        
        // Mark all tabs as active
        allTabs.forEach(function(tab) {
            tab.classList.add('active');
        });
    });
    
    // Update global button state (if function exists)
    if (typeof updateGlobalExpandAllButtonState !== 'undefined') {
        updateGlobalExpandAllButtonState();
    }
}

// Restore state when DOM is ready
// Use a small delay to ensure all annotations are rendered
function initAnnotationExpansionState() {
    // Wait a bit for DOM to be fully ready and annotations rendered
    setTimeout(function() {
        restoreAnnotationExpansionState();
    }, 100);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAnnotationExpansionState);
} else {
    // DOM is already ready
    initAnnotationExpansionState();
}

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
        
        // Hide all content
        var allContents = container.querySelectorAll('.annotation-content');
        allContents.forEach(function(contentEl) {
            contentEl.classList.remove('show');
            contentEl.style.display = 'none';
        });
        
        // Remove active class from all tabs - CSS will handle the default state
        var allTabs = tabElement.parentElement.querySelectorAll('.annotation-tab');
        allTabs.forEach(function(tab) {
            tab.classList.remove('active');
            // Clear any inline styles to let CSS take over
            tab.style.color = '';
            tab.style.backgroundColor = '';
            tab.style.borderColor = '';
            
            // Reset span colors (skip badges)
            var spans = tab.querySelectorAll('span');
            spans.forEach(function(span) {
                if (!span.classList.contains('badge')) {
                    span.style.color = '';
                }
            });
        });
        
        // If clicking the same tab, toggle it off (already cleared above)
        if (isAlreadyActive) {
            // Update global expand-all button state when deselecting
            updateGlobalExpandAllButtonState();
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
        
        // Update global expand-all button state
        updateGlobalExpandAllButtonState();
    }
}

// Helper function to update global expand-all button state based on current tab visibility across all results
if (typeof updateGlobalExpandAllButtonState === 'undefined') {
    function updateGlobalExpandAllButtonState() {
        var globalBtn = document.getElementById('expandAllAnnotationsBtn');
        if (!globalBtn) {
            return;
        }
        
        // Find all annotation tab containers on the page
        var allContainers = document.querySelectorAll('[id^="res_"][id$="_annot_tabs"]');
        var hasAnyVisibleContent = false;
        
        allContainers.forEach(function(container) {
            var contents = container.querySelectorAll('.annotation-content');
            contents.forEach(function(contentEl) {
                if (contentEl.classList.contains('show') || contentEl.style.display === 'inline-block') {
                    hasAnyVisibleContent = true;
                }
            });
        });
        
        // Update global button text and icon based on current state
        var globalText = globalBtn.querySelector('.expand-all-global-text');
        var globalIcon = globalBtn.querySelector('i');
        if (globalText && globalIcon) {
            if (hasAnyVisibleContent) {
                globalText.textContent = 'Collapse all';
                globalIcon.className = 'fa-solid fa-angles-up';
                globalBtn.classList.add('active');
            } else {
                globalText.textContent = 'Expand all';
                globalIcon.className = 'fa-solid fa-angles-down';
                globalBtn.classList.remove('active');
            }
        }
    }
}

// Function to toggle all annotation tabs across all result items on the page
if (typeof toggleAllAnnotationTabsGlobal === 'undefined') {
    function toggleAllAnnotationTabsGlobal() {
        // Find all annotation tab containers on the page
        var allContainers = document.querySelectorAll('[id^="res_"][id$="_annot_tabs"]');
        
        if (!allContainers || allContainers.length === 0) {
            return;
        }
        
        // Check if any content is currently visible across all containers
        var hasAnyVisibleContent = false;
        allContainers.forEach(function(container) {
            var contents = container.querySelectorAll('.annotation-content');
            contents.forEach(function(contentEl) {
                if (contentEl.classList.contains('show') || contentEl.style.display === 'inline-block') {
                    hasAnyVisibleContent = true;
                }
            });
        });
        
        // Determine action: expand if none visible, collapse if any visible
        var shouldExpand = !hasAnyVisibleContent;
        
        // Save state to localStorage
        saveAnnotationExpansionState(shouldExpand);
        
        // Process each container
        allContainers.forEach(function(container) {
            var allContents = container.querySelectorAll('.annotation-content');
            // Find the parent element that contains both tabs and content
            // The tabs are in a sibling div with class 'annotation-tabs'
            var parentElement = container.parentElement;
            var tabsContainer = parentElement ? parentElement.querySelector('.annotation-tabs') : null;
            var allTabs = tabsContainer ? tabsContainer.querySelectorAll('.annotation-tab') : [];
            
            allContents.forEach(function(contentEl) {
                if (shouldExpand) {
                    // Expand: show content
                    contentEl.style.display = 'inline-block';
                    requestAnimationFrame(function() {
                        contentEl.classList.add('show');
                    });
                } else {
                    // Collapse: hide content
                    contentEl.classList.remove('show');
                    contentEl.style.display = 'none';
                }
            });
            
            // Update tab active states
            allTabs.forEach(function(tab) {
                if (shouldExpand) {
                    tab.classList.add('active');
                } else {
                    tab.classList.remove('active');
                    // Reset tab styles
                    tab.style.color = '';
                    tab.style.backgroundColor = '';
                    tab.style.borderColor = '';
                    var spans = tab.querySelectorAll('span');
                    spans.forEach(function(span) {
                        if (!span.classList.contains('badge')) {
                            span.style.color = '';
                        }
                    });
                }
            });
            
        });
        
        // Update global expand-all button
        var globalBtn = document.getElementById('expandAllAnnotationsBtn');
        if (globalBtn) {
            var globalText = globalBtn.querySelector('.expand-all-global-text');
            var globalIcon = globalBtn.querySelector('i');
            if (globalText && globalIcon) {
                if (shouldExpand) {
                    globalText.textContent = 'Collapse all';
                    globalIcon.className = 'fa-solid fa-angles-up';
                    globalBtn.classList.add('active');
                } else {
                    globalText.textContent = 'Expand all';
                    globalIcon.className = 'fa-solid fa-angles-down';
                    globalBtn.classList.remove('active');
                }
            }
        }
    }
}


