<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package wsatt
 */

// Exit if uninstall constant is not defined.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'wsatt_status' );
delete_option( 'wsatt_attributes' );
