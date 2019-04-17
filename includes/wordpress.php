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
