<?php
/**
 *  Plugin to add guest authors to a post.
 *
 * @package STYLE_AUTHOR_BIOS TEMPLATE
 * @version 1.0.1212018
 */

class STYLE_AUTHOR_BIOS {

	// ###################### Magic ########################

	private $plugin_name = 'author_bios';

	// Hold the class instance.
	private static $instance = null;

	// Hold the magic fields
	private $data = array();

	function __construct() {
		$this->setup_variables();
		$this->setup_post();
		$this->setup_taxonomies();
		$this->actions_and_filters();
	}

	/**
	 * @return bool|STYLE_AUTHOR_BIOS|null
	 */
	public static function getInstance() {
		/** This is only for the MLA Style Center blog so we make sure we are on the correct blog site before continuing. */
		if ( ! self::is_site() ) {
			return false;
		}

		if ( self::$instance == null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @param $name
	 * @param $value
	 */
	public function __set( $name, $value ) {
		$name                = "mla_sab_" . $name;
		$this->data[ $name ] = $value;
	}

	/**
	 * @param $name
	 */
	public function __unset( $name ) {
		$name = "mla_sab_" . $name;
		unset( $this->data[ $name ] );
	}

	/**
	 * @param $name
	 *
	 * @return mixed|null
	 */
	public function __get( $name ) {
		$name = "mla_sab_" . $name;
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

	/**
	 * @param $param
	 *
	 * @return mixed|null
	 */
	public function get( $param ) {
		return $this->$param;
	}

	// ###################### Plugin starts here ########################

	/**
	 * Variables used through out the class are stored in magic vars and accessible using the $this->fieldname within
	 * the class.
	 *
	 * @since   1.0.1212018
	 *
	 * @used-by __construct()
	 * @return void
	 */
	public function setup_variables() {

	}

	/**
	 * Post types used through out the class
	 * the class.
	 *
	 * @since   1.0.1212018
	 *
	 * @used-by __construct()
	 * @return void
	 */
	public function setup_post() {

	}

	/**
	 * Taxonomies used through out the class
	 * the class.
	 *
	 * @since   1.0.1212018
	 *
	 * @used-by __construct()
	 * @return void
	 */
	public function setup_taxonomies() {
		$this->create_taxonomy('Author', array('post'), false );
	}

	/**
	 * Load WP filters and actions
	 *
	 * @since   1.0.1212018
	 *
	 * @used-by __construct()
	 */
	public function actions_and_filters() {

	}

	/**
	 * Private function to determine if the current site is MLA Style site.
	 *
	 * @since   1.0.1212018
	 *
	 * @used-by getInstance()
	 * @return bool
	 */
	private static function is_site( $sitename = "The MLA Style Center" ) {
		$blog = get_blog_details( get_current_blog_id() );

		return $blog->blogname == $sitename ? true : false;
	}

	/**
	 * Adds the meta box(es) to the admin page.
	 *
	 * @since   1.0.1212018
	 *
	 * @used-by add_action( 'add_meta_boxes' )
	 * @uses    get_current_screen()
	 * @uses    add_meta_box()
	 * @uses    $this->prefix
	 */
	public function post_metabox_add() {
		$screen = get_current_screen();
		if ( $screen->post_type != 'post' ) {
			return;
		}
		add_meta_box(
			'internal_name',
			_x( 'Display Label', 'This is a description', $this->plugin_name ),
			array( $this, ' [[ callback function in this class ]] ' ),
			$screen->id,
			'side', //can be main, side, or advanced
			'core'
		);
	}

	/**
	 * Saves the meta field
	 *
	 * @used-by add_action( 'save_post' )
	 *
	 * @param $post_id
	 * @param $post
	 * @param $update
	 *
	 * @since   1.0.1212018
	 *
	 */

	public function post_metabox_save( $post_id, $post, $update ) {

	}


	/**
	 * @param $post_type_name
	 * @param $args
	 */
	public function create_post_type( $post_type_name, $args ) {

		if ( empty( $args['labels'] ) ) {
			$args['labels'] = array(
				'name'          => _x( $post_type_name, "", 'learningspace' ),
				'singular_name' => _x( $post_type_name, "", 'learningspace' ),
			);
		}

		//required for gutenberg
		//$args['show_in_rest'] = true;


		// Registering your Custom Post Type
		register_post_type( $post_type_name, $args );
	}

	/**
	 * @param       $tax_name
	 * @param array $post_types
	 * @param bool  $is_hierarchical
	 * @param bool  $labels
	 * @param bool  $show_ui
	 */
	public function create_taxonomy( $tax_name, $post_types = array( "post" ), $is_hierarchical = true, $labels = false, $show_ui = true ) {
		if ( ! $labels ) {
			$labels = array(
				'name'              => _x( $tax_name, $tax_name ),
				'singular_name'     => _x( $tax_name, $tax_name ),
				'search_items'      => __( 'Search ' . $tax_name ),
				'all_items'         => __( 'All ' . $tax_name ),
				'parent_item'       => null,
				'parent_item_colon' => null,
				'edit_item'         => __( 'Edit ' . $tax_name ),
				'update_item'       => __( 'Update ' . $tax_name ),
				'add_new_item'      => __( 'Add New ' . $tax_name ),
				'new_item_name'     => __( 'New ' . $tax_name . ' Name' ),
				'menu_name'         => __( $tax_name ),
			);
		}
		if ( $is_hierarchical && empty( $labels['parent_item'] ) ) {
			$labels['parent_item']       = __( 'Parent Topic' );
			$labels['parent_item_colon'] = __( 'Parent Topic:' );
		}
		register_taxonomy( strtolower( str_replace( " ", "_", $tax_name ) ), $post_types, array(
			'hierarchical'      => $is_hierarchical,
			'labels'            => $labels,
			'show_ui'           => $show_ui,
			'show_in_menu'      => false,
			'show_in_nav_menu'  => false,
			'show_in_admin_bar' => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => $tax_name ),
		) );
	}
}
