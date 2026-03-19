$(document).ready(() => {
    $('#settings-unlink-switch').click(event => {
        if (!confirm('You are about to unlink your BIP! Scholar profile from ORCiD. This means that you will not longer be able to use your BIP! Scholar profile, and we will not keep or share any information regarding your ORCiD profile.')) {
            event.preventDefault();
        }
    });

    const $createApiTokenBtn = $('#create-api-token-btn');
    const $copyApiTokenBtn = $('#copy-api-token-btn');
    const $apiTokenInput = $('#api-token-input');

    if ($copyApiTokenBtn.length) {
        $copyApiTokenBtn.tooltip();
    }

    function generateHexToken(length = 50) {
        // Generate cryptographically strong random hex token (length <= auth_token varchar(50))
        const byteLength = Math.ceil(length / 2);
        const bytes = new Uint8Array(byteLength);
        window.crypto.getRandomValues(bytes);
        const hex = Array.from(bytes).map(b => b.toString(16).padStart(2, '0')).join('');
        return hex.slice(0, length);
    }

    if ($createApiTokenBtn.length && $apiTokenInput.length) {
        $createApiTokenBtn.on('click', () => {
            const updateUrl = $createApiTokenBtn.data('update-url');
            const newToken = generateHexToken(50);

            $createApiTokenBtn.prop('disabled', true);

            $.ajax({
                url: updateUrl,
                type: 'POST',
                data: {
                    settingName: 'auth_token',
                    settingValue: newToken,
                    _csrf: yii.getCsrfToken(),
                },
                success: response => {
                    if (response && response.success) {
                        $apiTokenInput.val(newToken);
                    } else {
                        alert(response?.error || 'Failed to create token.');
                    }
                },
                error: () => {
                    alert('Failed to create token.');
                },
                complete: () => {
                    $createApiTokenBtn.prop('disabled', false);
                },
            });
        });
    }

    if ($copyApiTokenBtn.length && $apiTokenInput.length) {
        $copyApiTokenBtn.on('click', () => {
            const token = ($apiTokenInput.val() || '').trim();
            if (! token) {
                return;
            }

            navigator.clipboard.writeText(token).then(() => {
                $copyApiTokenBtn
                    .attr('data-original-title', 'Token copied!')
                    .tooltip('show')
                    .off('mouseenter focus');

                setTimeout(() => {
                    $copyApiTokenBtn.tooltip('hide').removeAttr('data-original-title');
                }, 1500);
            }).catch(err => {
                console.error('Failed to copy token.', err);
            });
        });
    }
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
