<?php

/**
 * Plugin Name: VyborZayma Stars
 * Plugin URI: https://wyborzayma.ru
 * Description: Плагина вывода рейтинга для комментариев и постов
 * Version: 1.0.0
 * Author: Dmitriy Kovalev
 * Author URI: https://wyborzayma.ru
 * #309
 *
/*/

if ( !defined( 'ABSPATH' ) ) {
    exit( );
}

define( "VZSATRS_ABSOLUTE_PATH", dirname( __FILE__ ) );

define( "VZSATRS_RELATIVE_PATH", dirname( plugin_basename( __FILE__ ) ) );

define( "VZSATRS_JS_DIR", plugins_url() . '/' . VZSATRS_RELATIVE_PATH . '/js/' );

define( "VZSATRS_CSS_DIR", plugins_url() . '/' . VZSATRS_RELATIVE_PATH . '/css/' );

define( "VZSATRS_IMG_DIR", plugins_url() . '/' . VZSATRS_RELATIVE_PATH . '/img/' );

require VZSATRS_ABSOLUTE_PATH . '/inc/main_shortcode.php';
    
    add_action( 'wp_enqueue_scripts', 'enqueue_cusiom_scripts' );

    if( !function_exists( 'enqueue_cusiom_scripts') ) {

        function enqueue_cusiom_scripts() {

            if(is_admin()) return false;

            wp_enqueue_style( 'vzs-styles', VZSATRS_CSS_DIR . 'style.css', array(), '1.0.0', 'all' );

            wp_enqueue_script( 'vzs-script', VZSATRS_JS_DIR . 'script.js', array('jquery'), '1.0.0', true );

        }

    }


	if( !function_exists( 'add_fields_for_vzstars' ) ) {

    	function add_fields_for_vzstars ( $fields ){

    		global $post;

			echo '<input type="hidden" id="vzstars" name="vzstars">';
			echo '<input type="hidden" id="vzstars_post_id" name="vzstars_post_id" value="' . $post->ID . '">';

			return $fields;
		}
	}	


    function vzstars_save_comment_meta ( $comment_id ){

		if( !empty( $_POST['vzstars'] ) && !empty( $_POST['vzstars_post_id'] ) ){

			$rating = intval( $_POST['vzstars'] );
			$id = intval( $_POST['vzstars_post_id'] );
			add_comment_meta( $comment_id, 'vzs_reiting_comment', $rating );

			if ( ! get_post_meta( $id, 'vzs_reiting_data', true ) ) {
				$data['count'] = 1;
				$data['total'] = $rating;
				$data['rating'] = $rating;
				update_post_meta( $id, 'vzs_reiting_data', $data );
				update_post_meta( $id, 'vzs_reiting', $rating );
			}else{
				$data = get_post_meta( $id, 'vzs_reiting_data', true );
				$data['count'] ++;
				$data['total'] = $data['total'] + $rating;
				$data['rating'] = $data['total'] / $data['count'];
				update_post_meta( $id, 'vzs_reiting_data', $data );
				update_post_meta( $id, 'vzs_reiting', $data['rating'] );
			}

		}

	}

	add_action( 'comment_post', 'vzstars_save_comment_meta' );


    