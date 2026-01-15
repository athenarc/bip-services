/**
 * Loads and renders annotation evolution charts asynchronously
 */
// @ts-ignore
var appBaseUrl = typeof appBaseUrl !== 'undefined' ? appBaseUrl : window.appBaseUrl || '';
$(document).ready(function() {
    // Get parameters from data attributes or URL
    var spaceUrlSuffix = $('#annotation-evolution-data').data('space-url-suffix');
    var annotationId = $('#annotation-evolution-data').data('annotation-id');
    var id = $('#annotation-evolution-data').data('id');
    
    // If data attributes are not available, try to get from URL
    if (!spaceUrlSuffix || !annotationId || !id) {
        var pathname = window.location.pathname;
        var annotationMatch = pathname.match(/\/([^\/]+)\/annotation\/(\d+)/);
        
        if (annotationMatch) {
            spaceUrlSuffix = annotationMatch[1];
            annotationId = annotationMatch[2];
            var urlParams = new URLSearchParams(window.location.search);
            id = urlParams.get('id');
        }
    }
    
    // Only proceed if we have all required parameters
    if (!spaceUrlSuffix || !annotationId || !id) {
        $('#annotation-charts-loading').hide();
        $('#annotation-charts-container').hide();
        return;
    }
    
    $.ajax({
        url: `${appBaseUrl}/site/get-annotation-evolution`,
        type: 'GET',
        data: {
            space_url_suffix: spaceUrlSuffix,
            annotation_id: annotationId,
            id: id
        },
        success: function(data) {
            if (data.count_per_year && data.citation_per_year && 
                Object.keys(data.count_per_year).length > 0) {
                var years = Object.keys(data.count_per_year);
                var counts = Object.values(data.count_per_year);
                var citations = Object.values(data.citation_per_year);
                
                $('#annotation-charts-loading').hide();
                $('#annotation-charts-container').show();
                
                render_bar_plot('annotation-evolution-bar-plot', years, counts, 'Number of research products', 'Number of research products', 'Publication year');
                render_bar_plot('annotation-citations-per-year-bar-plot', years, citations, 'Citation count', 'Citation count', 'Publication year');
            } else {
                $('#annotation-charts-loading').hide();
                $('#annotation-charts-container').hide();
            }
        },
        error: function(_xhr, _status, error) {
            console.error('Error loading annotation evolution data:', error);
            $('#annotation-charts-loading').hide();
            $('#annotation-charts-container').hide();
        }
    });
});

