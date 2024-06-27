/*
 * Script to submit the google analytics opt-out form
 * 
 * @author: Kostis Zagganas (First Version: September 2018)
 */

$("#analytics_opt_out_checkbox").on('change', function()
    {	
    	boxHidden = $("#analytics_opt_out");
    	checkBox = $("#analytics_opt_out_chekbox");

        parentForm = $(this).closest('form');

        
        /*
         * The following is needed to update the hidden field, because without it
         * get parameters are not added
         */
        if (boxHidden.val()==1)
        {
        	boxHidden.val("0");
        }
        else
        {
        	boxHidden.val("1");
        }

        parentForm.submit();
    });