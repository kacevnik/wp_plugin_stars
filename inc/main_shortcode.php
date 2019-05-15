<?php

    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly.
    }

    function vzstars_shortcode( $atts ){

        extract( shortcode_atts( array(
            'title'       => '',
            'modal_title' => 'Оцените',
            'size'        => 'small',
            'id'          => '',
            'reiting'     => 0,
            'action'      => 'show',
            'class'       => ''
        ), $atts ) );

        // Creating

        $actions = array ( 'ahow', 'add' );
		$action = in_array( $action, $actions ) ? $action : 'show';
		
		switch ( $size ) {
			case 'large':
				$size_class = ' reviews_stars_large';
				$width = 160;
				break;
			case 'medium':
				$size_class = ' reviews_stars_medium';
				$width = 120;
				break;
			default:
				$size_class = ' reviews_stars_small';
				$width = 80;
				break;
		}

        // Creating title

        if ( $title || $title != '' ) {
        	$output_title = '<div class="reviews_stars_title">' . $title . '</div>';
        }else {
        	$output_title = $title;
        }

        if ( !$id || $id == '' ) {
        	echo 'В шорткоде не указан обязательный параметр ID';
        	return;
        }

		// creating main css class
		
		$output_class = '';

        if( $class != '' ){
        	$clases = explode( ',', $class );
        	foreach ( $clases as $class_item ) {
        		$output_class .= ' ' . $class_item;
        	}
        }else{
        	$output_class = $class;
        }

        $output  = '<!-- Reviews Stars Shortcode for #' . $id . '-->';
        $output .= $output_title . '<span class="reviews_stars' . $output_class . '" id="reviews_stars_' . $id . '">';

                        // creating action

        if ( $action == 'show' ) {

			global $comment;

			if ( get_the_ID() ) {
				$reiting_data = ( !get_post_meta( $id, 'vzs_reiting_data', true ) ) ? $reiting : unserialize( get_post_meta( $id, 'vzs_reiting_data', true ) ) ;
				$reiting = (double)$reiting_data['rating'];
			}

			if( $comment ) {
				if( get_comment_ID() ) {
					$reiting = ( !get_comment_meta( $id, 'cp_comment_rating', true ) ) ? (double)$reiting : get_comment_meta( $id, 'cp_comment_rating', true );
				}
			}
        	
        	$reiting = ( !$reiting || $reiting == '' ) ? 0 : $reiting;
        	$reiting = $reiting > 5 ? 5 : $reiting;

        	$output .= '<div class="reviews_stars_show_wrap' . $size_class . '">';
        	$output .= '<div class="reviews_stars_show_retair" style="width: ' . $width / 5 * $reiting  . 'px;" data-value="' . $reiting . '">';		
        	$output .= '</div>';
        	$output .= '</div><!-- end reviews_stars_show_wrap -->';
        }elseif ( $action == 'add' ) {

    		add_action( 'comment_form_logged_in_after', 'add_fields_for_vzstars' );
			add_action( 'comment_form_after_fields', 'add_fields_for_vzstars' );

        	$output .= '<div class="reviews_stars_add_wrap' . $size_class . '" data-width="' . $width . '" data-value="0">';
        	$output .= '<div class="reviews_stars_show_retair_add">';

        	$output .= '</div>';
        	$output .= '<div class="reviews_stars_add_hover">';

        	$output .= '</div>';
        	$output .= '</div><!-- end reviews_stars_show_wrap -->';

        	$output .= '<div class="modal fade" id="vzstars_modal" role="dialog" aria-labelledby="modal_vzstars" aria-hidden="true">';
        	$output .= '<div class="modal-dialog modal-sm"">';
        	$output .= '<div class="modal-content">';
        	$output .= '<div class="modal-header">';
        	$output .= '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
        	$output .= '<div class="modal-title" id="modal_vzstars">' . $modal_title . '</div>';
        	$output .= '</div><!-- /header -->';
        	$output .= '<div class="modal-body">';

        	$output .= '<div class="reviews_stars_add_wrap' . $size_class . '" data-width="' . $width . '" data-value="0">';
        	$output .= '<div class="reviews_stars_show_retair_add">';
        	$output .= '</div>';
        	$output .= '<div class="reviews_stars_add_hover">';
        	$output .= '</div>';
        	$output .= '</div><!-- end reviews_stars_show_wrap -->';

        	$output .= '</div>';
        	$output .= '</div><!-- /.modal-content -->';
        	$output .= '</div><!-- /.modal-dialog -->';
			$output .= '</div>';
			
			$output .= '<div class="modal fade" id="vzstars_modal_error" role="dialog" aria-labelledby="modal_vzstars" aria-hidden="true">';
        	$output .= '<div class="modal-dialog modal-sm"">';
        	$output .= '<div class="modal-content">';
        	$output .= '<div class="modal-header">';
        	$output .= '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
        	$output .= '<div class="modal-title" id="modal_vzstars">' . __( 'Ошибка', '' ) . '</div>';
        	$output .= '</div><!-- /header -->';
			$output .= '<div class="modal-body">';
			$output .= '</div>';
        	$output .= '<div class="modal-body-loader">';
        	$output .= '<img src="' . VZSATRS_IMG_DIR . 'loading.gif">';

        	$output .= '</div>';
        	$output .= '</div><!-- /.modal-content -->';
        	$output .= '</div><!-- /.modal-dialog -->';
        	$output .= '</div>';
        }

        $output.= '</span>';
        $output.= '<!-- End VyborZayma Stars Shortcode for #' . $id . '-->';

        return $output;

    }

    add_shortcode( 'vzstars', 'vzstars_shortcode' );

?>