/*
 * Basic functionality for search results, e.g., show search context
 */

$(document).ready(function()
{
    $('a.context-popup-link').each(function()
    {
        $(this).on("click", function(e) {
            siblingRowHtml = $(this).next().html();
            $('#modalContent').html(siblingRowHtml);
            $('#modal').modal('show');
        });
    });


    // Initialize the Bootstrap tooltip for the copy-link class
  $('.copy-link[data-toggle="tooltip"]').tooltip({
        trigger: 'manual',
        title : 'Link Copied!'
    });

  // Handle the click event on the copy-link class
  $('.copy-link').on('click', function(e) {
    e.preventDefault();
    let currentElement = $(this)
    // Get the text to be copied from the href attribute
    let text = currentElement.attr("href");

    // Create a temporary textarea element to hold the text to be copied
    let textarea = $('<textarea></textarea>');
    textarea.text(text);
    $('body').append(textarea);

    // Select the text in the textarea
    textarea.select();

    // Copy the selected text to the clipboard
    document.execCommand('copy');

    // Remove the temporary textarea element
    textarea.remove();

    // Trigger the Bootstrap tooltip to show the specified copy message
    currentElement.tooltip('show');

    // Hide the Bootstrap tooltip after a delay of 1 second
    setTimeout(function() {
        currentElement.tooltip('hide');
        }, 1000);


  });


});


