<?php
/**
 * In post tweet editor for edit flow. Allows a user to create a post and add the body and image for that promotional
 * tweet inside that post edit page. The tweet will be published when the post id published. Likewise the tweet body
 * should be added to the post's edit flow calender hover pagelet. User: jbetancourt Date: 11/20/18 Time: 3:00 PM
 *
 * @package MLA Style Twitter Post Editor
 * @version 1.0.1212018
 */

class STYLE_TWITTER_POST_EDITOR {

	// ###################### Magic ########################

	// Used in get / set magic
	private $prefix = "hc_stpe_";

	// Hold the class instance.
	private static $instance = null;

	// Hold the magic fields
	private $data = array();

	function __construct() {
		if(!class_exists( 'Normalizer')) {
			require_once('normalizer.php');
		}
		$this->hc_stpe_setup_variables();
		$this->hc_stpe_actions_and_filters();
	}

	public static function getInstance() {
		/** This is only for the MLA Style Center blog so we make sure we are on the correct blog site before continuing. */
		if ( ! self::hc_stpe_is_mla_style_site() ) {
			return false;
		}

		if ( self::$instance == null ) {
			self::$instance = new STYLE_TWITTER_POST_EDITOR();
		}

		return self::$instance;
	}

	public function __set( $name, $value ) {
		$name                = "hc_stpe_" . $name;
		$this->data[ $name ] = $value;
	}

	public function __unset( $name ) {
		$name = "hc_stpe_" . $name;
		unset( $this->data[ $name ] );
	}

	public function __get( $name ) {
		$name = "hc_stpe_" . $name;
		if ( array_key_exists( $name, $this->data ) ) {
			return $this->data[ $name ];
		}

		$trace = debug_backtrace();
		trigger_error(
			'Undefined property via __get(): ' . $name .
			' in ' . $trace[0]['file'] .
			' on line ' . $trace[0]['line'],
			E_USER_NOTICE );

		return null;
	}

	public function get( $param ) {
		return $this->$param;
	}

	// ###################### Plugin starts here ########################

	/**
	 * Variables used through out the class are stored in magic vars and accessible using the $this->fieldname within
	 * the class.
	 *
	 * @since   1.0.1142018
	 *
	 * @used-by __construct()
	 * @defines $this->tweet_field
	 * @defines $this->tweet_isSent_field
	 * @return void
	 */
	public function hc_stpe_setup_variables() {
		$this->tweet_field                = "txt_tweet";
		$this->tweet_isSent_field         = "b_tweet_isSent";
		$this->twitter_consumer_key       = null;
		$this->twitter_consumer_secret    = null;
		$this->twitter_oauth_token        = null;
		$this->twitter_oauth_token_secret = null;
	}

	/**
	 * Load WP filters and actions
	 *
	 * @since   1.0.1142018
	 *
	 * @used-by __construct()
	 */
	public function hc_stpe_actions_and_filters() {
		add_action( 'add_meta_boxes', array( $this, 'hc_stpe_post_metabox_add' ) );
		add_filter( 'get_user_option_meta-box-order_post', array( $this, 'hc_stpe_post_metabox_order' ) );
		add_action( 'save_post', array( $this, 'hc_stpe_post_metabox_save' ), 10, 3 );
		add_action( 'transition_post_status', array( $this, 'hc_stpe_on_published' ), 10, 3 );
		add_filter( 'ef_calendar_item_information_fields', array(
			$this,
			'hc_stpe_add_tweet_to_edit_flow_calendar'
		), 10, 2 );
		//add_action( 'admin_enqueue_scripts', array( $this, 'hc_stpe_post_editor_js_css' ) );
	}

	/**
	 * Private function to determine if the current site is MLA Style site.
	 *
	 * @since   1.0.1142018
	 *
	 * @used-by getInstance()
	 * @return bool
	 */
	private static function hc_stpe_is_mla_style_site() {
		$blog = get_blog_details( get_current_blog_id() );

		return $blog->blogname == "The MLA Style Center" ? true : false;
	}

	/**
	 * Adds the meta box(es) to the admin page.
	 *
	 * @since   1.0.1142018
	 *
	 * @used-by add_action( 'add_meta_boxes' )
	 * @uses    get_current_screen()
	 * @uses    add_meta_box()
	 * @uses    $this->prefix
	 */
	public function hc_stpe_post_metabox_add() {
		$screen = get_current_screen();
		if ( $screen->post_type != 'post' ) {
			return;
		}
		add_meta_box(
			$this->prefix . 'tweet_manage',
			_x( 'Twitter Post', 'Add a Twitter Post for this Article', $this->prefix . 'plugin' ),
			array( $this, 'hc_stpe_twitter_editor' ),
			$screen->id,
			'side',
			'core'
		);
	}

