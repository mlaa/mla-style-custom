<?php
/**
 * Customizations to elasticpress plugin.
 *
 * @package MLA_Style_Custom
 */
/*
**
 * Register meta box(es).
 */
function mla_style_custom_register_meta_boxes() {
    add_meta_box( 'meta-box-id', __( 'Author Order', 'textdomain' ), 'mla_style_custom_author_order_callback', 'post' );
}
add_action( 'add_meta_boxes', 'mla_style_custom_register_meta_boxes' );
 
/**
 * Meta box display callback.
 *
 * @param WP_Post $post Current post object.
 */
function mla_style_custom_author_order_callback( $post ) {
	 $post_id = get_the_ID();
 	 echo '<ul id="author-order-terms">';
	
	 $terms = get_the_terms( $post_id, 'mla_author' );
         foreach ( $terms as $term ) {
             echo '<li class="item" id="term-'.$term->term_id.'"><span>'. $term->name .'</span></li>';        
         }
         echo '</ul>';
         echo '<a href="javascript: void(0); return false;" id="save_term_order" class="button-primary">Update Order</a>';
}

function mla_style_custom_author_order_enqueue_admin_script( $hook ) {
    $jtime = filemtime( dirname(__FILE__) . '/js/author-sortable.js'  );
    $ctime = filemtime( dirname(__FILE__) . '/css/mla-style-custom.css' );
    
    wp_enqueue_script( 'jquery-ui-sortable' );
    wp_enqueue_script('author-order-script', plugin_dir_url( __FILE__ ) . 'js/author-sortable.js', array('jquery','jquery-ui-sortable'), $jtime );
    wp_enqueue_style('author-order-css', plugin_dir_url( __FILE__ ) . 'css/mla-style-custom.css',[], $ctime);
}
add_action( 'admin_enqueue_scripts', 'mla_style_custom_author_order_enqueue_admin_script' );

add_action ( 'wp_ajax_save_term_order', 'term_order_save' );
function term_order_save() {
    global $wpdb;
    
    $wpdb->flush();
    $item_id = $_POST['post_id'];
    $meta_key = '_term_order';

    $order = $_POST[ 'order' ];
    $str = str_replace( "term-", "", $order );
    $int = str_replace( "'", "", $str );

    update_post_meta( $item_id, $meta_key, array( 'term_order' => $int ) );

    $response = '<p>Term order updated</p>';
    echo $response;

    die(1);
}
