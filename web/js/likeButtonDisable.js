$(document).ready(function()
{
    $("a.my-btn:has(i.fa-bookmark)").bind('click', function(event)
    {
        event.preventDefault();
        return false;
    });
});

