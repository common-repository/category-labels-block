<?php
/**
 * Utilities.
 *
 * @package CategoryLabelsBlock
 */

/**
 * Provides the active seo plugin name if found, otherwise false.
 *
 * @return string|false - Plugin name, or false if no SEO plugin is active.
 */
function clb_get_active_seo_plugin() {
	$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );

	if ( in_array( 'wordpress-seo/wp-seo.php', $active_plugins, true ) ) {
		return 'yoast';
	}

	if ( in_array( 'seo-by-rank-math/rank-math.php', $active_plugins, true ) ) {
		return 'rank-math';
	}

	return false;
}

/**
 * Checks if any of the following SEO plugin is active:
 *
 * 1. Rankmath
 * 2. Yoast SEO
 *
 * @return bool - True if any active, otherwise false.
 */
function clb_is_seo_plugin_active() {
	return false !== clb_get_active_seo_plugin();
}


/**
 * Gets the primary term based on the installed SEO plugin.
 *
 * @param int $post_id - Post id to get the primary term of.
 *
 * @return WP_Term|false - Primary term if found, otherwise false on errors.
 */
function clb_get_primary_term( $post_id = null ) {

	if ( is_null( $post_id ) ) {
		$post_id = get_the_ID();
	}

	$active_seo_plugin = clb_get_active_seo_plugin();
	$primary_term      = false;

	// Case 1: Yoast.
	if ( 'yoast' === $active_seo_plugin && class_exists( 'WPSEO_Primary_Term' ) ) {
		$yoast_primary_term_instance = new WPSEO_Primary_Term( 'category', $post_id );
		$yoast_primary_term          = get_term( $yoast_primary_term_instance->get_primary_term() );

		if ( is_null( $yoast_primary_term ) || is_wp_error( $yoast_primary_term ) ) {
			return false;
		}

		$primary_term = $yoast_primary_term;
	}

	// Case 2: Rank Math.
	if ( 'rank-math' === $active_seo_plugin ) {
		$rankmath_primary_term_id = get_post_meta( $post_id, 'rank_math_primary_category', true );
		$rankmath_primary_term    = get_term( $rankmath_primary_term_id );

		if ( is_null( $rankmath_primary_term ) || is_wp_error( $rankmath_primary_term ) ) {
			return false;
		}

		$primary_term = $rankmath_primary_term;
	}

	return $primary_term;
}

/**
 * Checks if the given term is set as primary from SEO plugins.
 *
 * @param int   $term_id - Term to check for.
 * @param int[] $attached_terms - All the attached terms
 * @param int   $index - Current term index.
 * @param bool  $fallback - Use fallback
 *
 * @return bool - True if set, otherwise false.
 */
function clb_is_term_primary( $term_id, $attached_terms, $index, $fallback ) {

	$primary_term = clb_get_primary_term();
	$current_term = get_term( $term_id );

	if ( false === $primary_term ) {
		return false;
	}

	if ( false === $fallback ) {
		return $term_id === $primary_term->term_id;
	}

	$has_primary_parent_attached = $primary_term instanceof \WP_Term && in_array( $primary_term->parent, $attached_terms, true );
	$has_current_parent_attached = $current_term instanceof \WP_Term && in_array( $current_term->parent, $attached_terms, true );

	if ( false === $has_primary_parent_attached && false === $has_current_parent_attached && $index === 1 ) {
		$current_post_id = get_the_ID();
		return has_term( $current_term->term_id, $current_term->taxonomy, $current_post_id );
	}

	// Check 1: Check if the current term matching but not have it's parent since that takes priority (priority #2).
	if ( $has_current_parent_attached ) {
		return false;
	}

	// Check 2: Check if the current term is parent (priority #1).
	if ( $primary_term->parent === $term_id ) {
		return true;
	}

	return $term_id === $primary_term->term_id;
}

/**
 * Extracts the post terms from the category labels block.
 *
 * @param  \WP_Block $block - Block.
 * @return int[] - List of term ids.
 */
function clb_extract_post_terms( \WP_Block $block ) {

	// Case 1: Exit if it's not the category labels block.
	if ( 'small-plugins/category-labels' !== $block->name ) {
		return array();
	}

	$attached_terms = array();

	foreach ( $block->inner_blocks as $inner_block ) {
		$term             = $inner_block->attributes['term'];
		$attached_term_id = isset( $term['id'] ) ? $term['id'] : null;

		if ( is_null( $attached_term_id ) ) {
			continue;
		}

		$attached_terms[] = $attached_term_id;
	}

	return $attached_terms;
}
