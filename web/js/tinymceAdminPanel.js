$(document).ready(() => {
    // Keep full font-size options, but render a narrower font-size control in the toolbar.
    if (!document.getElementById('tiny-admin-toolbar-widths')) {
        const style = document.createElement('style');
        style.id = 'tiny-admin-toolbar-widths';
        style.textContent = `
            .tox .tox-tbtn[title="Font sizes"] {
                width: 62px;
                min-width: 62px;
                max-width: 62px;
            }
        `;
        document.head.appendChild(style);
    }

    const adminTextAreaTinyConfig = {
        selector: '.rich_text_area_admin',
        branding: false,
        elementpath: false,
        menubar: false,
        link_default_protocol: 'https',
        link_assume_external_targets: true,
        content_style: 'body {font-size: 12pt; line-height: 1.1;} p { margin: 2; }',
        lineheight_formats: '1 1.1 1.2 1.3 1.4 1.5 1.6 1.7 1.8 2',
        style_formats: [
            { title: 'Headers', items: [
                { title: 'Heading 1', format: 'h1' },
                { title: 'Heading 2', format: 'h2' },
                { title: 'Heading 3', format: 'h3' },
                { title: 'Heading 4', format: 'h4' },
                { title: 'Heading 5', format: 'h5' },
                { title: 'Heading 6', format: 'h6' },
            ] },
            { title: 'Blocks', items: [
                { title: 'Paragraph', format: 'p' },
                { title: 'Blockquote', format: 'blockquote' },
                { title: '<pre>', format: 'pre' },
            ] },
        ],
        min_height: 150,
        plugins: [
            'advlist lists autolink link ',
            'code table paste hr',
        ],

        toolbar: [
            'undo redo | fontsizeselect | styleselect | bold italic underline |' +
      ' strikethrough subscript superscript | link |' +
      ' alignleft aligncenter alignright alignjustify | numlist bullist outdent indent | table |',
        ],
    };

    tinymce.init(adminTextAreaTinyConfig);
});
