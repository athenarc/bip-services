/*
 * Datepicker pop-up to define academic gaps
   Rag : Responsible academic age (Fair academic age)
 */

$(document).ready(function () {


    $("#Rag-form").submit(function(e){
        e.preventDefault();
        let $ragInputs = $('#Rag-form :input');
        let ragValues = {};
        let ragValuesLength = 0;
        $ragInputs.each(function() {
            // get values only from elements with tag name set
            if (this.name && $(this).val()){
            ragValues[this.name] = $(this).val();
            ragValuesLength += 1;
            }
        });

        if (ragValuesLength !== 3 ){
            alert("Fields cannot be blank!");
            return;
        }
        let startDate = Date.parse(ragValues.from_date);
        let endDate = Date.parse(ragValues.to_date);

        if (isNaN(startDate) || isNaN(endDate)){
            alert("Invalid Dates");
            return;
        }
        if (startDate === endDate){
            alert("End date should be greater than Start date");
            return;
        }
        if (startDate > endDate){
            alert("Invalid Date Range");
            return;
        }
        //Required for post requests in yii
        var csrfToken = $('meta[name="csrf-token"]').attr("content");
        //Do the required ajax action
        $.ajax(
        {
            url:   window.location.origin + '/bip/web/index.php/scholar/add-rag',
            type: 'POST',
            data:
            {
                'description' : ragValues.Rag_Description,
                'from_date' : ragValues.from_date,
                'to_date' : ragValues.to_date,
                _csrf : csrfToken
            },
            success: function({found_date_period, new_rag_data})
            {   

                if (found_date_period){
                    alert("Date interval already exists");
                } else if (!new_rag_data.id){
                    alert("Invalid date")

                } else{
                    let newRag = $(`<tr id = rag_${new_rag_data.id}>`
                    + `<td class = "col-xs-5" >${new_rag_data.description}</td>`
                    + `<td class = "col-xs-5" >${new_rag_data.from_date} to ${new_rag_data.to_date}</td>`
                    + '<td class = "col-xs-1 text-right" >'
                    + '<button type="button" class="rag-delete btn btn-xs">'
                    + '<i role="button" class="fa-solid fa-xmark"></i></button></td>'
                    + '</tr>');

                    //append current item
                    $('#academic-age-datepicker-modal').find("tbody").append(newRag);
                    if ($('#academic-age-datepicker-modal').find("tbody").children().length > 0 ) {
                        $('#no-rag-dates').hide();
                    };
                }
            },
            error: function(e)
            {
                alert("There was an error processing your request!");
                location.reload();
            }
        })
        
    });

    $(document).on('click', '.rag-delete', function () {
        let removedElement = $(this).closest('tr')
        let ragId = removedElement.attr('id').replace("rag_", "");

        //Required for post requests in yii
        var csrfToken = $('meta[name="csrf-token"]').attr("content");
        //Do the required ajax action
        $.ajax(
        {
            url:   window.location.origin + '/bip/web/index.php/scholar/remove-rag',
            type: 'POST',
            data:
            {
                'rag_id' : ragId,
                _csrf : csrfToken
            },
            success: function()
            {   
                removedElement.fadeOut("slow", function() {
                    removedElement.remove();
                    $.when(removedElement.remove()).then(() => {
                        if ($('#academic-age-datepicker-modal').find("tbody").children().length == 0 ) {
                            $('#no-rag-dates').show();
                       };
                    });

                });

            },
            error: function(e)
            {
                alert("There was an error processing your request!");
                location.reload();
            }
        })
    })

    $('#academic-age-datepicker-modal.edit_perm').on('hide.bs.modal', function (e) {
        let minYear = $("#academic-age-indicator").data("minYear");
        let academicAge = $("#academic-age-indicator").data("academicAge");

        //Required for post requests in yii
        var csrfToken = $('meta[name="csrf-token"]').attr("content");
        //Do the required ajax action
        $.ajax(
        {
            url:   window.location.origin + '/bip/web/index.php/scholar/update-rag',
            type: 'POST',
            data:
            {
                'min_year' : minYear,
                'academic_age' : academicAge,
                _csrf : csrfToken
            },
            success: function({responsible_academic_age})
            {      
                // update html element value
                $("#responsible-academic-age").text(responsible_academic_age);
            },
            error: function(e)
            {
                alert("There was an error processing your request!");
                location.reload();
            }
        })
      })
});