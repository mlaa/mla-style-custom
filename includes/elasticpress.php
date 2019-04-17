<?php
/**
 * Customizations to elasticpress plugin.
 *
 * @package MLA_Style_Custom
 */

/**
 * Forcing EP to get content from database instead of index.
 *
 * @param array  $results Array with found post content.
 * @param array  $respose Array with EP_query response.
 *
 * @return array
 */
function mla_style_custom_ep_search_results_array( $results, $response ) {
    foreach($results[posts] as &$post ) {
        $post[post_content] = apply_filters('the_content', get_post_field('post_content', $post[post_id]));
        $post[post_title] = apply_filters('the_title', get_post_field('post_title', $post[post_id])); 
    }
    return $results;
}
add_filter('ep_search_results_array', 'mla_style_custom_ep_search_results_array', 10, 2);
