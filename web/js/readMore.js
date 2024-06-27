$(document).ready(function() {
    $(".toggle-btn").click(function() {
      
        var elem = $(this).text();
      
        if (elem == "(Read More)") {
            
            var more = $(this).siblings('.hidden-value').text(); // full field value
            var less = $(this).siblings('.show-value').text(); // shortened field value

            // swap above
            $(this).siblings('.hidden-value').text(less); 
            $(this).siblings('.show-value').text(more);

            // change button text
            $(this).text("(Read Less)");
        } else {
            //Stuff to do when btn is in the read less state
            
            var less = $(this).siblings('.hidden-value').text(); // full field value
            var more = $(this).siblings('.show-value').text(); // shortened field value

            // swap above
            $(this).siblings('.hidden-value').text(more); 
            $(this).siblings('.show-value').text(less);

            // change button text
            $(this).text("(Read More)");
        }
    });
});