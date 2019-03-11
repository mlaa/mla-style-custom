<?php
/**
 *  Plugin to add guest authors to a post.
 *
 * Tax image code sourced from https://catapultthemes.com/adding-an-image-upload-field-to-categories/
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
		$this->data[ $name ] = $value;
	}

	/**
	 * @param $name
	 */
	public function __unset( $name ) {
		unset( $this->data[ $name ] );
	}

	/**
	 * @param $name
	 *
	 * @return mixed|null
	 */
	public function __get( $name ) {
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
		$this->create_taxonomy( 'MLA Author', array( 'post' ), false );
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
			'Bradley Smith'               => ' is an associate professor of English and the director of the First-Year Writing Program at Governors State University. His research interests include first-year writing pedagogy and writing program administration. He has published articles in College Composition and Communication and the Journal for the Assembly for Expanded Perspectives on Learning and has a chapter, written with Kerri K. Morris, in WPAs in Transition (Utah State UP, 2018).',
			'Mike Burke'                  => ', associate professor of English at St. Louis Community College, Meramec, has taught English at community colleges part-time since 2002 and full-time since 2007. He has also taught at the United States Military Academy and Southern Illinois University, Edwardsville. He is on the executive committee of the MLA forum on community colleges.',
		);
		foreach ( $authors as $author => $description ) {
			if ( ! term_exists( $author, 'mla_author' ) ) {
				if($author == 'Modern Language Association') {
					$slug = 'mla';
				} else {
					$slug = explode(" ",$author);
					$slug = $slug[0][0].array_pop( $slug);
				}
				wp_insert_term(
					$author, // the term
					'mla_author', // the taxonomy
					array(
						'description' => $description,
						'slug' => $slug
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
		add_action( 'init', array( $this, 'setup_taxonomies' ), 0 );
		add_action( 'wp_enqueue_scripts', array( $this, 'set_author_image_css' ), 9999 );
		add_action( 'the_content', array( $this, 'render_html' ) );
		add_action( 'mla_author_add_form_fields', array( $this, 'add_category_image' ), 10, 2 );
		add_action( 'created_mla_author', array( $this, 'save_category_image' ), 10, 2 );
		add_action( 'mla_author_edit_form_fields', array( $this, 'update_category_image' ), 10, 2 );
		add_action( 'edited_mla_author', array( $this, 'updated_category_image' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_media' ) );
		add_action( 'admin_footer', array( $this, 'add_script' ) );
		//add_filter( 'template_include', array( $this, 'force_listing_templates' ) );
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
	 * @param $post_id
	 * @param $post
	 * @param $update
	 *
	 * @return bool
	 */
	public function set_defaults_on_admin_save ($post_id, $post, $update ) {
		$post_type = get_post_type();
		if("post" != $post_type || !$this->is_site() || in_category( 'behind-the-style' )) {
			return false;
		}

		$default_taxonomy = get_term_by( 'slug', 'modern-language-association', 'mla_author' );

	}

	/**
	 * @param $
	 */
	public function render_html( $content ) {
		//Only add the authors to the Behind the Style post
		if ( ! in_category( 'behind-the-style' ) && ! in_category( 'teaching-resources' ) ) {
			return $content;
		}

		$html    = array();
		$post_id = get_the_ID();
		$authors = wp_get_post_terms( $post_id, 'mla_author' ) ?: $this->update_legacy_post_author_value( $post_id );
		if ( $authors ) {
			foreach ( $authors as $author ) {
				$html[] = $this->render_author_description_html( $author );
			}
		} else {
			$html[] = $this->render_author_description_html( 'mla' );
		}

		return $content . implode( "", $html );

	}

	/**
	 * @param $post_id
	 *
	 * @return array|bool|WP_Error
	 */
	public function update_legacy_post_author_value( $post_id ) {
		if ( $author_meta = get_post_meta( $post_id, 'post_author', true ) ) {
			$legacy_author_meta_value_map = array(
				'carr'                    => 'Nora Carr',
				'foasberg'                => 'Nancy Foasberg',
				'gibson'                  => 'Angela Gibson',
				'wirth'                   => 'Eric Wirth',
				'woods'                   => 'Livia Arndal Woods',
				'kandel'                  => 'Michael Kandel',
				'latimer'                 => 'Barney Latimer',
				'rappaport'               => 'Jennifer Rappaport',
				'grooms'                  => 'Russell Grooms',
				'suffern'                 => 'Erika Suffern',
				'hoffman'                 => 'Joan M. Hoffman',
				'mla'                     => 'Modern Language Association',
				'carillo'                 => 'Ellen Carillo',
				'yang'                    => 'Alice Yang',
				'wallace'                 => 'Joseph Wallace',
				'brookbank-christenberry' => array( 'Elizabeth Brookbank', 'H. Faye Christenberry' ),
				'duffy'                   => 'Caitlin Duffy',
				'smith'                   => 'Bradley Smith',
				'burke'                   => 'Mike Burke',

			);
			foreach ( explode( ",", $author_meta ) as $author ) {
				wp_set_post_terms( $post_id, $legacy_author_meta_value_map[ $author ], 'mla_author', true );
			}

			return wp_get_post_terms( $post_id, 'mla_author' );
		} else {
			return false;
		};

	}

	/**
	 *
	 */
	public function set_author_image_css() {
		if ( $authors = get_the_terms( get_the_ID(), 'mla_author' ) ) {
			$css = array();
			foreach ( $authors as $author ) {
				$name =  strtolower($author->name);
				$arr = explode( " ", $name);
				$image      = ".author-photo-" . array_pop($arr ) ;
				$image_path = $this->get_author_image_path( $author->term_id );
				if ( $image_path ) {
					$css[] = $image_path ? "$image::before {background-image: url('" . $image_path . "') !important;}" : "";
				}
			}
			wp_add_inline_style( 'mla-style-center-main', implode( "", $css ) );
		}
	}

	/**
	 * @param $term_id
	 *
	 * @return string
	 */
	private function get_author_image_path( $term_id ) {
		$image_id = get_term_meta( $term_id, 'author-taxonomy-image-id', true );

		return ! empty( $image_id ) ? wp_get_attachment_image_src( $image_id )[0] : "";
	}

	/**
	 * @param $template
	 *
	 * @return string
	 */
	public function force_listing_templates( $template ) {
		if ( is_tax( 'mla_author' ) ) {
			$template = WP_PLUGIN_DIR . '/' . plugin_basename( dirname( dirname( __FILE__ ) ) ) . '/templates/archive-author.php';
		}

		return $template;
	}

	/**
	 * @param string $tax
	 *
	 * @return bool|string
	 */
	private function render_author_description_html( $tax = 'mla' ) {
		$taxonomy = $tax;
		if ( ! is_object( $tax ) && $tax === 'mla' ) {
			$taxonomy = get_term_by( 'slug', 'modern-language-association', 'mla_author' );
		}

		$description = $taxonomy->description;
		$author      = $taxonomy->name;
		$image_path  = $this->get_author_image_path( $taxonomy->term_id );
		$image       = ! empty( $image_path ) ? "author-photo-" . array_pop( explode( " ", strtolower( $author ) ) ) : 'author-photo-guest';

		if ( strpos( $description, $author ) === false ) {
			$description = '<span style="font-weight: bold">' . $author . '</span> ' . $description;
		}

		$html = <<<HTMLCONTENT
		<div class="author_container">
			<div class="instructor-intro tile instructor-tile instructor-tile-small">
				<div class="tile-link tile-body">

					<div class="author-photo $image"></div>
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
	public
	function create_taxonomy(
		$tax_name, $post_types = array( "post" ), $is_hierarchical = true, $labels = false, $show_ui = true
	) {
		if ( ! class_exists( 'Inflector' ) ) {
			require_once( 'inflector.php' );
		}
		if ( ! $labels ) {
			$labels = array(
				'name'              => _x( $tax_name, $tax_name ),
				'singular_name'     => _x( Inflector::singularize( $tax_name ), $tax_name ),
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
			$labels['parent_item_colon'] = $labels['parent_item'] . __( ':' );
		}
		$slug    = Inflector::pluralize( strtolower( str_replace( " ", "-", $tax_name ) ) );
		$rewrite = array(
			'slug'         => $slug,
			'pages'        => true,
		);
		register_taxonomy( strtolower( str_replace( " ", "_", $tax_name ) ), $post_types, array(
			'hierarchical'      => $is_hierarchical,
			'labels'            => $labels,
			'show_ui'           => $show_ui,
			'has_archive'       => true,
			'show_in_menu'      => true,
			'show_in_nav_menu'  => true,
			'show_in_admin_bar' => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'show_in_rest'      => true,
			'rewrite'           => $rewrite,
		) );
	}

	public
	function load_media() {
		if ( ! isset( $_GET['taxonomy'] ) ) {
			return;
		}
		wp_enqueue_media();
	}

	/**
	 * Add a form field in the new category page
	 *
	 * @since 1.0.0
	 */

	public
	function add_category_image(
		$taxonomy
	) { ?>
        <div class="form-field term-group">
            <label for="author-taxonomy-image-id"><?php _e( 'Image', $this->plugin_name ); ?></label>
            <input type="hidden" id="author-taxonomy-image-id" name="author-taxonomy-image-id" class="custom_media_url"
                   value="">
            <div id="category-image-wrapper"></div>
            <p>
                <input type="button" class="button button-secondary author_tax_media_button"
                       id="author_tax_media_button"
                       name="author_tax_media_button" value="<?php _e( 'Add Image', $this->plugin_name ); ?>"/>
                <input type="button" class="button button-secondary author_tax_media_remove"
                       id="author_tax_media_remove"
                       name="author_tax_media_remove" value="<?php _e( 'Remove Image', $this->plugin_name ); ?>"/>
            </p>
        </div>
	<?php }

	/**
	 * Save the form field
	 *
	 * @since 1.0.0
	 */
	public
	function save_category_image(
		$term_id, $tt_id
	) {
		if ( isset( $_POST['author-taxonomy-image-id'] ) && '' !== $_POST['author-taxonomy-image-id'] ) {
			add_term_meta( $term_id, 'author-taxonomy-image-id', absint( $_POST['author-taxonomy-image-id'] ), true );
		}
	}

	/**
	 * Edit the form field
	 *
	 * @since 1.0.0
	 */
	public
	function update_category_image(
		$term, $taxonomy
	) { ?>
        <tr class="form-field term-group-wrap">
            <th scope="row">
                <label for="author-taxonomy-image-id"><?php _e( 'Image', $this->plugin_name ); ?></label>
            </th>
            <td>
				<?php $image_id = get_term_meta( $term->term_id, 'author-taxonomy-image-id', true ); ?>
                <input type="hidden" id="author-taxonomy-image-id" name="author-taxonomy-image-id"
                       value="<?php echo esc_attr( $image_id ); ?>">
                <div id="category-image-wrapper">
					<?php if ( $image_id ) { ?>
						<?php echo wp_get_attachment_image( $image_id, 'thumbnail' ); ?>
					<?php } ?>
                </div>
                <p>
                    <input type="button" class="button button-secondary author_tax_media_button"
                           id="author_tax_media_button" name="author_tax_media_button"
                           value="<?php _e( 'Add Image', $this->plugin_name ); ?>"/>
                    <input type="button" class="button button-secondary author_tax_media_remove"
                           id="author_tax_media_remove" name="author_tax_media_remove"
                           value="<?php _e( 'Remove Image', $this->plugin_name ); ?>"/>
                </p>
            </td>
        </tr>
	<?php }

	/**
	 * Update the form field value
	 *
	 * @since 1.0.0
	 */
	public
	function updated_category_image(
		$term_id, $tt_id
	) {
		if ( isset( $_POST['author-taxonomy-image-id'] ) && '' !== $_POST['author-taxonomy-image-id'] ) {
			update_term_meta( $term_id, 'author-taxonomy-image-id', absint( $_POST['author-taxonomy-image-id'] ) );
		} else {
			update_term_meta( $term_id, 'author-taxonomy-image-id', '' );
		}
	}

	/**
	 * Enqueue styles and scripts
	 *
	 * @since 1.0.0
	 */
	public
	function add_script() {
		if ( ! isset( $_GET['taxonomy'] ) ) {
			return;
		}
		?>
        <script> jQuery(document).ready(function ($) {
                _wpMediaViewsL10n.insertIntoPost = '<?php _e( "Insert", "author" ); ?>';

                function ct_media_upload(button_class) {
                    var _custom_media = true, _orig_send_attachment = wp.media.editor.send.attachment;
                    $('body').on('click', button_class, function (e) {
                        var button_id = '#' + $(this).attr('id');
                        var send_attachment_bkp = wp.media.editor.send.attachment;
                        var button = $(button_id);
                        _custom_media = true;
                        wp.media.editor.send.attachment = function (props, attachment) {
                            if (_custom_media) {
                                $('#author-taxonomy-image-id').val(attachment.id);
                                $('#category-image-wrapper').html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />');
                                $('#category-image-wrapper .custom_media_image').attr('src', attachment.url).css('display', 'block');
                            } else {
                                return _orig_send_attachment.apply(button_id, [props, attachment]);
                            }
                        }
                        wp.media.editor.open(button);
                        return false;
                    });
                }

                ct_media_upload('.author_tax_media_button.button');
                $('body').on('click', '.author_tax_media_remove', function () {
                    $('#author-taxonomy-image-id').val('');
                    $('#category-image-wrapper').html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />');
                });
                // Thanks: http://stackoverflow.com/questions/15281995/wordpress-create-category-ajax-response
                $(document).ajaxComplete(function (event, xhr, settings) {
                    var queryStringArr = settings.data.split('&');
                    if ($.inArray('action=add-tag', queryStringArr) !== -1) {
                        var xml = xhr.responseXML;
                        $response = $(xml).find('term_id').text();
                        if ($response != "") {
                            // Clear the thumb image
                            $('#category-image-wrapper').html('');
                        }
                    }
                });
            });
        </script>
	<?php }
}

STYLE_AUTHOR_BIOS::getInstance();
