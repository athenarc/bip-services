// Storage key for annotation type selection state (now supports multiple selections)
var ANNOTATION_TYPE_SELECTED_STORAGE_KEY = 'bip_annotation_type_selected';

// Function to save selected annotation types to localStorage (array of IDs)
function saveSelectedAnnotationTypes(annotationIds) {
    try {
        if (!annotationIds || annotationIds.length === 0) {
            localStorage.removeItem(ANNOTATION_TYPE_SELECTED_STORAGE_KEY);
        } else {
            localStorage.setItem(ANNOTATION_TYPE_SELECTED_STORAGE_KEY, JSON.stringify(annotationIds));
        }
    } catch (e) {
        // localStorage might not be available, ignore
    }
}

// Function to get selected annotation types from localStorage (returns array of IDs)
function getSelectedAnnotationTypes() {
    try {
        var stored = localStorage.getItem(ANNOTATION_TYPE_SELECTED_STORAGE_KEY);
        if (stored) {
            var parsed = JSON.parse(stored);
            // Ensure we return an array
            if (Array.isArray(parsed)) {
                return parsed;
            }
            // If it's a number (old format), convert to array
            if (typeof parsed === 'number') {
                return [parsed];
            }
            return [];
        }
        return [];
    } catch (e) {
        // localStorage might not be available, default to empty array
        return [];
    }
}

// Function to toggle a single annotation type in the selection
function toggleSelectedAnnotationType(annotationId) {
    var selectedTypes = getSelectedAnnotationTypes();
    // Ensure selectedTypes is an array
    if (!Array.isArray(selectedTypes)) {
        selectedTypes = [];
    }
    var index = selectedTypes.indexOf(annotationId);
    
    if (index > -1) {
        // Remove if already selected
        selectedTypes.splice(index, 1);
    } else {
        // Add if not selected
        selectedTypes.push(annotationId);
    }
    
    saveSelectedAnnotationTypes(selectedTypes);
    return selectedTypes;
}

// Function to update bold styling for selected annotation type links
function updateSelectedAnnotationTypeStyle() {
    var selectedTypes = getSelectedAnnotationTypes();
    // Ensure selectedTypes is an array
    if (!Array.isArray(selectedTypes)) {
        selectedTypes = [];
    }
    var allAnnotationLinks = document.querySelectorAll('[data-annotation-id]');
    
    allAnnotationLinks.forEach(function(link) {
        var annotationIdAttr = link.getAttribute('data-annotation-id');
        if (annotationIdAttr) {
            var annotationId = parseInt(annotationIdAttr, 10);
            // If this annotation type is in the selected list, make it bold
            if (selectedTypes.indexOf(annotationId) > -1) {
                link.style.fontWeight = 'bold';
            } else {
                link.style.fontWeight = '';
            }
        }
    });
}

