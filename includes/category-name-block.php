<?php
/**
 * Category Name Block.
 *
 * @package CategoryLabelsBlock
 */

/**
 * Main class that handles the functionality of category name block.
 */
class Category_Labels_Core_Name_Block {

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register' ) );
		add_action( 'init', array( $this, 'register_assets' ) );
	}

	/**
	 * Registers the block on server side.
	 *
	 * @return void
	 */
	public function register() {

		register_block_type_from_metadata(
			CLB_DIR_PATH . 'dist/category-name/block.json',
			array(

				'render_callback' => function( $block_attributes, $content ) {
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
		wp_register_script(
			'category-name-block-script',
			CLB_PLUGIN_URL . 'dist/category-name.js',
			array(),
			uniqid(),
			true
		);

		wp_register_style(
			'category-name-block-editor-style',
			CLB_PLUGIN_URL . 'dist/category-name-editor.css',
			array(),
			uniqid()
		);

		wp_register_style(
			'category-name-block-frontend-style',
			CLB_PLUGIN_URL . 'dist/category-name-frontend.css',
			array(),
			uniqid()
		);
	}
}

new Category_Labels_Core_Name_Block();