	/**
	 * The metabox html call back function
	 * This function places the twitter form unto the left side bar for default posts.
	 *
	 * @since   1.0.1142018
	 *
	 * @used-by hc_stpe_post_metabox_add()
	 * @uses    get_post_meta()
	 * @uses    get_the_ID()
	 * @uses    get_the_permalink()
	 * @uses    get_the_title()
	 * @uses    Normalizer::isNormalized()
	 * @uses    Normalizer::normalize()
	 * @uses    wp_editor()
	 * @uses    hc_stpe_after_tinymce_settings_js()
	 * @uses    get_post()
	 * @uses    preg_replace()
	 * @uses    strlen()
	 * @uses    $this->tweet_field
	 * @uses    $this->tweet_isSent_field
	 */
	public function hc_stpe_twitter_editor() {
		?>
        <div id='twitter_wrapper'><?php
		/**
		 * We default it with existing text field if it exists,
		 * otherwise we check to see if a title is in place in order to
		 * add a default permalink. If no title we leave it blank.
		 */
        $post_id = get_the_ID();
        $post_meta = get_post_meta( $post_id );
        error_log( print_r($post_meta,true));
		$existing = get_post_meta( $post_id, $this->tweet_field, true );
		$tmp      = $existing ?: "<a href='" . get_the_permalink() . "'>" . get_the_title() . "</a>";
		$url      = get_the_title() ? $tmp : '';
		if ( ! Normalizer::isNormalized( $url, Normalizer::FORM_C ) ) {
			$url = Normalizer::normalize( $url, Normalizer::FORM_C );
		}

		wp_editor( $url, $this->tweet_field . '_id', array(
			'wpautop'             => false,
			'media_buttons'       => true,
			'default_editor'      => 'tinymce',
			'drag_drop_upload'    => true,
			'textarea_name'       => $this->tweet_field,
			'textarea_rows'       => 15,
			'tabindex'            => '',
			'tabfocus_elements'   => ':prev,:next',
			'editor_css'          => '',
			'editor_class'        => '',
			'teeny'               => true,
			'dfw'                 => false,
			'_content_editor_dfw' => false,
			'tinymce'             => array(
				'setup'          => $this->hc_stpe_after_tinymce_settings_js(),
				'valid_children' => "+a[img|a]",
				'toolbar'        => false,
				'statusbar'      => false
			),
			'quicktags'           => false
		) );
		$is_already_sent = get_post_meta( $post_id, $this->tweet_isSent_field, true );
		error_log($this->tweet_isSent_field);
		$status  = get_post_meta( $post_id, $this->tweet_isSent_field, true ) === true ?"<span style='font-weight: bold'>Tweeted<span>": "<span style='font-style: italic'>Pending Tweet...</span>";
		$content = preg_replace( "/(<[a-zA-Z\/][^<>]*>|\[([^\]]+)\])/i", "", $url );
		$count   = 280 - strlen( $content );
		?>
        <div style='float:left'><?php echo $status; ?></div>
        <div style='float:right'>
            <span id='tweetCnt' style='width: 40px'/><?php echo $count; ?></span> Chars Left
        </div>
        <br style='clear:both'/>
        </div><?php // wrapper div close
	}

