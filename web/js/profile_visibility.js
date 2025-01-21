function toggleProfileVisibility(isPublic, callback) {
    var csrfToken = $('meta[name="csrf-token"]').attr("content");
    var newVisibility = isPublic ? 1 : 0;

    if (newVisibility) {
        if (!confirm('You are about to make your BIP! Scholar profile public. This means that your profile information (research works, roles, tags, indicators, narratives) will be publicly available to anyone through BIP! Scholar’s UI and API. You can switch to a private profile anytime you want. Please confirm that you understood this and that you give your consent.')) {
            return;
        }
    } else {
        if (!confirm('You are about to make your BIP! Scholar profile private. This means that your profile information (research works, roles, tags, indicators, narratives) will stop being publicly available through BIP! Scholar’s UI and API from now onwards. Please confirm that you understood this and that you give your consent.')) {
            return;
        }
    }

    $.ajax({
        url: `${appBaseUrl}/scholar/ajax-update-public-profile`,
        type: 'POST',
        data: {
            'is_public': newVisibility,
            _csrf: csrfToken
        },
        success: function() {
            if (callback) {
                callback();
            }
        },
        error: function() {
            alert("There was an error processing your request!");
        }
    });
}

function updateLockIcon(isPublic) {
    var newClass = isPublic ? 'fa-lock' : 'fa-lock-open';
    var newTitle = isPublic ? 'This profile is only visible to you (Switch to Public Profile).' : 'This profile is publicly visible (Switch to Private Profile).';

    $('#profile-visibility-toggle')
        .removeClass('fa-lock fa-lock-open')
        .addClass(newClass)
        .attr('title', newTitle);

    $("#profile-visibility-text").text(isPublic ? "Public" : "Private");

    $('.cv-narrative-settings-toggle').toggle();
}

$(document).ready(function() {
    $('#settings-public-switch').click(function(event) {
        var isPublic = event.target.checked;
        toggleProfileVisibility(isPublic);
    });

    $('#profile-visibility-toggle').click(function() {
        var isPublic = $(this).hasClass('fa-lock');
        toggleProfileVisibility(isPublic, function() {
            updateLockIcon(!isPublic);
        });
    });
});