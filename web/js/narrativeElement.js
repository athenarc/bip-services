$(document).ready(function () {

    // AJAX call function
    function saveContent(url, element_id, template_id, value) {
        $.ajax({
            url,
            type: 'POST',
            data: {
                element_id,
                template_id,
                value,
            },
            success: function(response) {
                console.log('Data saved successfully:', response);
                $('#status_message_' + element_id + ' .status-message').text(response.message).attr('title', response.date);
                $('#status_message_' + element_id + ' .status-count .count-message').text(response.count);
                if (response.limit_status) {
                    $('#status_message_' + element_id + ' .status-count .limit-status').attr('title', response.limit_status);
                    $('#status_message_' + element_id + ' .status-count .limit-status').show();               
                } else {
                    $('#status_message_' + element_id + ' .status-count .limit-status').hide();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error saving data:', error);
                $('#status_message_' + element_id + ' .status-message').text('Error saving data');
            }
        });
    }

    // setup tinnymce for textarea narratives
    tinymce.init({
        selector: '.narrative-element-textarea',
        branding: false,
        elementpath: false,
        menubar: false,
        link_default_protocol: 'https',
        link_assume_external_targets: true,
        content_style: "body {font-size: 12pt; line-height: 1.1;} p { margin: 2; }",
        lineheight_formats: '1 1.1 1.2 1.3 1.4 1.5 1.6 1.7 1.8 2',
        min_height: 150,
        // statusbar: false,
  
        plugins: [
            "advlist lists autolink link ",
            "code table paste ",
            "charmap hr print searchreplace"
        ],


        toolbar: [
        // 'undo redo | fontselect | fontsizeselect | lineheight | bold italic underline |'+
        // ' forecolor backcolor removeformat | strikethrough subscript superscript |'+
        // ' alignleft aligncenter alignright alignjustify | numlist bullist outdent indent |'+
        // ' styleselect | table charmap hr | link | searchreplace | formatting'
          'undo redo | bold italic underline |'+
          ' strikethrough subscript superscript | link |'+
          ' numlist bullist outdent indent |'
        ],
        setup: function(editor) {
            const debouncedSaveContent = debounce(function() {
                let element_id = editor.getElement().getAttribute('element_id');
                let ajax_link = editor.getElement().getAttribute('ajax_link');
                let template_id = $('#template_id').val();
                let value = editor.getContent();

                saveContent(ajax_link, element_id, template_id, value);
            }, 1000); // Adjust the debounce delay as needed (1000ms = 1 second)

            ['input', 'change', 'paste', 'keydown'].forEach( event => {
                editor.on(event, function() {
                    let element_id = editor.getElement().getAttribute('element_id');
                    $('#status_message_' + element_id + ' .status-message').text('Typing...');
    
                    debouncedSaveContent();
                });
            });
        }
    });

  });
  