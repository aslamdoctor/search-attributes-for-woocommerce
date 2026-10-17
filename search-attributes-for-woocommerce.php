<?php

/**
 * Plugin Name: Search Attributes for WooCommerce
 * Plugin URI: https://wordpress.org/plugins/search-attributes-for-woocommerce/
 * Description: This WordPress plugin allows you to extend WordPress search feature by searching into Woocommerce product attributes
 * Version: 1.3.7
 * Author: Aslam Doctor
 * Author URI: https://aslamdoctor.com/
 * Developer: Aslam Doctor
 * Developer URI: https://aslamdoctor.com/
 * Text Domain:  wsatt
 * Domain Path: /languages
 * Requires at least: 6.6
 *
 * WC requires at least: 4.3
 * WC tested up to: 11.2.1
 *
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package wsatt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! defined( 'WSATT_VERSION' ) ) {
	define( 'WSATT_VERSION', '1.3.7' );
}

/**
 * Check if woocommerce is installed and activated before activating plugin.
 */
function wsatt_check_required_plugin() {
	if ( is_admin() && current_user_can( 'activate_plugins' ) && ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'wsatt_plugin_notice' );

		deactivate_plugins( plugin_basename( __FILE__ ) );

		if ( isset( $_GET['activate'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			unset( $_GET['activate'] ); // phpcs:ignore WordPress.Security.NonceVerification
		}
	}
	register_setting(
		'wsatt-settings-group',
		'wsatt_status',
		array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'default'           => 0,
		)
	);
	register_setting(
		'wsatt-settings-group',
		'wsatt_attributes',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'wsatt_sanitize_attributes',
			'default'           => array(),
		)
	);
}

add_action( 'admin_init', 'wsatt_check_required_plugin' );

/**
 * Sanitize attributes array.
 *
 * @param array|mixed $attributes Attribute list from form.
 * @return array
 */
function wsatt_sanitize_attributes( $attributes ) {
	if ( ! is_array( $attributes ) ) {
		return array();
	}
	return array_values( array_map( 'sanitize_text_field', $attributes ) );
}

/**
 * Show plugin activation notice
 */
function wsatt_plugin_notice() {
	?>
	<div class="error"><p><?php esc_html_e( 'Please activate Woocommerce plugin before using', 'wsatt' ); ?> <strong><?php esc_html_e( 'Search Attributes for WooCommerce', 'wsatt' ); ?></strong> <?php esc_html_e( 'plugin.', 'wsatt' ); ?></p></div>
	<?php
}

/**
 * Add Admin Menu.
 */
function wsatt_register_my_custom_submenu_page() {
	if ( is_user_logged_in() ) {
		$user = wp_get_current_user();
		$role = (array) $user->roles;

		if ( in_array( 'administrator', $role, true ) ) {
			wsatt_add_pages( 'manage_options' );
		} elseif ( in_array( 'shop_manager', $role, true ) ) {
			wsatt_add_pages( 'shop_manager' );
		} elseif ( current_user_can( 'manage_woocommerce' ) ) {
			wsatt_add_pages( 'shop_manager' );
		}
	}
}
add_action( 'admin_menu', 'wsatt_register_my_custom_submenu_page', 99 );

/**
 * Add page to admin menu
 *
 * @param [String] $role Current User role.
 */
function wsatt_add_pages( $role ) {
	add_submenu_page(
		'woocommerce',
		__( 'Search Attributes for WooCommerce', 'wsatt' ),
		__( 'Search Attributes for WooCommerce', 'wsatt' ),
		$role,
		'wsatt-page',
		'wsatt_callback'
	);
}


/**
 * Add Plugin Settings link on Plugins page.
 *
 * @param [Array] $links All links related to specific plugin under Admin>Plugins section.
 */
function wsatt_plugin_settings_link( $links ) {
	$settings_link = admin_url( 'admin.php?page=wsatt-page' );
	array_unshift( $links, '<a href="' . esc_url( $settings_link ) . '">' . __( 'Settings', 'wsatt' ) . '</a>' );
	return $links;
}
$wsatt_plugin = plugin_basename( __FILE__ );
add_filter( 'plugin_action_links_' . esc_html( $wsatt_plugin ), 'wsatt_plugin_settings_link' );



