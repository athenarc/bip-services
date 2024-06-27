$(document).ready(function() {
    $('#settings-public-switch').click(function(event) {
        var websiteRoot = window.location.origin;

        var csrfToken = $('meta[name="csrf-token"]').attr("content");
        var is_public = (event.target.checked) ? 1 : 0;

        if(is_public) {
            if(!confirm('You are about to make your BIP! Scholar profile public. This means that your profile information (research works, roles, tags, indicators, narratives) will be publicly available to anyone through BIP! Scholar’s UI and API. You can switch to a private profile anytime you want. Please confirm that you understood this and that you give your consent.')){
                event.preventDefault();
                return;
            }
        } else {
            if (!confirm('You are about to make your BIP! Scholar profile private. This means that your profile information (research works, roles, tags, indicators, narratives) will stop being publicly available through BIP! Scholar’s UI and API from now onwards. Please confirm that you understood this and that you give your consent.')){
                event.preventDefault();
                return;
            }
        }

        $.ajax({
            url:   websiteRoot + '/bip/web/index.php/scholar/ajax-update-public-profile',
            type: 'POST',
            data: {
                'is_public' : is_public,
                _csrf : csrfToken
            },
            success: function({ orcid }) {
                let visibility = (is_public) ? ("Public") : ("Private");
                $("#profile-visibility-text").text(visibility);

                // hide and show functionality and text in cv narrative settings
                $('.cv-narrative-settings-toggle').toggle();

            },
            error: function(e) {
                alert("There was an error processing your request!");
            }
        });
    });

    $('#settings-unlink-switch').click(function(event) {

        if(!confirm('You are about to unlink your BIP! Scholar profile from ORCiD. This means that you will not longer be able to use your BIP! Scholar profile, and we will not keep or share any information regarding your ORCiD profile.')){
            event.preventDefault();
            return;
        }

    });



    $("#settings-narrative-public-switch").click(function(){
        if(this.checked){
            $(".cv-narrative-settings-checkbox").each(function(){
                this.checked=true;
            })
        }else{
            $(".cv-narrative-settings-checkbox").each(function(){
                this.checked=false;
            })
        }

        AjaxUpdateCvNarrative(this.checked, null)
    });

    $(".cv-narrative-settings-checkbox").click(function () {
        if ($(this).is(":checked")){
            var isAllChecked = 1;
            $(".cv-narrative-settings-checkbox").each(function(){
                if(!this.checked){
                    isAllChecked = 0;
                }
            })

            if(isAllChecked == 1){
                $("#settings-narrative-public-switch").prop("checked", true);
            }else {
                $("#settings-narrative-public-switch").prop("checked", false);
            }

        }else {
            $("#settings-narrative-public-switch").prop("checked", false);
        }

        AjaxUpdateCvNarrative(this.checked, $(this).attr("data-cv-narrative-id"))


    });


    function AjaxUpdateCvNarrative(isChecked, CvNarrativeId) {

        let websiteRoot = window.location.origin;
        let csrfToken = $('meta[name="csrf-token"]').attr("content");

        $.ajax({
            url:   websiteRoot + '/bip/web/index.php/scholar/ajax-update-public-cv-narrative',
            type: 'POST',
            data: {
                'is_public' : isChecked ? 1 : 0,
                'cv_narrative_id' : CvNarrativeId,
                _csrf : csrfToken
            },
            error: function(e) {
                alert("There was an error processing your request!");
            }
        });
    }

    $('#keyword-relevance-toggle').on('change', function(){
        var isChecked = $(this).prop('checked');
        $.ajax({
            url: '/user/update-keyword-relevance',
            type: 'POST',
            data: {
                keyword_relevance: isChecked ? 1 : 0,
                _csrf: yii.getCsrfToken(),
            },
            error: function(xhr, status, error) {
                const errorMesssage = 'Failed to update keyword relevance';
                console.error(errorMesssage, error);
                alert(errorMesssage);
            }
        });
    });
});
