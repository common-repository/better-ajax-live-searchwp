(function($) {
	jQuery(document).ready(function($) {
		$("input#keyword").keyup(function() {
		    if ($(this).val().length > 1) {
				$("#datafetch").show();
		    } else {
		    	$("#datafetch").hide();
		    }
	    });
	});
})(jQuery);