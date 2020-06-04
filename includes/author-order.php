<?php
/**
 * Adding custom author interface.
 *
 * @package MLA_Style_Custom
 */

/**
 * Register author order metabox.
 */
function mla_style_custom_register_meta_boxes() {
    add_meta_box( 'meta-box-id', __( 'MLA Author Order', 'textdomain' ), 'mla_style_custom_author_order_callback', 'post', 'side' );
}
add_action( 'add_meta_boxes', 'mla_style_custom_register_meta_boxes' );
 
/**
 * Meta box display callback
 *
 * @param WP_Post $post Current post object.
 */
function mla_style_custom_author_order_callback( $post ) {
    $post_id = get_the_ID();
    echo '<ul id="author-order-terms">';

    $terms = get_the_terms( $post_id, 'mla_author' );
    
    if( false == $terms) {
	return false;
    }

    $ordered_terms = get_post_meta( $post_id, '_term_order', true );
     
    if( empty( $ordered_terms )  ) {

        foreach ( $terms as $term ) {
            echo '<li class="item" id="term-'.$term->term_id.'"><span>'. $term->name .'</span></li>';        
        } 
    } else {
        $terms = $ordered_terms[ 'term_order' ];
        $term_ids = explode ( ",", $terms );

        for( $i = 0; $i <  count ( $term_ids ); $i++ ) {
            $term = get_term( $term_ids[$i], $taxonomy, OBJECT);
            echo '<li class="item" id="term-'.$term->term_id.'"><span>'. $term->name .'</span></li>';        
         }
     }
     echo '</ul>';
     echo '<a href="javascript: void(0); return false;" id="save_term_order" class="button-primary">Update Order</a>';
}

/**
 * Admin enqueue scripts
 *
 * @param string $hook The current admin page..
 */
function mla_style_custom_author_order_enqueue_admin_script( $hook ) {
    $jtime = filemtime( dirname(__FILE__) . '/js/author-sortable.js'  );
    $ctime = filemtime( dirname(__FILE__) . '/css/mla-style-custom.css' );
    
    wp_enqueue_script( 'jquery-ui-sortable' );
    wp_enqueue_script('author-order-script', plugin_dir_url( __FILE__ ) . 'js/author-sortable.js', array('jquery','jquery-ui-sortable'), $jtime );
    wp_enqueue_style('author-order-css', plugin_dir_url( __FILE__ ) . 'css/mla-style-custom.css',[], $ctime);
}
add_action( 'admin_enqueue_scripts', 'mla_style_custom_author_order_enqueue_admin_script' );

add_action( 'wp_ajax_save_term_order', 'mla_style_custom_term_order_save' );

/**
 * Term order save. 
 *
 */
function mla_style_custom_term_order_save() {
    global $wpdb;
    
    $wpdb->flush();
    $item_id = $_POST['post_id'];
    $meta_key = '_term_order';

    $term_order = $_POST[ 'order' ];
    $str = str_replace( "term-", "", $term_order );
    $int = str_replace( "'", "", $str );

    update_post_meta( $item_id, $meta_key, array( 'term_order' => $int ) );

    $response = '<p>Term order updated</p>';
    echo $response;

    die(1);
}

/**
 * Update author order meta.
 *
 * Makes sure the author entries are in sync on post save.
 */
function mla_style_custom_update_author_order_meta() {

    $term_ids = array();
    $term_objs = get_the_terms( get_the_ID(), 'mla_author' );

    if( false == $term_objs ) {
        return;
    }
    // get_the_terms returns an array of WP_Term objects
    foreach ($term_objs as $term_obj)
        $term_ids[] = $term_obj->term_id;

    // get the ids in the post meta
    $ordered_terms = get_post_meta( get_the_ID(), '_term_order', true );

    if( empty( $ordered_terms )  ) {
        return; 
    }

    $terms = $ordered_terms[ 'term_order' ];
    $ordered_term_ids = explode( ",", $terms );
    
    $result = array_diff($ordered_term_ids, $term_ids);
    
    if(!empty($result)) {
       $result = array_diff($ordered_term_ids, $result);
    } 
    $result2 = array_unique(array_merge($result, $term_ids));
    $int = implode( ",", $result2 );
  
    update_post_meta( get_the_ID(), '_term_order', array( 'term_order' => $int ) );
}

add_action( 'save_post', 'mla_style_custom_update_author_order_meta' );
