$(document).ready(() => {
    $('#settings-unlink-switch').click(event => {
        if (!confirm('You are about to unlink your BIP! Scholar profile from ORCiD. This means that you will not longer be able to use your BIP! Scholar profile, and we will not keep or share any information regarding your ORCiD profile.')) {
            event.preventDefault();
        }
    });
});

function toggleSwitch(checkbox, settingName, url) {
    const isChecked = checkbox.checked;
    $.ajax({
        url,
        type: 'POST',
        data: {
            settingName,
            settingValue: isChecked ? 1 : 0,
            _csrf: yii.getCsrfToken(),
        },
        success: function (response) {
            console.log('Setting updated successfully:', response);
        },
        error: function (xhr, status, error) {
            const errorMesssage = 'Failed to update setting';
            console.error(errorMesssage, error);
            alert(errorMesssage);
        },
    });
}

function confirmAiAssistantToggle(elem, url) {
    if (elem.checked) {
        const confirmed = confirm('You are about to enable the AI Assistant. Please note that this feature may share your input with third-party services to provide enhanced functionality. By proceeding, you confirm that you understand this and you give your consent.');
        if (!confirmed) {
            elem.checked = false;
            return;
        }
    }

    toggleSwitch(elem, 'ai_features', url);
}
