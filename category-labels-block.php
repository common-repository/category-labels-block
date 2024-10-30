<?php

/**
 * Plugin Name: Category Labels Block
 * Description: An helpful block that allows displaying a subset of associated post categories.
 * Author: Small Plugins
 * Author URI: https://www.smallplugins.com/
 * Version: 1.0.1
 * Requires at least: 5.8.3
 * Requires PHP: 5.7
 * Text Domain: category-labels-block
 * Domain Path: /languages
 * Tested up to: 6.6
 *
 * @package CategoryLabelsBlock
 */
if ( !defined( 'ABSPATH' ) ) {
    die( 'No direct access' );
}
if ( !defined( 'CLB_DIR_PATH' ) ) {
    define( 'CLB_DIR_PATH', \plugin_dir_path( __FILE__ ) );
}
if ( !defined( 'CLB_PLUGIN_URL' ) ) {
    define( 'CLB_PLUGIN_URL', \plugins_url( '/', __FILE__ ) );
}
if ( !defined( 'CLB_PLUGIN_BASE_NAME' ) ) {
    define( 'CLB_PLUGIN_BASE_NAME', \plugin_basename( __FILE__ ) );
}
if ( !class_exists( 'Category_Labels_Block' ) ) {
    /**
     * Main plugin class
     */
    final class Category_Labels_Block {
        /**
         * Var to make sure we only load once
         *
         * @var boolean $loaded
         */
        public static $loaded = false;

        /**
         * Constructor
         *
         * @return void
         */
        public function __construct() {
            if ( !static::$loaded ) {
                if ( !function_exists( 'clb_fs' ) ) {
                    // Create a helper function for easy SDK access.
                    function clb_fs() {
                        global $clb_fs;
                        if ( !isset( $clb_fs ) ) {
                            // Include Freemius SDK.
                            require_once dirname( __FILE__ ) . '/freemius/start.php';
                            $clb_fs = fs_dynamic_init( array(
                                'id'             => '12285',
                                'slug'           => 'category-labels-block',
                                'type'           => 'plugin',
                                'public_key'     => 'pk_4f252538ecc4618f878a5e0d3af85',
                                'is_premium'     => false,
                                'has_addons'     => false,
                                'has_paid_plans' => true,
                                'menu'           => array(
                                    'first-path' => 'plugins.php',
                                    'support'    => false,
                                ),
                                'is_live'        => true,
                            ) );
                        }
                        return $clb_fs;
                    }

                    // Init Freemius.
                    clb_fs();
                    // Signal that SDK was initiated.
                    do_action( 'clb_fs_loaded' );
                }
                // Utils.
                require_once CLB_DIR_PATH . 'includes/utils.php';
                // Blocks.
                require_once CLB_DIR_PATH . 'includes/category-labels-block.php';
                require_once CLB_DIR_PATH . 'includes/category-link-block.php';
                require_once CLB_DIR_PATH . 'includes/category-name-block.php';
                static::$loaded = true;
            }
        }

    }

    new Category_Labels_Block();
}