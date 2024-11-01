!function($){     
	jQuery( '#verse_url_get' ).click( function( e ) {
		e.preventDefault();
		var $el = $( this ).parent(),
		uploader = wp.media( {
			title : 'Choose a song',
			library : { type : 'audio'},
			multiple : false
		} )
		.on( 'select', function() {
			var selection = uploader.state().get( 'selection' ),
			attachment = selection.first().toJSON();
              //console.log(attachment);
              $( 'input', $el ).val( attachment.url );
              $( 'img', $el ).attr( 'src', attachment.url ).show();
          } )
		.open();
	} );


	(function($){
        jQuery(window).load(function(){
            jQuery(".verse-main-content, .translated-verse").mCustomScrollbar();
        });
    })(jQuery);


}(jQuery);