	/**
	 * Because we use a secondary TinyMCE for the twitter textarea we need to add a JS event handler
	 * directly inside the actual TinyMCE setup key in the php wp_editor method's settings arg.
	 *
	 * @since   1.0.1142018
	 *
	 * @used-by hc_stpe_twitter_editor()
	 * @return string
	 */
	private function hc_stpe_after_tinymce_settings_js() {
		/** This is a JS function passed to the TINYMCE setup during the wp_editor init*/
		return "[function(ed) {
		    const max = 280;
            ed.on('keypress', function(e) {
                let content = ed.getContent().replace(/(<[a-zA-Z\/][^<>]*>|\[([^\]]+)\])/ig,''); 
                let len = content.length;
                let count = document.getElementById('tweetCnt');
                let diff = max - len; 
                if ( diff < 0 && e.code != 'Backspace') {
                    tinymce.dom.Event.cancel(e);
                }    
                count.innerHTML = diff;    
            });
        }][0]";
	}

	/**
	 * We need to reorder the twitter metabox so that it's directly underneath the publish button.
	 * Any metabox NOT added to the array below will show up underneath those that are.
	 *
	 * @since   1.0.1142018
	 *
	 * @used-by add_action('get_user_option_meta-box-order_post' )
	 * @uses    join()
	 * @uses    $this->prefix
	 *
	 * @param $order
	 *
	 * @return array
	 */
	function hc_stpe_post_metabox_order( $order ) {
		return array(
			'side' => join(
				",",
				array(       // Arrange here as you desire
					'submitdiv', /* This is the submit/publish box */
					$this->prefix . 'tweet_manage',
				)
			),
		);
	}

	/**
	 * Saves the twitter field if conditions allow it.
	 *
	 * @since   1.0.1142018
	 *
	 * @used-by add_action( 'save_post' )
	 * @uses    get_current_screen()
	 * @uses    current_user_can()
	 * @uses    hc_stpe_remove_p_tag()
	 * @uses    hc_stpe_of_kses_data()
	 * @uses    hc_stpe_modify_tweet_metadata()
	 * @uses    $this->tweet_field()
	 * @defines $this->tweet_field_value
	 * @defines $this->post_id
	 *
	 * @param $post_id
	 * @param $post
	 * @param $update
	 */
	public function hc_stpe_post_metabox_save( $post_id, $post, $update ) {
		/**
		 *  We only want to save for Draft and Future statuses.
		 *  After it's in a publish state we can't edit Tweets on Twitter, so we don't want to save it again.
		 */

		$is_already_sent = get_post_meta( $post->ID, $this->tweet_isSent_field, true ) === true;
		$tweet_field_value   = ! empty( $_POST[ $this->tweet_field ] ) ? self::hc_stpe_remove_p_tag( self::hc_stpe_of_kses_data( $_POST[ $this->tweet_field ] ) ) : false;

		/**
		 * We want to save the tweet field only when the criteria is met
		 * - The post has never been sent before.
		 * AND
		 * - There is a tweet field value to save
		 */
		if ( $is_already_sent || ! $tweet_field_value ) {
			error_log( 'STPE ERROR 001: Post based Tweet save disabled' );
			return;
		}

		/** Only run on default post-type 'post' */
		$pt = get_current_screen()->post_type;
		if ( $pt != 'post' ) {
			return;
		}

		if ( current_user_can( 'edit_post', $post_id ) ) {
			$this->post_id           = $post_id;
			$this->tweet_field_value = $tweet_field_value;
			$this->hc_stpe_modify_tweet_metadata();
		} else {
			error_log( "STPE ERROR 002: Tweet Save Rejected: User cannot update post" );
		}
	}

	/**
	 * Used to clean HTML that we want stored in the database.
	 *
	 * @since   1.0.1142018
	 *
	 * @used-by hc_stpe_post_metabox_save()
	 * @uses    global $allowedposttags
	 * @uses    Normalizer::isNormalized()
	 * @uses    Normalizer::normalize()
	 * @uses    wp_kses()
	 *
	 * @param $data
	 *
	 * @return string
	 */
	private static function hc_stpe_of_kses_data( $data ) {
		global $allowedposttags;
		$of_allowedposttags           = $allowedposttags;
		$of_allowedposttags['script'] = array( 'type' => array() );
		if ( ! Normalizer::isNormalized( $data, Normalizer::FORM_C ) ) {
			$data = Normalizer::normalize( $data, Normalizer::FORM_C );
		}

		return wp_kses( $data, $of_allowedposttags );
	}

	/**
	 * Used to remove the auto p that is added by TinyMCE
	 *
	 * @since   1.0.1142018
	 *
	 * @param $content
	 *
	 * @used-by hc_stpe_post_metabox_save()
	 * @uses    str_ireplace()
	 * @return mixed
	 */
	private static function hc_stpe_remove_p_tag( $content ) {
		$content = str_ireplace( '<p>', '', $content );
		$content = str_ireplace( '</p>', '', $content );

		return $content;
	}

	/**
	 * Saves the tweet field to to the post's meta data field
	 *
	 * @since   1.0.1142018
	 *
	 * @param bool $value
	 *
	 * @uses    $this->tweet_field_value if $value is false
	 * @used-by hc_stpe_post_metabox_save()
	 * @uses    $this->post_id
	 * @uses    $this->tweet_field
	 * @uses    update_post_meta()
	 * @return bool|int
	 */
	private function hc_stpe_modify_tweet_metadata( $value = false ) {
		$value = $value ?: $this->tweet_field_value;

		return update_post_meta( $this->post_id, $this->tweet_field, $value );
	}

	/**
	 * Not used but it's good to have.
	 *
	 * @since 1.0.1142018
	 *
	 * @uses  delete_post_meta()
	 * @return bool
	 */
	private function hc_stpe_delete_tweet_metadata() {
		return delete_post_meta( $this->post_id, $this->tweet_field );
	}

	/**
	 * Kicks off the actual tweet to twitter on publish state change.
	 *
	 * @since   1.0.1142018
	 *
	 * @param $new_status
	 * @param $old_status
	 * @param $post
	 *
	 * @used-by add_action( 'transition_post_status' )
	 * @uses    get_the_ID()
	 * @uses    get_post_meta()
	 * @uses    $this->tweet_isSent_field
	 * @uses    hc_stpe_get_tweet_from_post()
	 * @uses    update_post_meta()
	 * @uses    hc_stpe_send_tweet_to_twitter()
	 * @return void
	 */
	public function hc_stpe_on_published( $new_status, $old_status, $post ) {
		error_log( "post has moved to " . $new_status );
		// Only send this to twitter once ever. If you need to edit it after push you need to do it on twitter.
		$id = get_the_ID();

		if ( $new_status === 'publish' && get_post_meta( $id, $this->tweet_isSent_field, true ) === false && $tweet = $this->hc_stpe_get_tweet_from_post( $id )) {
			error_log( "ok, lets send a tweet!" );
				$b = $this->hc_stpe_send_tweet_to_twitter( $tweet, $id ) === 1 ? true : false;
				update_post_meta( $id, $this->tweet_isSent_field, $b );
		}
	}

	/**
	 * Retrieves the tweet from the post metadata
	 *
	 * @since   1.0.1142018
	 *
	 * @param $post_id
	 *
	 * @used-by hc_stpe_on_published()
	 * @uses    get_post_meta()
	 * @uses    $this->tweet_field
	 *
	 * @return mixed
	 */
	public function hc_stpe_get_tweet_from_post( $post_id ) {
		return get_post_meta( $post_id, $this->tweet_field, true );
	}

	/**
	 * Insert the tweet as part of excerpt into the publishing calendar view
	 *
	 * @since 1.0.1142018
	 *
	 * @param $post_id
	 */
	public function hc_stpe_add_tweet_to_edit_flow_calendar( $infoFields, $post_id ) {

		if ( $tweet = $this->hc_stpe_get_tweet_from_post( $post_id ) ) {
			$tweet                 = preg_replace( "/<img[^>]+\>/i", " (image) ", $tweet );
			$tweet                 = preg_replace( "/<a\s(.+?)>(.+?)<\/a>/is", "Link:($2)", $tweet );
			$infoFields['hc_stpe'] = array(
				"label" => "Tweet",
				"value" => preg_replace( "/(<[a-zA-Z\/][^<>]*>|\[([^\]]+)\])/i", "", strip_tags( $tweet ) )
			);
		}

		return $infoFields;
	}

	/**
	 * Uses the WP to Twitter plugin to send out the tweet.
	 *
	 * @since   1.0.1142018
	 *
	 * @param $tweet
	 * @param $post
	 *
	 * @uses    function_exists()
	 * @uses    wpt_post_to_twitter()
	 * @used-by hc_stpe_on_published()
	 * @return int -1 == not sent, 0 == send failure, 1 == sent successful
	 */
	public function hc_stpe_send_tweet_to_twitter( $tweet, $post_id ) {
		$resp = - 1;
		if ( function_exists( 'wpt_post_to_twitter' ) ) {
			$resp = wpt_post_to_twitter( $tweet, false, $post_id );
		}

		return (int) $resp;
	}

	/**
	 * Ajax callback
	 *
	 * @since 1.0.1142018
	 *
	 * @param $tweet
	 * @param $post
	 *
	 * @uses  hc_stpe_send_tweet_to_twitter()
	 * @return int -1 == not sent, 0 == send failure, 1 == sent successful
	 */
	public function hc_stpe_send_retweet_to_twitter( $tweet, $post_id ) {
		return $this->hc_stpe_send_tweet_to_twitter( $tweet, $post_id );
	}

	/**
	 * Ajax Handler
	 *
	 * @since 1.0.1142018
	 *
	 * @param $tweet
	 * @param $post
	 */
	public function hc_stpe_retweet_ajax( $tweet, $post ) {

	}
}
