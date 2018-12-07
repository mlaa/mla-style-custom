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
		$this->create_taxonomy( 'Author', array( 'post' ), false );
		$authors = array(
			'Nora Carr'                   => 'is an instructor at Queens College, City University of New York (CUNY). She is a PhD candidate in comparative literature at the CUNY Graduate Center.',
			'Nancy Foasberg'              => 'is the humanities librarian at Queens College, City University of New York.',
			'Angela Gibson'               => 'is the director of scholarly communication at the MLA. She has a decade and a half of editorial experience and holds a PhD in Middle English from the University of Rochester. Before coming to the MLA, she taught college courses in writing and literature.',
			'Eric Wirth'                  => 'was the head of editorial services at the MLA for twenty-seven years, until his retirement in 2016, where he prepared scholarly writing for publication. Previously, he produced reference books at other publishers, after studying French literature in college.',
			'Livia Arndal Woods'          => 'is a teaching fellow at Queens College, City University of New York, where she teaches composition and British literature. She is working on a book project on reading practices and pregnancy in nineteenth-century realist fiction as well as a series of articles on digital pedagogy.',
			'Michael Kandel'              => 'has been editing at the MLA for twenty-one years. He also translated several Polish writers, among them Stanisław Lem, Andrzej Stasiuk, Marek Huberath, and Paweł Huelle, and edited, for Harcourt Brace, several American writers, among them Jonathan Lethem, Ursula K. Le Guin, James Morrow, and Patricia Anthony.',
			'Barney Latimer'              => 'as senior editor of MLA publications, has copyedited PMLA articles for more than ten years. He holds an MA in English from New York University. He has taught high school and college classes in writing and literary analysis, as well as seminars in poetry writing at several nonprofit organizations that serve New Yorkers with mental illness.',
			'Jennifer Rappaport'          => 'is managing editor, MLA style resources, at the Modern Language Association. She received a BA in English and French from Vassar College and an MA in comparative literature from New York University, where she taught expository writing. Before coming to the MLA, she worked as an editor at a university press and as a freelance copyeditor and translator for commercial and academic publishers.',
			'Russell Grooms'              => 'is the reference and instruction librarian at Northern Virginia Community College, Woodbridge.',
			'Erika Suffern'               => 'is the head of book publications at the MLA. She received a BA from Bard College and an MA from the University of Delaware and has ten years of editorial experience. Before joining the MLA staff in 2016, she was associate director of the Renaissance Society of America and managing editor of its journal, Renaissance Quarterly.',
			'Joan M. Hoffman'             => 'is professor of Spanish at Western Washington University.',
			'Modern Language Association' => 'Written by members of the MLA staff',
			'Ellen Carillo'               => 'is associate professor at the University of Connecticut and the author of the MLA Guide to Digital Literacy, forthcoming in 2019.',
			'Alice Yang'                  => 'is a student at Northwestern University and is spending her third year at Hertford College, University of Oxford. She is studying English literature at both institutions and planning to earn a master of fine arts in creative writing.',
			'Joseph Wallace'              => 'copyedits articles for PMLA. He received a PhD from the University of North Carolina, Chapel Hill. Before coming to the Modern Language Association as an assistant editor, he edited articles for Studies in Philology and taught courses on writing and on early modern literature.',
			'Elizabeth Brookbank'         => 'Elizabeth Brookbank is associate professor and instruction librarian at Western Oregon University. H. Faye Christenberry is comparative literature and philosophy librarian at the University of Washington. They are the authors of the MLA Guide to Undergraduate Research in Literature, forthcoming in 2019.',
			'H. Faye Christenberry'       => 'H. Faye Christenberry is comparative literature and philosophy librarian at the University of Washington. They are the authors of the MLA Guide to Undergraduate Research in Literature, forthcoming in 2019.',
			'Caitlin Duffy'               => ' is a former New York City secondary school teacher and a current PhD student in English literature at Stony Brook University, State University of New York.',
		);
		foreach ( $authors as $author => $description ) {
			if ( ! term_exists( $author, 'author' ) ) {
				wp_insert_term(
					$author, // the term
					'author', // the taxonomy
					array(
						'description' => $description,
					)
				);
			}
		}
	}

	/**
	 * Load WP filters and actions
	 *
	 * @since   1.0.1212018
	 *
	 * @used-by __construct()
	 */
	public function actions_and_filters() {
		add_action('init', array($this, 'setup_taxonomies'));
		add_action('mla_style_theme_author_bios', array($this, 'render_html'));
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
	 * @param $
	 */
	public function render_html( $post_id ) {
		$html = array();
		$authors = wp_get_post_terms( $post_id, 'author' );
		foreach ($authors as $description) {
			$html[] = $this->render_author_description_html($description);
		}
		return implode("", $html);
	}

	/**
	 * @param $description
	 *
	 * @return string
	 */
	private function render_author_description_html( $description ) {
		$html = <<<HTMLCONTENT
		<div class="author_container">
			<div class="instructor-intro tile instructor-tile instructor-tile-small">
				<div class="tile-link tile-body">
					<div class="author-photo author-photo-guest"></div>
					<p>$description</p>
				</div>
			</div>
		</div> <!-- /.author_template -->
HTMLCONTENT;

		return $html;
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
