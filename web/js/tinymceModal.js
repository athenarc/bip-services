/*
* Bookmarks notes editor
*/

$(document).ready(function () {


    var paperId = null;

    var tinyConfig = {
        selector: '#notes-area',
        height: '65vh',
        branding: false,
        elementpath: false,
        menubar: false,
        link_default_protocol: 'https',
        link_assume_external_targets: true,
        table_tab_navigation: true,
        content_style: "body {font-size: 12pt; line-height: 1.1;} p { margin: 2; }",
        fontsize_formats: "8pt 10pt 12pt 14pt 15pt 16pt 18pt 20pt 24pt 36pt 40pt",
        lineheight_formats: '1 1.1 1.2 1.3 1.4 1.5 1.6 1.7 1.8 2',
        // statusbar: false,
        // toolbar_mode: 'sliding',
        // remove_linebreaks : false,


        style_formats: [
            {title: 'Headers', items: [
                {title: 'Heading 1', block: 'h1'},
                {title: 'Heading 2', block: 'h2'},
                {title: 'Heading 3', block: 'h3'},
                {title: 'Heading 4', block: 'h4'},
                {title: 'Heading 5', block: 'h5'},
                {title: 'Heading 6', block: 'h6'}
            ]},

            {title: 'Blocks', items: [
                {title: 'Paragraph', block: 'p'},
            ]},
        ],

        plugins: [
            "advlist lists autolink link ",
            "code table paste wordcount ",
            "charmap hr print searchreplace"
        ],


        toolbar: [
        'undo redo | fontselect | fontsizeselect | lineheight | bold italic underline |'+
        ' forecolor backcolor removeformat | strikethrough subscript superscript |'+
        ' alignleft aligncenter alignright alignjustify | numlist bullist outdent indent |'+
        ' styleselect | table charmap hr | link | print searchreplace |formatting'
        ],


    };



    $(".show-notes").click(function () {
        let currentElement = $(this);
        paperId = currentElement.attr('id').replace("notes-", "");
        console.log(paperId);

        // show modal loading message during ajax call
        $('#loading-notes-message').show();
        $('#text-editor-modal')
                            .find('.modal-dialog')
                            .load(currentElement.attr("href"), function(response, status, xhr) {

            //Load succesful and completed
            tinymce.init(tinyConfig).then(() => {
                $('#loading-notes-message').hide();
            })

            if ( status == "error" ) {
                alert("There was an error processing your request! ");
                location.reload();
                }

        });
    });


    // delegetaed binding because button #save-notes is
    // added to the document at a later time
    $(document).on('click', '#save-notes', function(){
        let ed = tinyMCE.get('notes-area');
        let ed_content = ed.getContent()

        ajaxSave(ed_content, paperId)
        $('#text-editor-modal').modal('hide');

    });

    var ajaxSave = function(ed_content, paperId) {

        //Required for post requests in yii
        var csrfToken = $('meta[name="csrf-token"]').attr("content");

        //Do the required action
        $.ajax({
            url:   `${appBaseUrl}/readings/save-notes`,
            type: 'POST',
            data:{
                'notes' : ed_content,
                'paper_id' : paperId,
                _csrf : csrfToken
            },
            success: function(data){

                // update notes icon if user saves empty/non-empty note
                let new_icon = (ed_content === "") ? '<i class="fa-regular fa-pen-to-square"></i>' : '<i class="fa-solid fa-pen-to-square"></i>';
                $(`#notes-${paperId} > i`).replaceWith(new_icon);

            },
            error: function(e){
                alert("There was an error processing your request!");
                location.reload();
            }
        });
    }

    $('#text-editor-modal').on('hidden.bs.modal', function (e) {
        // destroy text-area and editor
        tinymce.remove('#notes-area');
        $('#notes-area').remove();
        // partially destroy modal content except modal-body
        // to keep loading message
        $(this).find('.modal-header').remove();
        $(this).find('.modal-footer').remove();

    });


    // Prevent bootstrap dialog from blocking tinymce focusin
    $(document).on('focusin', function(e) {
        if ($(e.target).closest(".tox-tinymce-aux, .moxman-window, .tam-assetmanager-root").length) {
            e.stopImmediatePropagation();
        }
    });


});