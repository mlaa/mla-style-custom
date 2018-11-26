<?php
/**
 * In post tweet editor for edit flow. Allows a user to create a post and add the body and image for that promotional tweet
 * inside that post edit page. The tweet will be published when the post id published.
 * Likewise the tweet body should be added to the post's edit flow calender hover pagelet.
 * User: jbetancourt
 * Date: 11/20/18
 * Time: 3:00 PM
 */
error_log("loaded file");
class STYLE_TWITTER_POST_EDITOR {

	// ###################### Magic ########################

	// Used in get / set magic
	private $prefix = "hc_stpe_";

	// Hold the class instance.
	private static $instance = null;

	// Hold the magic fields
	private $data = array();

	function __construct() {
		$this->hc_stpe_setup_variables();
		$this->hc_stpe_actions_and_filters();
	}

	public static function getInstance() {
		if ( self::$instance == null ) {
			self::$instance = new STYLE_TWITTER_POST_EDITOR();
		}

		return self::$instance;
	}

	public function __set( $name, $value ) {
		$name                = $this->prefix . $name;
		$this->data[ $name ] = $value;
	}

	public function __unset( $name ) {
		$name = $this->prefix . $name;
		unset( $this->data[ $name ] );
	}

	public function __get( $name ) {
		$name = $this->prefix . $name;
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

	public function hc_stpe_setup_variables() {
		// prefix is magically applied to all entries below.
		$this->tweet_field        = "txt_tweet";
		$this->tweet_isSent_field = "b_tweet_isSent";
	}

	public function hc_stpe_actions_and_filters() {
		// todo: only on style site, only if can edit
		//
		error_log( 'hc_stpe_actions_and_filters' );
		add_action( 'add_meta_boxes', array( $this, 'hc_stpe_post_metabox_add' ) );
		add_action( 'save_post', array( $this, 'hc_stpe_post_metabox_save' ) );
		add_action( 'transition_post_status', array( $this, 'hc_stpe_on_published' ), 10, 3 );
	}

	public function hc_stpe_post_metabox_add() {

		error_log( 'hc_stpe_post_metabox_add' );
		add_meta_box(
			$this->prefix . 'tweet_manage',
			_x( 'Twitter Post', 'Add a Twitter Post for this Article', $this->prefix . 'plugin' ),
			array( $this, 'hc_stpe_twitter_editor' ),
			get_current_screen()->id,
			'advanced',
			'core'
		);
	}

	public function hc_stpe_twitter_editor() {
		wp_editor( "", $this->tweet_field, array(
			'wpautop'             => false,
			'media_buttons'       => false,
			'default_editor'      => 'quicktags',
			'drag_drop_upload'    => false,
			'textarea_name'       => $this->tweet_field,
			'textarea_rows'       => 20,
			'tabindex'            => '',
			'tabfocus_elements'   => ':prev,:next',
			'editor_css'          => '',
			'editor_class'        => '',
			'teeny'               => true,
			'dfw'                 => false,
			'_content_editor_dfw' => false,
			'tinymce'             => false,
			'quicktags'           => true
		) );
	}

	public function hc_stpe_post_editor_js_css() {

	}

	public function hc_stpe_post_metabox_save( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( current_user_can( 'edit_post' ) ) {
			$this->post_id           = $post_id;
			$this->tweet_field_value = ! empty( $_POST[ $this->tweet_field ] ) ? sanitize_text_field( $_POST[ $this->tweet_field ] ) : null;
			$this->hc_stpe_modify_tweet_metadata();
		} else {
			error_log( "STPE ERROR 001: Tweet Save Rejected: User cannot update post" );
		}

		return $post_id;
	}

	private function hc_stpe_modify_tweet_metadata( $value = false ) {
		$value = $value ?: $this->tweet_field_value;
		return update_post_meta( $this->post_id, $this->tweet_field, $value );
	}

	private function hc_stpe_delete_tweet_metadata() {
		return delete_post_meta( $this->post_id, $this->tweet_field );
	}

	public function hc_stpe_on_published( $new_status, $old_status, $post ) {
		// Only send this to twitter once ever. If you need to edit it after push you need to do it on twitter.
		if ( $new_status === 'publish' && get_post_meta( $post, $this->tweet_isSent_field, true ) == false ) {
			if($tweet = $this->hc_stpe_get_tweet_from_post($post->ID)) {
				update_post_meta( $post, $this->tweet_isSent_field, true );
				$this->hc_stpe_send_tweet_to_twitter( $tweet, $post->ID );
			}
		}
	}

	public function hc_stpe_add_tweet_to_edit_flow_calendar( $post_id ) {

	}

	public function hc_stpe_get_tweet_from_post(  $post_id ) {
		return get_post_meta( $post_id, $this->tweet_field, true );
	}

	public function hc_stpe_send_tweet_to_twitter( $tweet, $post ) {

	}
}

STYLE_TWITTER_POST_EDITOR::getInstance();