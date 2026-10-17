<?php
/**
 * Helper Functions
 *
 * @package wsatt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get all product attributes.
 *
 * @return array
 */
function wsatt_get_all_product_attributes() {
	if ( function_exists( 'wc_get_attribute_taxonomies' ) ) {
		$attribute_taxonomies = wc_get_attribute_taxonomies();
		if ( ! empty( $attribute_taxonomies ) ) {
			return $attribute_taxonomies;
		}
	}

	global $wpdb;
	$attribute_taxonomies = $wpdb->get_results( 'SELECT * FROM ' . esc_sql( $wpdb->prefix ) . 'woocommerce_attribute_taxonomies ORDER BY attribute_name ASC;' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$attribute_taxonomies = array_filter( (array) $attribute_taxonomies );
	return $attribute_taxonomies;
}
