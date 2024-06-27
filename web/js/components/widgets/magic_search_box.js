/* 
 * Basic functionality for magic seach box classes.
 * Do everything after the document has fully loaded.
 */

$(document).ready(function()
{
    /*
     * Register function for close buttons, that will:
     * a) Remove current div, as well as sibling hidden input
     * b) Re-submit parent form
     */
    $(".fa.fa-times").on('click', function()
    {
        /*
         * Get the div containing image tag & selected name + remove it.
         * Also get the parent form, and resubmit its action
         */
        parentHiddenDiv = $(this).closest("div.hidden_element_box");
        parentForm = $(this).closest('form');
        parentHiddenDiv.remove();
        $("#loading_div").show();
        $("#not_loading_div").hide();

        parentForm.submit();

    });
    
    /**
    * Register function for "clear all" button that will: 
    * a) remove all sibling divs, as well as their hidden inputs
    * b) re-submit parent form
    */
    $(".magic_search_box_clear_all").on('click', function()
    {
        siblingHiddenDivs = $(this).siblings("div.hidden_element_box");
        parentForm = $(this).closest('form');
        siblingHiddenDivs.remove();
        $(this).remove();   //remove "clear all" button
        $("#loading_div").show();
        $("#not_loading_div").hide();

        parentForm.submit();
    });

    /*
     * Register handler for link to reveal all selections in a magic search box.
     * 
     * In order to register the handlers on future elements (i.e. non - existing
     * on the page currently, we have to use delegate method. In order to make
     * this somehow faster, we use an initial selectod for our search box wrapper)
     */
   $(".magic_search_box_wrapper").delegate('.magic_search_box_reveal', 'click', function()
    {
        $(this).siblings("div.hidden_element_box").removeClass("non-display");
        $(this).text("Hide Selections");
        $(this).removeClass("magic_search_box_reveal").addClass("magic_search_box_hide");
    });
    /*
     * Register handler for link to hide all selections in a magic search box
     * 
     * See above for why we use delegate instead of "on"
     */    
    $(".magic_search_box_wrapper").delegate('.magic_search_box_hide', 'click', function()
    {
        $(this).siblings("div.hidden_element_box").slice(3).toggleClass("non-display");
        $(this).text("Show all");
        $(this).removeClass("magic_search_box_hide").addClass("magic_search_box_reveal");
    });

    /*
     * Follows code for auto-focus last selected magicbox after page reload
     * @author: Serafeim Chatzopoulos (May 2016)
     */
    var box_selected;    // id of last selected magicbox

    // if window.name is set, focus on the specified item
    if(window.name != ""){
        var el = document.getElementById(window.name);
        if(el != null){
            box_selected = window.name;
            el.focus();
        }
    }

    /*
     * Detects click events away from the magicboxes and clears selection
     */
    $(document).on('click', function(event)
    {
        var cur_click = $(event.target).attr('id');
        // if click detected is not on a magicbox or its suggestions
        if(cur_click  == undefined || (cur_click != undefined && 
            (cur_click.indexOf("search_box") == -1) && (cur_click.indexOf("ui-id") == -1))){

            window.name = "";
        }
    });

    /*
     * Detects focus on a magicbox
     */
    $(function() {
        $('input').focus(function(event) {
            box_selected = $(event.target).attr('id');

            // if a magicbox is focused, set window.name with its id
            if(box_selected  != undefined && 
                box_selected.indexOf("search_box") > -1){

                window.name = box_selected;
            }
            else{
                window.name = "";
            }
        });
    });
});

