$(document).ready(function() {
    $('#settings-unlink-switch').click(function(event) {

        if(!confirm('You are about to unlink your BIP! Scholar profile from ORCiD. This means that you will not longer be able to use your BIP! Scholar profile, and we will not keep or share any information regarding your ORCiD profile.')){
            event.preventDefault();
            return;
        }

    });

    $('#keyword-relevance-toggle').on('change', function(){
        var isChecked = $(this).prop('checked');
        $.ajax({
            url: appBaseUrl + '/user/update-keyword-relevance',
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
