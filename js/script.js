jQuery( document ).ready( function( $ ) {

	$( '.reviews_stars_add_wrap' ).mousemove( function( e ) {

		var pos = $( this ).offset();
    	var elem_left = pos.left;
    	var X = e.pageX - elem_left;
    	var width_container = $( this ).attr( 'data-width' );
    	var width = Math.ceil( X / ( width_container / 5 ) )*( width_container / 5 );
		$( this ).find( '.reviews_stars_add_hover' ).show().width( width );
	}); 


	$( '.reviews_stars_add_wrap' ).mouseleave( function( e ) {
		$( '.reviews_stars_add_hover' ).hide().width( 0 );
	});

	$( '.reviews_stars_add_hover' ).on('click', function( e ) {
		var pos = $( this ).offset();
    	var elem_left = pos.left;
    	var X = e.pageX - elem_left;
    	var width_container = $( this ).parent().attr( 'data-width' );
    	var width = Math.ceil( X / ( width_container / 5 ) )*( width_container / 5 );
    	$( this ).parent().attr( 'data-value', Math.ceil( X / ( width_container / 5 ) ) );
    	$( '.reviews_stars_show_retair_add' ).width( width );
    	$( '#vzstars' ).val( Math.ceil( X / ( width_container / 5 ) ) );
    	$('#vzstars_modal').modal('hide');
	});

	function addAjaxCoomentStar( value, id, name, email, message, parent=0 ) {
	
		var data = {
			action: 'add_ajax_comment_star',
			rating: value,
			post_id: id,
			name: name,
			email: email,
			message: message,
			parent: parent,
			nonce_code : raitingStar.nonce
		};
		$( '#vzstars_modal_error' ).modal();

		//Send value to the Server
		jQuery.post( raitingStar.url, data, function( response ) {

			if ( response == 0 ) {
				$( '#vzstars_modal_error .modal-body' ).html( 'Ошибка добавления комментария!' );
			}

			if ( response == 1 ) {
				$( '#vzstars_modal_error .modal-title' ).html( '' );
			}
		});	
	
	}

	var commentform = $( '#commentform' );

	commentform.submit( function( e ) {
		let value     = $( '#vzstars' ).val();
		let id        = $( '#vzstars_post_id' ).val();

		let formdata  = commentform.serialize();
		let formurl   = commentform.attr( 'action' );
		let complete  = false;

		let name      = $( '#author' ).val();
		let email     = $( '#email' ).val();
		let comment   = $( '#comment' ).val();
		let parent   = $( '#comment_parent' ).val();
		let textError = '';

		let re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

		if( !email ) {
			textError += 'Заполните email' + "<br>";
		}else{
			if( !re.test( email ) ){
				textError += 'Необходимо корректно указать Ваш email' + "<br>";
			}
		}

		if( !name ) {
			textError += 'Заполните Ваше Имя' + "<br>";
		}

		if( !comment ) {
			textError += 'Укажите сообщение' + "<br>";
		}

		if( !value ){
			$( '#vzstars_modal' ).modal();
			return false;
		}else if( textError != '' ){
			$( '#vzstars_modal_error .modal-body' ).html( textError );
			$( '#vzstars_modal_error' ).modal();
			return false;
		}else{
			$( '#vzstars_modal_error .modal-body-loader' ).show();
			$( '#vzstars_modal_error .modal-body' ).html( '' );
			$( '#vzstars_modal_error .modal-body-loader' ).hide();
			$( '#vzstars_modal_error .modal-title' ).html( '<span style="color: green;">Комментарий добавлен</span>' );
			$( '#vzstars_modal_error .modal-body' ).html( 'После проверки, комментарий будет опубликован' );
			$( '#comment' ).val('');
			$( '#author' ).val('');
			$( '#email' ).val('');
			$( '#vzstars' ).val('');
			addAjaxCoomentStar( value, id, name, email, comment, parent );
			return false;
		}
	});

});