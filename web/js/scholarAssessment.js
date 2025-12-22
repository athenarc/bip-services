$(document).ready(() => {
    let indicatorValues = [];
    let indicatorIDs = [];

    indicatorValues = $('.indicator-div > .indicator')
        .map(function () {
            return $(this).text();
        })
        .get();

    indicatorIDs = $('.indicator-div')
        .map(function () {
            return $(this).data('value');
        })
        .get();

    $('#assessment-framework-dropdown').on('change', function () {
        const frameworkId = $(this).val();

        $.ajax({
            url:
        `${window.location.origin
        }/bip/web/index.php/scholar/protocols-dropdown`,
            type: 'POST',
            data: {
                framework_id: frameworkId,
            },
            dataType: 'json',
            success: function (response) {
                $('#assessment-protocol-dropdown').html(response);
                $('#assessment-protocol-dropdown').prop('disabled', false);
                $('#assessment-protocol-dropdown').empty();
                const count = response.length;
                if (count === 0) {
                    $('#assessment-protocol-dropdown').empty();
                    $('#assessment-protocol-dropdown').prop('disabled', 'disabled');
                    $('#assessment-protocol-dropdown').append(
                        `<option value='${ id }'>Select Assessment Protocol</option>`
                    );
                    $('#assessment-protocol-dropdown').append(
                        `<option value='${ id }'></option>`
                    );
                    $('.indicator-div').removeClass('light-grey-text');

                    $.each(indicatorIDs, (index, indicator_id) => {
                        const $indicatorDiv = $(
                            `.indicator-div[data-value="${ indicator_id }"]`
                        );
                        $indicatorDiv.find('.indicator').text(indicatorValues[index]);
                    });
                    $('#assessment-protocol-dropdown').trigger('change');
                } else {
                    for (let i = 0; i < count; i++) {
                        var id = response[i]['id'];
                        const name = response[i]['name'];
                        $('#assessment-protocol-dropdown').append(
                            `<option value='${ id }'>${ name }</option>`
                        );
                    }
                    $('#assessment-protocol-dropdown').trigger('change');
                }
            },
            error: function (e) {
                alert(e.responseText);
            },
        });
    });

    $('#assessment-protocol-dropdown').on('change', function () {
        const protocolId = $(this).val();

        if (protocolId == undefined) {
            console.log(protocolId);
        }

        $.ajax({
            type: 'GET',
            url:
        `${window.location.origin
        }/bip/web/index.php/scholar/get-selected-protocol-indicators`,
            data: { protocolId: protocolId },
            success: function (data) {
                $('.indicator-div').removeClass('light-grey-text');

                console.log(data);
                $.each(indicatorIDs, (index, indicator_id) => {
                    const $indicatorDiv = $(
                        `.indicator-div[data-value="${ indicator_id }"]`
                    );

                    $indicatorDiv.find('.indicator').text(indicatorValues[index]);

                    if (data.includes(indicator_id)) {
                        $indicatorDiv.addClass('light-grey-text');
                        $indicatorDiv.find('.indicator').text('N/A');
                    } else {
                        $indicatorDiv.show();
                    }
                });
            },
            error: function (e) {
                $('.indicator-div').show();
            },
        });
    });


    $('#presets-dropdown').on('change', function () {
        const protocolId = $(this).val();

        if (protocolId == undefined) {
            console.log(protocolId);
        }

        $.ajax({
            type: 'GET',
            url:
        `${window.location.origin
        }/bip/web/index.php/scholar/get-selected-protocol-indicators`,
            data: { protocolId: protocolId },
            success: function (data) {
                $('.indicator-div').removeClass('light-grey-text');

                // console.log(data);
                $.each(indicatorIDs, (index, indicator_id) => {
                    const $indicatorDiv = $(
                        `.indicator-div[data-value="${ indicator_id }"]`
                    );

                    $indicatorDiv.find('.indicator').text(indicatorValues[index]);

                    if (!data.includes(indicator_id) && data.length !== 0) {
                        // console.log(indicator_id + ": " + false);
                        $indicatorDiv.addClass('light-grey-text');
                        $indicatorDiv.find('.indicator').text('N/A');
                    } else {
                        // console.log(indicator_id + ": " + true);
                        $indicatorDiv.show();
                    }
                });
            },
            error: function (e) {
                $('.indicator-div').show();
            },
        });
    });
});
