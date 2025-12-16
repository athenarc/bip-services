$(document).ready(() => {
    if (window.matchMedia('(max-width: 900px)').matches) {
        $('#warnMobileUser').modal('show');
    }
});
