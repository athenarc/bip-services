$(document).ready(() => {
    $('a.my-btn:has(i.fa-bookmark)').bind('click', event => {
        event.preventDefault();
        return false;
    });
});

