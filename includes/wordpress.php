<?php
/**
 * Customizations to WordPress
 *
 * @package mla_style_custom
 */

/**
 * Change Author metabox text
 *
 **/
function mla_style_custom_add_meta_boxes() {
    global $wp_meta_boxes;
        
    $wp_meta_boxes['post']['normal']['core']['authordiv']['title']= 'Post Owner';
}
add_action( 'add_meta_boxes_post',  'mla_style_custom_add_meta_boxes' );

/**
 * Move sticky posts to the top
 *
 *@param object $posts The post object.
 **/
function mla_style_custom_bump_sticky_posts_to_top( $posts ) {
    $stickies = array();
    
    foreach($posts as $i => $post) {
        if(is_sticky($post->ID)) {
            $stickies[] = $post;
            unset($posts[$i]);
        }
    }
    return array_merge($stickies, $posts);
}

add_filter('the_posts', 'mla_style_custom_bump_sticky_posts_to_top');
