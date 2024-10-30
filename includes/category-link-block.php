<?php

/**
 * Category Link Block.
 *
 * @package CategoryLabelsBlock
 */
/**
 * Main class that handles the server-side functionality of category link block.
 */
class Category_Link_Core_Block {
    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct() {
        add_action( 'init', array($this, 'register') );
        add_action( 'init', array($this, 'register_assets') );
    }

    /**
     * Registers the block on server side.
     *
     * @return void
     */
    public function register() {
        register_block_type_from_metadata( CLB_DIR_PATH . 'dist/category-link/block.json', array(
            'render_callback' => function ( $block_attributes, $content, $block_instance ) {
                // Check 1: Exiting when no terms are assigned to the block.
                if ( !isset( $block_attributes['term'] ) || empty( $block_attributes['term'] ) ) {
                    return '';
                }
                $should_scope = false !== $block_instance->context['category-labels-block/scope-to-current-post'];
                $display_primary = false !== $block_instance->context['category-labels-block/display-primary'] && clb_is_seo_plugin_active();
                $fallback_primary = $block_instance->context['category-labels-block/fallback-primary'];
                $limit = ( ctype_digit( $block_instance->context['category-labels-block/limit'] ) ? (int) $block_instance->context['category-labels-block/limit'] : null );
                $current_index = $block_instance->context['small-plugins/post-term/index'];
                $attached_terms = $block_instance->context['small-plugins/category-labels-block'];
                $term = $block_attributes['term'];
                $term_id = $term['id'];
                $current_term = get_term( $term_id );
                if ( is_null( $current_term ) || is_wp_error( $current_term ) ) {
                    return '';
                }
                $current_post_id = get_the_ID();
                $is_term_attached = has_term( $current_term->term_id, $current_term->taxonomy, $current_post_id );
                $content = str_replace( '{{title}}', $current_term->name, $content );
                $content = str_replace( '{{link}}', get_term_link( $current_term ), $content );
                // Checking if the term is primary.
                if ( $should_scope && $display_primary ) {
                    $is_term_attached = clb_is_term_primary(
                        $current_term->term_id,
                        $attached_terms,
                        $current_index,
                        $fallback_primary
                    );
                }
                if ( $should_scope ) {
                    return ( $is_term_attached ? $content : '' );
                }
                return $content;
            },
        ) );
    }

    /**
     * Registers the necessary block assets.
     *
     * @return void
     */
    public function register_assets() {
        wp_register_script(
            'category-link-block-script',
            CLB_PLUGIN_URL . 'dist/category-link.js',
            array(),
            uniqid(),
            true
        );
        wp_register_style(
            'category-link-block-editor-style',
            CLB_PLUGIN_URL . 'dist/category-link-editor.css',
            array(),
            uniqid()
        );
        wp_register_style(
            'category-link-block-frontend-style',
            CLB_PLUGIN_URL . 'dist/category-link-frontend.css',
            array(),
            uniqid()
        );
    }

}

new Category_Link_Core_Block();