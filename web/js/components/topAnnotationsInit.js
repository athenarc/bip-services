// Ensure the annotation type dropdown defaults to 'all' as soon as possible
(function() {
    var dropdown = document.getElementById('annotation_type_filter');
    if (dropdown) {
        dropdown.value = 'all';
    }
})();