// Function to restore annotation expansion state on page load
function restoreAnnotationExpansionState() {
    var selectedTypes = getSelectedAnnotationTypes();
    // Ensure selectedTypes is an array
    if (!Array.isArray(selectedTypes)) {
        selectedTypes = [];
    }
    
    // Update bold styling for selected annotation types
    updateSelectedAnnotationTypeStyle();
    
    // If any annotation types are selected, restore them
    if (selectedTypes.length > 0) {
        // Small delay to ensure DOM is ready
        setTimeout(function() {
            selectedTypes.forEach(function(annotationId) {
                expandAnnotationType(annotationId, false); // false = don't toggle, just expand
            });
        }, 150);
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

function switchAnnotationTab(containerId, tabId, contentId) {
    var container = document.getElementById(containerId);
    var tabElement = document.getElementById(tabId);
    var content = document.getElementById(contentId);
    
    if (!container || !tabElement || !content) {
        return;
    }
    
    // Check if this tab is already active
    var isAlreadyActive = tabElement.classList.contains('active');
    
    if (isAlreadyActive) {
        // Toggle off: fade out and hide this content, then remove active state
        content.classList.remove('show');
        
        // Wait for transition to complete before hiding
        setTimeout(function() {
            content.style.display = 'none';
        }, 200); // Match CSS transition duration (0.2s)
        
        tabElement.classList.remove('active');
        // Clear inline styles
        tabElement.style.color = '';
        tabElement.style.backgroundColor = '';
        tabElement.style.borderColor = '';
        
        // Reset span colors (skip badges)
        var spans = tabElement.querySelectorAll('span');
        spans.forEach(function(span) {
            if (!span.classList.contains('badge')) {
                span.style.color = '';
            }
        });
    } else {
        // Toggle on: show this content and mark as active
        content.style.display = 'block';
        requestAnimationFrame(function() {
            content.classList.add('show');
        });
        
        tabElement.classList.add('active');
    }
}

// Function to expand/collapse a specific annotation type across all results
// If toggle is false, it will only expand (used for restoring state)
function expandAnnotationType(annotationId, toggle) {
    if (toggle === undefined) {
        toggle = true; // Default to toggle behavior
    }
    
    // Find all annotation tabs with this specific annotation_id across all results
    var allTabs = document.querySelectorAll('[id$="_annot_tab_' + annotationId + '"]');
    
    // Get current selection state
    var selectedTypes = getSelectedAnnotationTypes();
    // Ensure selectedTypes is an array
    if (!Array.isArray(selectedTypes)) {
        selectedTypes = [];
    }
    var isCurrentlySelected = selectedTypes.indexOf(annotationId) > -1;
    
    // Check if any tabs for this annotation are currently active
    var hasAnyActive = false;
    if (allTabs && allTabs.length > 0) {
        allTabs.forEach(function(tab) {
            if (tab.classList.contains('active')) {
                hasAnyActive = true;
            }
        });
    }
    
    // Determine action
    var shouldExpand;
    if (toggle) {
        // Toggle behavior: if already selected, deselect it; otherwise select it
        // If there are no tabs (annotation doesn't appear in results), just toggle selection state
        if (allTabs.length === 0) {
            shouldExpand = !isCurrentlySelected;
        } else {
            // If there are tabs, check if they're active
            shouldExpand = !(isCurrentlySelected && hasAnyActive);
        }
    } else {
        // Non-toggle behavior: always expand (for restoring state)
        shouldExpand = true;
    }
    
    // Update selection list
    if (shouldExpand) {
        // Add to selection if not already there
        if (!isCurrentlySelected) {
            selectedTypes.push(annotationId);
            saveSelectedAnnotationTypes(selectedTypes);
        }
    } else {
        // Remove from selection
        var index = selectedTypes.indexOf(annotationId);
        if (index > -1) {
            selectedTypes.splice(index, 1);
            saveSelectedAnnotationTypes(selectedTypes);
        }
    }
    
    // Get all selected types to expand them all
    var finalSelectedTypes = getSelectedAnnotationTypes();
    
    // Process each result item
    var allContainers = document.querySelectorAll('[id^="res_"][id$="_annot_tabs"]');
    allContainers.forEach(function(container) {
        var parentElement = container.parentElement;
        var tabsContainer = parentElement ? parentElement.querySelector('.annotation-tabs') : null;
        var allTabsInContainer = tabsContainer ? tabsContainer.querySelectorAll('.annotation-tab') : [];
        
        // Process each annotation type in this container
        allTabsInContainer.forEach(function(tab) {
            var tabId = tab.id;
            // Extract annotation ID from tab ID (format: res_{internal_id}_annot_tab_{annotation_id})
            var match = tabId.match(/_annot_tab_(\d+)$/);
            if (!match) {
                return;
            }
            var tabAnnotationId = parseInt(match[1], 10);
            var contentId = tab.getAttribute('data-content-id');
            if (!contentId) {
                return;
            }
            
            var content = document.getElementById(contentId);
            if (!content) {
                return;
            }
            
            // Check if this annotation type should be visible
            var shouldBeVisible = finalSelectedTypes.indexOf(tabAnnotationId) > -1;
            
            if (shouldBeVisible) {
                // Show content
                content.style.display = 'block';
                requestAnimationFrame(function() {
                    content.classList.add('show');
                });
                // Mark tab as active
                tab.classList.add('active');
            } else {
                // Hide content with fade-out transition
                content.classList.remove('show');
                // Wait for transition to complete before hiding
                var contentToHide = content;
                setTimeout(function() {
                    if (contentToHide && !contentToHide.classList.contains('show')) {
                        contentToHide.style.display = 'none';
                    }
                }, 200); // Match CSS transition duration (0.2s)
                // Remove active from tab
                tab.classList.remove('active');
                tab.style.color = '';
                tab.style.backgroundColor = '';
                tab.style.borderColor = '';
            }
        });
    });
    
    // Update bold styling for selected annotation types
    updateSelectedAnnotationTypeStyle();
}



