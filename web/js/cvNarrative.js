/*
    Cv Narratives functionality
 */

$(document).ready(function () {

    let cvNarrativeTinyConfig = {
        selector: '#new_cv_narrative_description',
        height: '30vh',
        // height: '300px',
        branding: false,
        elementpath: false,
        menubar: false,
        link_default_protocol: 'https',
        link_assume_external_targets: true,
        table_tab_navigation: true,
        body_class : "tiny-mce-body-cv-narrative",
        content_css : '/bip/web/css/scholar-profile.css',
        // content_style: "body {font-size: 12pt; line-height: 1.1;} p { margin: 2; }",
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
            "code paste wordcount ",
            "charmap hr print searchreplace"
        ],


        toolbar: [
        'undo redo | fontselect | fontsizeselect | lineheight | bold italic underline |'+
        ' forecolor backcolor removeformat | strikethrough subscript superscript |'+
        ' alignleft aligncenter alignright alignjustify | numlist bullist outdent indent |'+
        ' styleselect | charmap hr | link | print searchreplace |formatting'
        ],


    };

    tinymce.init(cvNarrativeTinyConfig);

    // Prevent bootstrap dialog from blocking tinymce focusin
    $(document).on('focusin', function(e) {
        if ($(e.target).closest(".tox-tinymce-aux, .moxman-window, .tam-assetmanager-root").length) {
            e.stopImmediatePropagation();
        }
    });

    $('#cv-narrative-delete-button').click(function(event) {
        if(!confirm('Are you sure you want to delete this CV narrative ?')){
            event.preventDefault();
            return;
        }
    });

    $('#cv-narrative-create-button').click (function(e) {
        setCvNarrativeValues(clearAll=true);
        $('#new-cv-narrative-modal').modal('show');
        // $.pjax.reload('#cv-narrative-works-container', {timeout : false, async: true});

    });

    $('#cv-narrative-edit-button').click (function(e) {
        setCvNarrativeValues();
        $('#new-cv-narrative-modal').modal('show');
        // $.pjax.reload('#cv-narrative-works-container', {timeout : false, async: false});

    });

    function setCvNarrativeValues (clearAll=false) {
        // clean the editor from previous formatting
        tinymce.get("new_cv_narrative_description").execCommand('mceCleanup');

        let cv_narrative_modal_header = (clearAll) ? 'New' : 'Edit';
        let new_cv_narrative_id = (clearAll) ? '' : $('#current_cv_narrative_id').text();
        let new_cv_narrative_selected_papers = (clearAll) ? '' : $('#current_cv_narrative_papers').text();
        let new_cv_narrative_title = (clearAll) ? '' : $('#current_cv_narrative_title').text();
        let new_cv_narrative_description = (clearAll) ? '' : $('#current_cv_narrative_description').html();

        // disable the new_cv_narrative_id input when creating a new narrative
        $("#new_cv_narrative_id").prop('disabled', clearAll);
        $('#new_cv_narrative_id').val(new_cv_narrative_id);

        $('#new_cv_narrative_selected_papers').val(new_cv_narrative_selected_papers);
        $('#new_cv_narrative_title').val(new_cv_narrative_title);
        tinymce.get("new_cv_narrative_description").setContent(new_cv_narrative_description);

        $('#cv-narrative-modal-header').text(cv_narrative_modal_header);

        checkSavedSelection();
    }


    $("#cv-narrative-form").submit(function(e){

        // check that the description in the editor is filled
        let isDescriptionValid = $.trim(tinymce.get("new_cv_narrative_description").getContent({ format: "text" })).length;

        // check that all non-disabled input fields are filled
        // i.e #new_cv_narrative_id, #new_cv_narrative_title, #new_cv_narrative_selected_papers
        let isInputsValid = $(this).find('input:not(:disabled)').filter(function () {
            return $.trim($(this).val()).length == 0
        }).length == 0;

        let isFormValid = isInputsValid && isDescriptionValid;

        if (!isFormValid) {
            alert('Please fill in all the required fields.');
            e.preventDefault();
            return;
        }

    });

    function getSelectedPapersArray() {
        return $('#new_cv_narrative_selected_papers').val().split(',').filter((str) => str !== '');
    }

    function checkSavedSelection() {
        let selection = getSelectedPapersArray();

        $('.cv-narrative-selection-checkbox').each(function() {
            let key = $(this).data('key').toString();
            $(this).prop('checked', selection.includes(key));
        });

        // if all checkboxes are checked, check also the header checkbox
        if ($('.cv-narrative-selection-checkbox:checked').length === $('.cv-narrative-selection-checkbox').length) {
            $('.cv-narrative-select-on-check-all').prop('checked', true);
        } else {
            $('.cv-narrative-select-on-check-all').prop('checked', false);


        }
    }


    $(document).on('change', '.cv-narrative-selection-checkbox', function() {
        let key = $(this).data('key').toString();
        let selection = getSelectedPapersArray();

        if ($(this).prop('checked') && !selection.includes(key)) {
            selection.push(key);
        } else if (!$(this).prop('checked') && selection.includes(key)) {
            selection.splice(selection.indexOf(key), 1);
        }
        $('#new_cv_narrative_selected_papers').val(selection.join(','));
    });

    $(document).on('pjax:success', '#cv-narrative-works-container', function() {
        checkSavedSelection();
    });



});
