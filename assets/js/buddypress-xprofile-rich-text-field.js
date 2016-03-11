( function(jQ) {



/**
 * Wrapped in a class.
 */
bpxprofile_richtext = {

	init : function(){

	   if ( jQ("div#poststuff select#fieldtype").html() !== null ) {

			// add richtext field type on Add/Edit Xprofile field admin screen
			if (

				jQ('div#poststuff select#fieldtype option[value="richtext"]').html() === undefined ||
				jQ('div#poststuff select#fieldtype option[value="richtext"]').html() == null

			) {

				var richtextOption = '<option value="richtext">'+RichTextParams.richtext+'</option>';
				jQ("div#poststuff select#fieldtype").append(richtextOption);

			}

		}
	}

};



/**
 * On page load...
 */
jQ(document).ready(function(){
	bpxprofile_richtext.init();
});



})(jQuery);


