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
    global $wpdb, $table_prefix;

    foreach($results['posts'] as &$post ) {
	$post_content =  get_post_field('post_content', $post['post_id']);

	if(stristr( $post_content, "WpProQuiz") !== false ) {
            preg_match('/WpProQuiz\s*(\d+)/', $post_content, $matches);

            $quiz = $wpdb->get_var($wpdb->prepare("SELECT text FROM ". $table_prefix ."wp_pro_quiz_master WHERE id = %d LIMIT 1", $matches[1]));

            $post['post_content'] = $quiz;
            $post['post_excerpt'] = $quiz;

        } else {

            $post['post_content'] = apply_filters('the_content', get_post_field('post_content', $post['post_id']));
            $post['post_title'] = apply_filters('the_title', get_post_field('post_title', $post['post_id']));
	        $post['post_excerpt'] = apply_filters('the_excerpt', get_post_field('post_excerpt', $post['post_id']));
        }
    }

    return $results;
}
add_filter('ep_search_results_array', 'mla_style_custom_ep_search_results_array', 10, 2);

/**
 * Fix html that may be malformed due to preg_match.
 *
 * @param string $src The html to fix.
 */
function mla_style_custom_html_tidy( $src ){
    libxml_use_internal_errors(true);

    $x = new DOMDocument;
    $x->loadHTML('<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />'.$src);
    $x->formatOutput = true;

    $ret = preg_replace('~<(?:!DOCTYPE|/?(?:html|body|head))[^>]*>\s*~i', '', $x->saveHTML());

    return trim(str_replace('<meta http-equiv="Content-Type" content="text/html;charset=utf-8">','',$ret));
}

/**
 * Make sure open html tags are closed.
 *
 * @param string $html The html to fix.
 */
function mla_style_custom_close_tags( $html ) {
    preg_match_all('#<(?!meta|img|br|hr|input\b)\b([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);

    $openedtags = $result[1];
    preg_match_all('#</([a-z]+)>#iU', $html, $result);

    $closedtags = $result[1];
    $len_opened = count($openedtags);

    if (count($closedtags) == $len_opened) {
        return $html;
    }

	$openedtags = array_reverse($openedtags);

    for ($i=0; $i < $len_opened; $i++) {
        if (!in_array($openedtags[$i], $closedtags)) {
            $html .= '</'.$openedtags[$i].'>';
        } else {
            unset($closedtags[array_search($openedtags[$i], $closedtags)]);
        }
    }
    return $html;
}

/**
 * Allow html in excerpt.
 *
 * @param string $excerpt The excerpt.
 */
function mla_style_custom_wp_trim_excerpt( $excerpt ) {
    $raw_excerpt = $excerpt;

    if ( '' == $excerpt ) {

        $excerpt = get_the_content('');
        $excerpt = strip_shortcodes( $excerpt );
        $excerpt = apply_filters('the_content', $excerpt);
        $excerpt = str_replace(']]>', ']]&gt;', $excerpt);
        $excerpt = strip_tags($excerpt, '<i><em>'); /*IF you need to allow just certain tags. Delete if all tags are allowed */

        //Set the excerpt word count and only break after sentence is complete.
        $excerpt_word_count = 100;
        $excerpt_length = apply_filters('excerpt_length', $excerpt_word_count);
        $tokens = array();
        $excerpt_output = '';
        $count = 0;

        // Divide the string into tokens; HTML tags, or words, followed by any whitespace
        preg_match_all('/(<[^>]+>|[^<>\s]+)\s*/u', $excerpt, $tokens);

        foreach ($tokens[0] as $token) {

            if ($count >= $excerpt_length && preg_match('/[\,\;\?\.\!]\s*$/uS', $token)) {
            // Limit reached, continue until , ; ? . or ! occur at the end
                $excerpt_output .= trim($token);
                break;
            }

            // Add words to complete sentence
            $count++;

            // Append what's left of the token
            $excerpt_output .= $token;
        }

    $excerpt = trim( force_balance_tags( $excerpt_output ) );

    return $excerpt;

    }
    return apply_filters('mla_style_custom_wp_trim_excerpt', $excerpt, $raw_excerpt);
}

remove_filter('get_the_excerpt', 'wp_trim_excerpt');
add_filter('get_the_excerpt', 'mla_style_custom_wp_trim_excerpt');


/**
 * Filters WP_Query arguments for initial ElasticPress indexing.
 * Searches for posts with 'do_not_index' and adds those post IDs to 'posts__not_in' argument.
 * @param array $args
 * @return array
 */
function mla_style_custom_ep_filter( $args ) {
    $query   = new WP_Query( array( 'meta_key' => 'do_not_index', 'meta_value' => 'true', 'fields' => 'ids' ) );
    $exclude = array();
    if ( 0 != $query->post_count ) {
        $args[ 'post__not_in' ] = $query->posts;
    }
    return $args;
}

add_filter( 'ep_index_posts_args', 'mla_style_custom_ep_filter' );

/**
 * Filter to determine if a post should not be indexed.
 * @param bool $return_val Default is false.
 * @param array $post_args
 * @param int $post_id
 * @return boolean
 */
function mla_style_custom_ep_stop_sync( $return_val, $post_args, $post_id ) {
    $to_index    = get_post_meta( $post_id, 'do_not_index', true );
    $to_index    = filter_var( $to_index, FILTER_VALIDATE_BOOLEAN );

    if ( false === boolval( $to_index ) ) {
        return false;
    }

    if ( function_exists( 'ep_delete_post' ) ) {
        ep_delete_post( $post_id );
    }

    return true;
}

add_filter( 'ep_post_sync_kill', 'mla_style_custom_ep_stop_sync', 10, 3 );
