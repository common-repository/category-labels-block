<?php
/**
 * Category Labels Block.
 *
 * @package CategoryLabelsBlock
 */

/**
 * Main class that handles the server-side functionality of category labels block.
 */
class Category_Labels_Core_Block {

	/**
	 * Variable used to track index count.
	 *
	 * @var int
	 */
	private $index_count = 0;

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register' ) );
		add_action( 'init', array( $this, 'register_assets' ) );
		add_filter( 'render_block_context', array( $this, 'add_magic_context' ), 10, 3 );
	}

	/**
	 * Passes the calculated index count as context to post term block.
	 * So that the post term block can later apply limit restriction.
	 *
	 * @param array     $context - Current block context.
	 * @param array     $block - Parsed block.
	 * @param \WP_Block $parent_block - Parent block.
	 *
	 * @return array - Modified block data.
	 */
	public function add_magic_context( $context, $block, $parent_block ) {
		$block_name = isset( $block['blockName'] ) ? $block['blockName'] : null;

		if ( 'small-plugins/post-term' === $block_name ) {

			$attached_term_id = isset( $block['attrs']['term']['id'] ) ? $block['attrs']['term']['id'] : null;

			if ( is_null( $attached_term_id ) ) {
				return $context;
			}

			$attached_term = get_term( $attached_term_id );

			if ( is_null( $attached_term ) || is_wp_error( $attached_term ) ) {
				return $context;
			}

			$is_term_attached = has_term( $attached_term->term_id, $attached_term->taxonomy, get_the_ID() );

			if ( $is_term_attached ) {
				$this->index_count++;
			}

			$context['small-plugins/post-term/index']       = $this->index_count;
			$context['small-plugins/category-labels-block'] = clb_extract_post_terms( $parent_block );
		} elseif ( 'small-plugins/category-labels' === $block_name ) {
			$this->index_count = 0;
		}

		return $context;
	}

	/**
	 * Registers the block on server side.
	 *
	 * @return void
	 */
	public function register() {

		register_block_type_from_metadata(
			CLB_DIR_PATH . 'dist/category-labels/block.json',
			array(
				/**
				 * Adding a rendering condition to exit the block when the content is none to avoid unnecessary html.
				 *
				 * @param array $block_attributes - Attributes.
				 * @param string $content - Block content.
				 * @param \WP_Block $block_instance - Block instance.
				 *
				 * @return string - Block content.
				 */
				'render_callback' => function( $block_attributes, $content, $block_instance ) {
					if ( '<div class="wp-block-small-plugins-category-labels"></div>' === trim( $content ) ) {
						return '';
					}

					return $content;
				},
			)
		);

	}

	/**
	 * Registers the necessary block assets.
	 *
	 * @return void
	 */
	public function register_assets() {

		$metadata_path = CLB_DIR_PATH . 'dist/category-labels.asset.php';
		$metadata      = is_readable( $metadata_path ) ? require $metadata_path : array(
			'version'      => 'initial',
			'dependencies' => array(),
		);

		wp_register_script(
			'category-labels-block-script',
			CLB_PLUGIN_URL . 'dist/category-labels.js',
			$metadata['dependencies'],
			uniqid(),
			true
		);

		wp_localize_script(
			'category-labels-block-script',
			'smallPluginsCategoryLabelsBlockData',
			array(
				'hasSeoPlugin' => clb_is_seo_plugin_active() ? 'true' : 'false',
				'isPremium'    => clb_fs()->is_plan( 'plus', true ) ? 'true' : 'false',
			)
		);

		wp_register_style(
			'category-labels-block-editor-style',
			CLB_PLUGIN_URL . 'dist/category-labels-editor.css',
			array(),
			uniqid()
		);

		wp_register_style(
			'category-labels-block-frontend-style',
			CLB_PLUGIN_URL . 'dist/category-labels-frontend.css',
			array(),
			uniqid()
		);
	}
}

new Category_Labels_Core_Block();
