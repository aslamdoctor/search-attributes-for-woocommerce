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
 */
function wsatt_get_all_product_attributes() {
	global $wpdb;
	$attribute_taxonomies = $wpdb->get_results( 'SELECT * FROM ' . esc_sql( $wpdb->prefix ) . 'woocommerce_attribute_taxonomies ORDER BY attribute_name ASC;' ); // phpcs:ignore
	set_transient( 'wc_attribute_taxonomies', $attribute_taxonomies );
	$attribute_taxonomies = array_filter( $attribute_taxonomies );
	return $attribute_taxonomies;
}
