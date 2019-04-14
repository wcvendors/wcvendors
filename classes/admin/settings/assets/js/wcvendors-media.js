jQuery( function( $ ){

	var id; 	

	// Iterate over all instances of the uploader on the page 
	$('.wcv-img-id').each( function () {
	    
	    id = $( this ).data( 'id' ); 

	    // Handle Add banner
		$( '#wcv-add-' + id ).on( 'click', function(e) { 
			e.preventDefault(); 
			file_uploader( id ); 
			return false; 
		}); 

	});

	function file_uploader( id )
	{

		var media_uploader, json;

		if (undefined !== media_uploader ) { 
			media_uploader.open(); 
			return; 
		}

	    media_uploader = wp.media({
      		title: $( '#wcv-add-' + id ).data('window_title'), 
      		button: {
        		text: $( '#wcv-add-' + id ).data('save_button'), 
      		},
      		multiple: false  // Set to true to allow multiple files to be selected
    	});

	    media_uploader.on( 'select' , function(){

	    	json = media_uploader.state().get('selection').first().toJSON(); 

	    	if ( 0 > $.trim( json.url.length ) ) {
		        return;
		    }

		    $( '.wcv-image-container-' + id ).prop( 'src', json.sizes.full.url ); 		    
		    $( '#' + id ).val( json.sizes.full.url ); 

	    });

	    media_uploader.open();
	}
});