/**
 * Load Admin Page View.
 */
function wsatt_callback() {
	include 'inc/functions.php';
	include 'view.php';
}


/**
 * Add Scripts and Styles.
 *
 * @param [String] $hook The hook to enqueue stylesheet and scripts.
 */
function wsatt_enqueue( $hook ) {
	if ( 'woocommerce_page_wsatt-page' !== $hook ) {
			return;
	}

	// Stylesheets.
	wp_register_style( 'wsatt_app_css', ( plugin_dir_url( __FILE__ ) . '/css/app.css' ), false, WSATT_VERSION );
	wp_enqueue_style( 'wsatt_app_css' );
}
add_action( 'admin_enqueue_scripts', 'wsatt_enqueue' );


/**
 * Execute Hook if settings Enabled.
 */
$wsatt_status = get_option( 'wsatt_status' );
if ( isset( $wsatt_status ) && ! empty( $wsatt_status ) ) {
	/**
	 * Apply search filters
	 *
	 * @param WP_Query $query Global wp_query to modify.
	 */
	function wsatt_search_filter( $query ) {
		if ( is_admin() ) {
			return $query;
		}

		if ( $query->is_search() && get_search_query() ) {
			add_filter( 'posts_where', 'wsatt_brands_where' );
		}

		return $query;
	}
	add_filter( 'pre_get_posts', 'wsatt_search_filter' );

	/**
	 * Modify sql query for wp_query
	 *
	 * @param string $where Where condition for SQL query.
	 */
	function wsatt_brands_where( $where = '' ) {
		global $wpdb;

		// Remove filter to prevent modifying subsequent queries in the same request.
		remove_filter( 'posts_where', 'wsatt_brands_where' );

		$wsatt_attributes = get_option( 'wsatt_attributes' );
		if ( isset( $wsatt_attributes ) && is_array( $wsatt_attributes ) && count( $wsatt_attributes ) > 0 ) {
			$search_term = trim( get_search_query() );
			if ( '' !== $search_term ) {
				$taxonomies   = array_values( array_map( 'sanitize_text_field', $wsatt_attributes ) );
				$placeholders = implode( ', ', array_fill( 0, count( $taxonomies ), '%s' ) );
				$like         = '%' . $wpdb->esc_like( $search_term ) . '%';

				$query_params = array_merge( $taxonomies, array( $like ) );
				// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $placeholders contains safe generated %s placeholders.
				$sql          = $wpdb->prepare(
					" OR $wpdb->posts.ID IN (
						SELECT $wpdb->posts.ID
						FROM $wpdb->posts
						LEFT JOIN $wpdb->term_relationships ON ( $wpdb->posts.ID = $wpdb->term_relationships.object_id )
						LEFT JOIN $wpdb->term_taxonomy ON ( $wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id )
						LEFT JOIN $wpdb->terms ON ( $wpdb->term_taxonomy.term_id = $wpdb->terms.term_id )
						WHERE $wpdb->posts.post_type = 'product'
						AND $wpdb->posts.post_status = 'publish'
						AND $wpdb->term_taxonomy.taxonomy IN ($placeholders)
						AND $wpdb->terms.name LIKE %s
					)",
					$query_params
				);
				// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

				if ( $sql ) {
					$where .= $sql;
				}
			}
		}

		return $where;
	}
}


/**
 * Declare HPOS compatibility for the plugin
 */
function wsatt_declare_hpos_compatibility() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
}
add_action( 'before_woocommerce_init', 'wsatt_declare_hpos_compatibility' );


/**
 * Add sponsor link to plugin listing page.
 *
 * @param [Array]  $links All links related to specific plugin under Admin>Plugins section.
 * @param [String] $file The plugin file path.
 */
function wsatt_sponsor_link( $links, $file ) {
	if ( $file === plugin_basename( __FILE__ ) ) {
		$links[] = '<a href="https://github.com/sponsors/aslamdoctor"><span class="dashicons dashicons-star-filled" aria-hidden="true" style="font-size:14px;line-height:1.3"></span>' . __( 'Sponsor', 'wsatt' ) . '</a>';
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'wsatt_sponsor_link', 10, 2 );
