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
		if ( ! class_exists( 'Normalizer' ) ) {
			require_once( 'normalizer.php' );
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
		update_option( 'app_consumer_key', getenv( 'MLA_STYLE_TWITTER_APP_CONSUMER_KEY' ) );
		update_option( 'app_consumer_secret', getenv( 'MLA_STYLE_TWITTER_APP_CONSUMER_SECRET' ) );
		update_option( 'oauth_token', getenv( 'MLA_STYLE_TWITTER_OAUTH_TOKEN' ) );
		update_option( 'oauth_token_secret', getenv( 'MLA_STYLE_TWITTER_OAUTH_TOKEN_SECRET' ) );


		$this->tweet_field        = "_mla_txt_tweet";
		$this->tweet_isSent_field = "_mla_b_tweet_isSent";
		$this->tweet_btn_send     = "_mla_btn_tweet_allow";
		$this->tweet_image_field  = "_mla_tweet_image";
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
		add_action( 'transition_post_status', array( $this, 'hc_stpe_on_published' ), 999, 3 );
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
			_x( 'MLA Style - Twitter Post', 'Add a Twitter Post for this Article', $this->prefix . 'plugin' ),
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
			$post_id         = get_the_ID();
			$existing        = get_post_meta( $post_id, $this->tweet_field, true );
			$tweet_image     = get_post_meta( $post_id, $this->tweet_image_field, true );
			$is_already_sent = get_post_meta( $post_id, $this->tweet_isSent_field, true );
			$tmp             = $existing ?: "<a href='" . get_the_permalink() . "'>" . get_the_title() . "</a>";
			$url             = get_the_title() ? $tmp : '';

			if ( ! Normalizer::isNormalized( $url, Normalizer::FORM_C ) ) {
				$url = Normalizer::normalize( $url, Normalizer::FORM_C );
			}

			if ( ! empty( $tweet_image ) ) {
				$url .= "<img src='$tweet_image' class='alignnone clearfix' width='100%'/>";
			}

			$tiny_ini          = array(
				'setup'          => $this->hc_stpe_after_tinymce_settings_js(),
				'valid_children' => "+a[img|a]",
				'toolbar'        => false,
				'statusbar'      => false,
			);
			$show_media_button = true;
			if ( ! empty( $is_already_sent ) && $is_already_sent === true ) {
				$tiny_ini['readonly'] = 1;
				$show_media_button    = false;
			}

			//check if the post has been set to published for legacy post$
			if ( ! $existing && ! $is_already_sent && get_post_status( $post_id ) === 'publish' ) {
				update_post_meta( $post_id, $this->tweet_isSent_field, true );
				$is_already_sent = true;
				$url             = "MLA Style Twitter Posting disabled for legacy post.";
			}

			if ( ! $is_already_sent ) {
				wp_editor( $url, $this->tweet_field . '_id', array(
					'wpautop'             => false,
					'media_buttons'       => $show_media_button,
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
					'tinymce'             => $tiny_ini,
					'quicktags'           => false
				) );

				$content = preg_replace( "/(<[a-zA-Z\/][^<>]*>|\[([^\]]+)\])/i", "", $url );
				$count   = 280 - strlen( $content );

				$tweet_btn_send_value = get_post_meta( $post_id, $this->tweet_btn_send, true );
				$checked              = 'off' === $tweet_btn_send_value ? '' : 'checked="checked"';

				?>
                <style>
                    .switch {
                        position: relative;
                        display: inline-block;
                        width: 60px;
                        height: 34px;
                    }

                    .switch input {
                        opacity: 0;
                        width: 0;
                        height: 0;
                    }

                    .slider {
                        position: absolute;
                        cursor: pointer;
                        top: 0;
                        left: 0;
                        right: 0;
                        bottom: 0;
                        background-color: #ccc;
                        -webkit-transition: .4s;
                        transition: .4s;
                    }

                    .slider:before {
                        position: absolute;
                        content: "";
                        height: 26px;
                        width: 26px;
                        left: 4px;
                        bottom: 4px;
                        background-color: white;
                        -webkit-transition: .4s;
                        transition: .4s;
                    }

                    input:checked + .slider {
                        background-color: #0ca125;
                    }

                    input:focus + .slider {
                        box-shadow: 0 0 1px #0CA125;
                    }

                    input:checked + .slider:before {
                        -webkit-transform: translateX(26px);
                        -ms-transform: translateX(26px);
                        transform: translateX(26px);
                    }

                    /* Rounded sliders */
                    .slider.round {
                        border-radius: 34px;
                    }

                    .slider.round:before {
                        border-radius: 50%;
                    }
                </style>

                <div>

                    <div style="padding-bottom: 30px;">
                        <div style='float:right;'>
                            <span id='tweetCnt' style='width: 40px'/><?php echo $count; ?></span> Chars Left
                        </div>
                    </div>

                    <div>
                        <label class="switch pull-right">
                            <input type="checkbox" name="<?php echo $this->tweet_btn_send ?>"
                                   value="on" <?php echo $checked ?> >
                            <span class="slider round"></span>
                        </label>
                    </div>
                    <br style="clear:both"/>
                </div>

			<?php } else {
				echo $url;
			} ?>
        </div>
		<?php // wrapper div close
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

		$is_already_sent   = get_post_meta( $post->ID, $this->tweet_isSent_field, true ) === true;
		$tweet_field_value = ! empty( $_POST[ $this->tweet_field ] ) ? self::hc_stpe_remove_p_tag( self::hc_stpe_of_kses_data( $_POST[ $this->tweet_field ] ) ) : false;

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
			$this->tweet_field_value = strip_tags( $tweet_field_value );
			preg_match_all( '~<img.*?src=["\']+(.*?)["\']+~', $tweet_field_value, $urls );
			$this->tweet_image_value    = ! empty( $urls[1][0] ) ? $urls[1][0] : false;
			$this->tweet_btn_send_value = ! empty( $_POST[ $this->tweet_btn_send ] ) ? 'on' : 'off';
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
	 * @return  void
	 */
	private function hc_stpe_modify_tweet_metadata( $value = false ) {
		$value = $value ?: $this->tweet_field_value;

		update_post_meta( $this->post_id, $this->tweet_field, $value );
		if ( $this->tweet_image_value ) {
			update_post_meta( $this->post_id, $this->tweet_image_field, $this->tweet_image_value );
		}

		update_post_meta( $this->post_id, $this->tweet_btn_send, $this->tweet_btn_send_value );
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
		//error_log( $post->ID . " moved to status: " . $new_status );
		// Only send this to twitter once ever. If you need to edit it after push you need to do it on twitter.
		$id      = $post->ID;
		$tweet   = $this->hc_stpe_get_tweet_from_post( $id );
		$enabled = get_post_meta( $id, $this->tweet_btn_send, true );
		if ( ! $tweet || $enabled === 'off' ) {
			error_log( "\n\nExiting: post " . $id . " status: " . $new_status );
			error_log( "Tweet: " . $tweet );
			error_log( "Enabled: " . $enabled );

			return;
		}

		if ( $new_status === 'inherit' ) {
			$parent     = get_post( $post->post_parent );
			$new_status = $parent->post_status;
		}

//		$tweet_image = get_post_meta( $id, $this->tweet_image_field, true );
//		error_log("testing: image url: ".$tweet_image);
//		$this->hc_stpe_get_attachment_id($tweet_image);

		$is_sent = get_post_meta( $id, $this->tweet_isSent_field, true );

		if ( ( $new_status === 'publish' ) && empty( $is_sent ) ) {
			$tweet_image = get_post_meta( $id, $this->tweet_image_field, true );
			$b           = $this->hc_stpe_send_tweet_to_twitter( $tweet, $id, $tweet_image ) === 1 ? true : false;
			//$b           = 1; //pseudo response for testing
			error_log( "WP response = " . $b );
			update_post_meta( $id, $this->tweet_isSent_field, $b );
		} else {
			error_log( "\n\nNo Status. Exiting: post " . $id . " status: " . $new_status );
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
	 * @since   1.0.1222018
	 *
	 * @param               $tweet
	 * @param               $post_id
	 * @param string | bool $image
	 *
	 * @uses    function_exists()
	 * @uses    wpt_post_to_twitter()
	 * @used-by hc_stpe_on_published()
	 * @return int -1 == not sent, 0 == send failure, 1 == sent successful
	 */
	public function hc_stpe_send_tweet_to_twitter( $tweet, $post_id, $image = false ) {
		$resp = $this->hc_stpe_post_to_twitter( $tweet, false, $post_id, $image ) ?: - 1;

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

	/**
	 * @param $url
	 *
	 * @link https://wordpress.stackexchange.com/questions/6645/turn-a-url-into-an-attachment-post-id
	 * @return bool
	 */
	function hc_stpe_get_attachment_id( $url ) {

		$dir = wp_upload_dir();

		// baseurl never has a trailing slash
		if ( false === strpos( $url, $dir['baseurl'] . '/' ) ) {
			// URL points to a place outside of upload directory
			return false;
		}
		$urls_file_components = explode("/",$url);
		$file_array = explode("-",$urls_file_components[6]);
		$file  = $urls_file_components[4]."/".$urls_file_components[5]."/".$file_array[0];
		$query = array(
			'post_type'  => 'attachment',
			'fields'     => 'ids',
			'meta_query' => array(
				array(
					'key'     => '_wp_attached_file',
					'value'   => $file,
					'compare' => 'LIKE',
				),
			)
		);

		// query attachments
		$ids = get_posts( $query );
        error_log(print_r($ids, true));
		if ( ! empty( $ids ) ) {

			foreach ( $ids as $id ) {
					return $id;
			}
		}

		return false;
	}


	/**
	 * Forked version of WP to Twitter's wpt_post_to_twitter() to include the use of a media url
	 *
	 * @since   1.0.1222018
	 *
	 * @param   [type] $twit
	 * @param boolean       $auth
	 * @param boolean       $id
	 * @param string | bool $media url of media to include or true to include the default attachment from post
	 *
	 * @return mixed
	 * @uses
	 */
	function hc_stpe_post_to_twitter( $twit, $auth = false, $id = false, $media = false ) {
		if ( ! function_exists( 'wpt_check_oauth' ) ) {
			$error = __( 'WP To Twitter plugin not installed.', 'wp-to-twitter' );
			wpt_saves_error( $id, $auth, $twit, $error, '401', time() );
			wpt_set_log( 'wpt_status_message', $id, $error );
			error_log( $error );

			return false;
		}

		$recent = wpt_check_recent_tweet( $id, $auth );
		$error  = false;
		if ( 1 == get_option( 'wpt_rate_limiting' ) ) {
			// check whether this post needs to be rate limited.
			$continue = wpt_test_rate_limit( $id, $auth );
			if ( ! $continue ) {
				return false;
			}
		}

		$http_code = 0;
		if ( $recent ) {
			return false;
		}

		if ( ! wpt_check_oauth( $auth ) ) {
			$error = __( 'This account is not authorized to post to Twitter.', 'wp-to-twitter' );
			wpt_saves_error( $id, $auth, $twit, $error, '401', time() );
			wpt_set_log( 'wpt_status_message', $id, $error );
			error_log( $error );

			return false;
		} // exit silently if not authorized.

		$check = ( ! $auth ) ? get_option( 'jd_last_tweet' ) : get_user_meta( $auth, 'wpt_last_tweet', true ); // get user's last tweet.
		// prevent duplicate Tweets.
		if ( $check == $twit ) {
			wpt_mail( 'Matched: tweet identical', "This Tweet: $twit; Check Tweet: $check; $auth, $id, $media" ); // DEBUG.
			$error = __( 'This tweet is identical to another Tweet recently sent to this account.', 'wp-to-twitter' ) . ' ' . __( 'Twitter requires all Tweets to be unique.', 'wp-to-twitter' );
			wpt_saves_error( $id, $auth, $twit, $error, '403-1', time() );
			wpt_set_log( 'wpt_status_message', $id, $error );
			error_log( $error );

			return false;
		} elseif ( '' == $twit || ! $twit ) {
			wpt_mail( 'Tweet check: empty sentence', "$twit, $auth, $id, $media" ); // DEBUG.
			$error = __( 'This tweet was blank and could not be sent to Twitter.', 'wp-tweets-pro' );
			wpt_saves_error( $id, $auth, $twit, $error, '403-2', time() );
			wpt_set_log( 'wpt_status_message', $id, $error );
			error_log( $error );

			return false;
		} else {
			$media_id = false;
			// must be designated as media and have a valid attachment.
			switch ( $media ) {
				case ! empty( $media ) && is_string( $media ):
					$attachment = $this->hc_stpe_get_attachment_id( $media );
					error_log( "file attachment id: ". $attachment);
					break;
				case ! empty( $media ) && is_bool( $media ):
					$attachment = wpt_post_attachment( $id );
					break;
				case false:
				default:
					$attachment = false;
					break;
			}
			if ( $attachment ) {
				wpt_mail( 'Post has upload', "$auth, $attachment" );
				$meta = wp_get_attachment_metadata( $attachment );
				if ( ! isset( $meta['width'], $meta['height'] ) ) {
				    error_log("Image Data Does not Exist for #$attachment");
					error_log(print_r( $meta, true) );
					$attachment = false;
				}
			}
			$api        = 'https://api.twitter.com/1.1/statuses/update.json';
			$upload_api = 'https://upload.twitter.com/1.1/media/upload.json';
			$status     = array(
				'status'           => $twit,
				'source'           => 'mla-style',
				'include_entities' => 'true',
			);

			if ( wtt_oauth_test( $auth ) ) {
				$connection = wpt_oauth_connection( $auth );
				if ( $connection ) {
					if ( $media && $attachment && ! $media_id ) {
						$media_id = $connection->media( $upload_api, array(
							'auth'  => $auth,
							'media' => $attachment,
						) );
                        error_log( 'Media Uploaded');
						error_log(print_r( $auth, true) );
						error_log(print_r( $media_id, true) );
						error_log(print_r( $attachment, true) );
						wpt_mail( 'Media Uploaded', "$auth, $media_id, $attachment" );
						if ( $media_id ) {
							$status['media_ids'] = $media_id;
							/**
							 * Eventually, use this to add alt text. Not supported at this time.
							 * $metadata_api = 'https://upload.twitter.com/1.1/media/metadata/create.json';
							 * $alt_text     = get_post_meta( $args['media'], '_wp_attachment_image_alt', true );
							 * if ( '' != $alt_text ) {
							 * $image_alt = json_encode( array(
							 * 'media_id' => $media_id,
							 * 'alt_text' => array(
							 * 'text' => $alt_text,
							 * ),
							 * ) );
							 * $post_alt = $connection->post( $metadata_api, array( 'auth' => $auth, 'json' => $image_alt ), true );
							 * }
							 */
						}
					}
				}
			}
			if ( empty( $connection ) ) {
				$connection = array( 'connection' => 'undefined' );
			} else {
				$staging_mode = apply_filters( 'wpt_staging_mode', false, $auth, $id );
				if ( ( defined( 'WPT_STAGING_MODE' ) && true == WPT_STAGING_MODE ) || $staging_mode ) {
					// if in staging mode, we'll behave as if the Tweet succeeded, but not send it.
					$connection = true;
					$http_code  = 200;
					$notice     = __( 'In Staging Mode:', 'wp-to-twitter' ) . ' ';
				} else {
					$connection->post( $api, $status );
					$http_code = ( $connection ) ? $connection->http_code : 'failed';
					$notice    = '';
				}
			}
			wpt_mail( 'Twitter Connection', print_r( $connection, 1 ) . " - $twit, $auth, $id, $media" );
			if ( $connection ) {
				if ( isset( $connection->http_header['x-access-level'] ) && 'read' == $connection->http_header['x-access-level'] ) {
					// Translators: Twitter App editing URL.
					$supplement = sprintf( __( 'Your Twitter application does not have read and write permissions. Go to <a href="%s">your Twitter apps</a> to modify these settings.', 'wp-to-twitter' ), 'https://dev.twitter.com/apps/' );
				} else {
					$supplement = '';
				}
				$return = false;
				switch ( $http_code ) {
					case '100':
						$error = __( '100 Continue: Twitter received the header of your submission, but your server did not follow through by sending the body of the data.', 'wp-to-twitter' );
						break;
					case '200':
						$return = true;
						$error  = __( '200 OK: Success!', 'wp-to-twitter' );
						update_option( 'wpt_authentication_missing', false );
						break;
					case '304':
						$error = __( '304 Not Modified: There was no new data to return', 'wp-to-twitter' );
						break;
					case '400':
						$error = __( '400 Bad Request: The request was invalid. This is the status code returned during rate limiting.', 'wp-to-twitter' );
						break;
					case '401':
						$error = __( '401 Unauthorized: Authentication credentials were missing or incorrect.', 'wp-to-twitter' );
						update_option( 'wpt_authentication_missing', "$auth" );
						break;
					case '403':
						$error = __( '403 Forbidden: The request is understood, but has been refused by Twitter.', 'wp-to-twitter' );
						break;
					case '404':
						$error = __( '404 Not Found: The URI requested is invalid or the resource requested does not exist.', 'wp-to-twitter' );
						break;
					case '406':
						$error = __( '406 Not Acceptable: Invalid Format Specified.', 'wp-to-twitter' );
						break;
					case '422':
						$error = __( '422 Unprocessable Entity: The image uploaded could not be processed.', 'wp-to-twitter' );
						break;
					case '429':
						$error = __( '429 Too Many Requests: You have exceeded your rate limits.', 'wp-to-twitter' );
						break;
					case '500':
						$error = __( '500 Internal Server Error: Something is broken at Twitter.', 'wp-to-twitter' );
						break;
					case '502':
						$error = __( '502 Bad Gateway: Twitter is down or being upgraded.', 'wp-to-twitter' );
						break;
					case '503':
						$error = __( '503 Service Unavailable: The Twitter servers are up, but overloaded with requests - Please try again later.', 'wp-to-twitter' );
						break;
					case '504':
						$error = __( "504 Gateway Timeout: The Twitter servers are up, but the request couldn't be serviced due to some failure within our stack. Try again later.", 'wp-to-twitter' );
						break;
					default:
						// Translators: http code.
						$error = sprintf( __( '<strong>Code %s</strong>: Twitter did not return a recognized response code.', 'wp-to-twitter' ), $http_code );
						break;
				}
				$body             = $connection->body;
				$error_code       = ( 200 != $http_code ) ? $body->errors[0]->code : '';
				$error_message    = ( 200 != $http_code ) ? $body->errors[0]->message : '';
				$error_supplement = ( '' != $error_code ) ? ' (Error Code: ' . $error_code . ': ' . $error_message . ')' : '';
				$error            .= ( '' != $supplement ) ? " $supplement" : '';
				$error            .= $error_supplement;
				error_log( $error );
				wpt_mail( "Twitter Response: $http_code", "$error" ); // DEBUG.
				// only save last Tweet if successful.
				if ( 200 == $http_code ) {
					if ( ! $auth ) {
						update_option( 'jd_last_tweet', $twit );
					} else {
						update_user_meta( $auth, 'wpt_last_tweet', $twit );
					}
				}
				error_log( $error );
				wpt_saves_error( $id, $auth, $twit, $error, $http_code, time() );
				if ( 200 == $http_code ) {
					$jwt = get_post_meta( $id, '_jd_wp_twitter', true );
					if ( ! is_array( $jwt ) ) {
						$jwt = array();
					}
					$jwt[] = urldecode( $twit );
					if ( empty( $_POST ) ) {
						$_POST = array();
					}
					$_POST['_jd_wp_twitter'] = $jwt;
					update_post_meta( $id, '_jd_wp_twitter', $jwt );
					if ( ! function_exists( 'wpt_pro_exists' ) ) {
						// schedule a one-time promotional box for 4 weeks after first successful Tweet.
						if ( false == get_option( 'wpt_promotion_scheduled' ) ) {
							wp_schedule_single_event( time() + ( 60 * 60 * 24 * 7 * 4 ), 'wpt_schedule_promotion_action' );
							update_option( 'wpt_promotion_scheduled', 1 );
						}
					}
				}
				if ( ! $return ) {
					wpt_set_log( 'wpt_status_message', $id, $error );
					error_log( $error );
				} else {
					do_action( 'wpt_tweet_posted', $connection, $id );
					wpt_set_log( 'wpt_status_message', $id, $notice . __( 'Tweet sent successfully.', 'wp-to-twitter' ) );
				}

				return $return;
			} else {
				wpt_set_log( 'wpt_status_message', $id, __( 'No Twitter OAuth connection found.', 'wp-to-twitter' ) );

				return false;
			}
		}
	}
}
