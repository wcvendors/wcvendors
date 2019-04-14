jQuery(function( $ ) {

    // Quick Edit 
	$( '#the-list' ).on( 'click', '.editinline', function() {

		 if ( $('.inline-edit-author').length && $('.inline-edit-author-vendor').length ) { 
            $( '.inline-edit-author').hide(); 
            $( '.inline-edit-author').attr( 'class', 'inline-edit-author-old' ); 
            $( 'select[name=post_author]').attr( 'name', 'post_author-old' );
            $( '.inline-edit-author-vendor').attr( 'class', 'inline-edit-author' );
            $( 'select[name=post_author-vendor]').attr( 'name', 'post_author' );
        } 

		var post_id = $( this ).closest( 'tr' ).attr( 'id' );
		post_id = post_id.replace('post-', '');
        console.log ( post_id ); 
		var $wcv_inline_data = $( '#wcvendors_inline_' + post_id );

        console.log( $wcv_inline_data ); 

		var vendor_id           = $wcv_inline_data.find( '.vendor_id' ).text(), 
            commission_rate     = $wcv_inline_data.find( '.commission_rate' ).text();

            console.log( commission_rate ); 

        // Set values 
		$('select[name="post_author"] option[value="' + vendor_id + '"]', '.inline-edit-row').attr( 'selected', 'selected' );
        $( 'input[name="_wcv_commission_rate"]', '.inline-edit-row' ).val( commission_rate );

	});

    // Bulk Edit 
	$( '#wpbody' ).on( 'click', '#doaction, #doaction2', function() {
		 if ( $('.inline-edit-author').length && $('.inline-edit-author-vendor').length ) { 
            $( '.inline-edit-author').hide(); 
            $( '.inline-edit-author').attr( 'class', 'inline-edit-author-old' ); 
            $( 'select[name=post_author]').attr( 'name', 'post_author-old' );
            $( '.inline-edit-author-vendor').attr( 'class', 'inline-edit-author' );
            $( 'select[name=post_author-vendor]').attr( 'name', 'post_author' );
        } 
	}); 

}); 