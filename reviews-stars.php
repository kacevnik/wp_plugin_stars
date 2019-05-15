<?php

/**
 * Plugin Name: Reviews Stars
 * Plugin URI: https://github.com/kacevnik/wp_plugin_stars/tree/master
 * Description: Плагина вывода рейтинга для комментариев и постов
 * Version: 2.0.0
 * Author: Dmitriy Kovalev
 * Author URI: https://approve.media
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

            if( is_admin() ) return false;

            wp_enqueue_style( 'vzs-styles', VZSATRS_CSS_DIR . 'style.min.css', array(), '1.0.0', 'all' );

			wp_enqueue_script( 'vzs-script', VZSATRS_JS_DIR . 'script.min.js', array( 'jquery' ), '1.0.0', true );
			
			wp_localize_script( 'vzs-script', 'raitingStar', 
				array(
					'url'   => admin_url( 'admin-ajax.php' ),
					'nonce' => wp_create_nonce( 'raitingStar-nonce' )
				)
			); 

        }

    }

	if( !function_exists( 'add_fields_for_vzstars' ) ) {

    	function add_fields_for_vzstars ( $fields ){

    		global $post;

			echo '<input type="hidden" id="vzstars" name="vzstars">';
			echo '<input type="hidden" id="vzstars_post_id" name="vzstars_post_id" value="' . $post->ID . '">';
			echo '<input type="hidden" name="comment_parent" />';

			return $fields;
		}
	}
	
	if( wp_doing_ajax() ){
		add_action( 'wp_ajax_add_ajax_comment_star', 'add_ajax_comment_star' );
		add_action( 'wp_ajax_nopriv_add_ajax_comment_star', 'add_ajax_comment_star' );
	}

	function add_ajax_comment_star(){
		check_ajax_referer( 'raitingStar-nonce', 'nonce_code' );

		$comment_parent = isset($_POST['parent']) ? absint($_POST['parent']) : 0;

		if( $_POST['rating'] && $_POST['post_id'] && $_POST['nonce_code'] && $_POST['name'] && $_POST['message'] && $_POST['email'] ){
			$id_comment = wp_new_comment( wp_slash( array(
					'comment_post_ID' => $_POST['post_id'],
					'comment_author'  => $_POST['name'],
					'comment_approved' => 0,
					'comment_content' => $_POST['message'],
					'comment_author_email' => $_POST['email'],
					'comment_parent' => $comment_parent
				)
			)
			);

			update_comment_meta( $id_comment, 'cp_comment_rating', (int)$_POST['rating'] );

			$data = unserialize( get_post_meta( $_POST['post_id'], 'vzs_reiting_data', true ) );

			if ( !isset( $data ) || !$data ) {
				$data['count'] = 1;
				$data['total'] = $_POST['rating'];
				$data['rating'] = $_POST['rating'];
				update_post_meta( $_POST['post_id'], 'vzs_reiting_data', serialize( $data ) );
			} else {
				$data['count'] ++;
				$data['total'] = $data['total'] + $_POST['rating'];
				$data['rating'] = $data['total'] / $data['count'];
				update_post_meta( $_POST['post_id'], 'vzs_reiting_data', serialize( $data ) );
			}
		} else {
			echo 0;
			wp_die();
		}

		echo 1;

		wp_die();
	}

	add_action( 'delete_comment', 'delete_meta_comment', 10, 2 );
	function delete_meta_comment( $comment_id, $comment ){
		$data = unserialize( get_post_meta( $comment->comment_post_ID, 'vzs_reiting_data', true ) );
		$raiting = get_comment_meta( $comment_id, 'cp_comment_rating', true );

		$data['count'] --;
		$data['total'] = $data['total'] - $raiting;
		if( $data['count'] == 0 ) {
			$data['rating'] = 0;
		} else {
			$data['rating'] = $data['total'] / $data['count'];
		}
		
		update_post_meta( $comment->comment_post_ID, 'vzs_reiting_data', serialize( $data ) );
	}

	if ( !function_exists( 'comments_list_text' ) ) {
        function comments_list_text( $comment, $args, $depth ) {
            global $wp;
            $GLOBALS['comment'] = $comment;

            $city      = get_comment_meta( $comment->comment_ID, 'geo_data', true );
            $rating    = get_comment_meta( $comment->comment_ID, 'cp_comment_rating', true );
            $city_name = !empty( $city['city_name'] ) ? $city['city_name'] : '';

            if ( 'div' == $args['style'] ) {
                $tag = 'div';
                $add_below = 'comment';
            } else {
                $tag = 'li';
                $add_below = 'div-comment';
            } 

        ?>

        <<?php echo $tag ?> <?php comment_class( '', $comment ); ?> id="comment-<?php comment_ID(); ?>">
        <?php if ( 'div' != $args['style'] ) { ?>
            <div id="div-comment-<?php comment_ID(); ?>" class="comment-body">
        <?php } ?>
        <?php if ( '0' == $comment->comment_approved ) : ?>
            <em class="comment-awaiting-moderation"><?php _e( 'Ваш комментарий ожидает модерации.' ) ?></em>
            <br />
        <?php endif; ?>

        <div class="comment-author vcard">
            <img src="<?php echo '/wp-content/themes/'.get_stylesheet().'/img/comment_avatar.png'; ?>" class="avatar avatar-113 photo">
            <span class="name"><span class="comment-author-name"><?php echo get_comment_author_link( $comment ) ?></span></span>
            <span><?php echo $city_name ?></span>
            <span><?php echo get_comment_date( '', $comment ) ?></span>
            <?php echo do_shortcode( '[vzstars id="' . $comment->comment_ID . '" class="comment_list_' . get_post_type() . '"]' ); ?>
        </div>

        <?php comment_text( get_comment_id(), array_merge( $args, array( 'add_below' => $add_below, 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>

        <?php
        if( !empty( $wp->query_vars['reviews'] ) ) {
            comment_reply_link( array_merge( $args, array(
                'add_below' => $add_below,
                'depth'     => $depth,
                'max_depth' => $args['max_depth'],
                'before'    => '<div class="reply">',
                'after'     => '</div>'
            ) ) );
        } else {
            if( !empty( $wp->query_vars['reviews'] ) ) { $slug ='/reviews/'; }
            if( !empty( $wp->query_vars['otzyv'] ) ) { $slug ='/otzyv/'; }
            if( !empty( $wp->query_vars['otzyvy'] ) ) { $slug ='/otzyvy/'; }
            ?>

            <?php

            $cm_url = explode("/",$_SERVER['REQUEST_URI']);

            ?>

            <div class="reply">
            <?php
            comment_reply_link(
                array_merge(
                    $args,
                    array(
                        'add_below' => $add_below,
                        'depth'     => $depth,
                        'max_depth' => $args['max_depth'],
                        'before'    => '<span class="icon-back"></span>',
                    )
                )
            ); ?>
            </div><?php

        } ?>

        <?php if ( 'div' != $args['style'] ) { ?>
            </div>
        <?php }

	}
	
	
	//Вывод списка комметариев
	if ( !function_exists( 'comments_list_star_raiting' ) ){
		function comments_list_star_raiting() {

			global $post;

			$page = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

			$args = array(
				'walker'            => null,
				'max_depth'         => '',
				'style'             => 'ul',
				'callback'          => 'comments_list_text',
				'end-callback'      => null,
				'type'              => 'comment',
				'reply_text'        => __( 'Ответить', '' ),
				'page'              => $page,
				'per_page'          => '25',
				'avatar_size'       => 32,
				'reverse_top_level' => null,
				'reverse_children'  => '',
				'format'            => 'html5',  // или xhtml, если HTML5 не поддерживается темой
				'short_ping'        => false,    // С версии 3.6,
				'echo'              => true,     // true или false
			);

			$args_2 = array(

				'post_id' => $post->ID,
				'status'  => 'approve'

			);

			$comments =  get_comments( $args_2 );

			wp_list_comments( $args, $comments );

			$args_pogin = array(
				'prev_text'    => __( '«' ),
				'next_text'    => __( '»' ),
				'format'       => null,
				'base'         => get_the_permalink() . 'otzyvy/%_%',
				'format'       => 'cpage/%#%/',
				'total'        => ceil( count( $comments ) / 25 ),
				'current'      => $page,
				'echo'         => true,
				'mid_size'     => 1,
				'end_size'     => 0,
				'add_fragment' => '',
			);

			echo '<div class="vz_pagination">';

			paginate_comments_links( $args_pogin );

			echo '</div>';

		}
	}

}


    