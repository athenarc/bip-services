$(document).ready(function() {
    $('#settings-unlink-switch').click(function(event) {

        if(!confirm('You are about to unlink your BIP! Scholar profile from ORCiD. This means that you will not longer be able to use your BIP! Scholar profile, and we will not keep or share any information regarding your ORCiD profile.')){
            event.preventDefault();
            return;
        }

    });
});

function toggleSwitch(checkbox, settingName, url) {
    var isChecked = checkbox.checked;
    $.ajax({
        url,
        type: 'POST',
        data: {
            settingName,
            settingValue: isChecked ? 1 : 0,
            _csrf: yii.getCsrfToken(),
        },
        success: function(response) {
            console.log('Setting updated successfully:', response);
        },
        error: function(xhr, status, error) {
            const errorMesssage = 'Failed to update setting';
            console.error(errorMesssage, error);
            alert(errorMesssage);
        }
    });
}