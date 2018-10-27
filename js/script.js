jQuery( document ).ready( function( $ ) {

	$( '.vyborzayma_stars_add_wrap' ).mousemove( function( e ) {

		var pos = $( this ).offset();
    	var elem_left = pos.left;
    	var X = e.pageX - elem_left;
    	var width_container = $( this ).attr( 'data-width' );
    	var width = Math.ceil( X / ( width_container / 5 ) )*( width_container / 5 );
		$( this ).find( '.vyborzayma_stars_add_hover' ).show().width( width );
	}); 


	$( '.vyborzayma_stars_add_wrap' ).mouseleave( function( e ) {
		$( '.vyborzayma_stars_add_hover' ).hide().width( 0 );
	});

	$( '.vyborzayma_stars_add_hover' ).on('click', function( e ) {
		var pos = $( this ).offset();
    	var elem_left = pos.left;
    	var X = e.pageX - elem_left;
    	var width_container = $( this ).parent().attr( 'data-width' );
    	var width = Math.ceil( X / ( width_container / 5 ) )*( width_container / 5 );
    	$( this ).parent().attr( 'data-value', Math.ceil( X / ( width_container / 5 ) ) );
    	$( '.vyborzayma_stars_show_retair_add' ).width( width );
    	$( '#vzstars' ).val( Math.ceil( X / ( width_container / 5 ) ) );
    	$('#vzstars_modal').modal('hide');
	});

	var commentform = $( '#commentform' );

	commentform.submit( function( e ) {

		var formdata  = commentform.serialize();
		var formurl   = commentform.attr( 'action' );
		var complete  = false;
		var value     = $( '#vzstars' ).val();
		var id        = $( '#vzstars_post_id' ).val();

		if( !value ){
			$( '#vzstars_modal' ).modal();
			return false;
		}else {
			return true;
		}
	});

});