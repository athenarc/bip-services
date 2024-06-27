$(document).ready(function () {

  let adminTextAreaTinyConfig = {
      selector: '.rich_text_area_admin',
      branding: false,
      elementpath: false,
      menubar: false,
      link_default_protocol: 'https',
      link_assume_external_targets: true,
      content_style: "body {font-size: 12pt; line-height: 1.1;} p { margin: 2; }",
      lineheight_formats: '1 1.1 1.2 1.3 1.4 1.5 1.6 1.7 1.8 2',
      min_height: 150,
      plugins: [
          "advlist lists autolink link ",
          "code paste hr"
      ],

      toolbar: [
      'undo redo | bold italic underline |'+
      ' strikethrough subscript superscript | link |'+
      ' numlist bullist outdent indent |'
      ]
  };

  tinymce.init(adminTextAreaTinyConfig);
});
