<?php
/**
 * Show admin notice
 *
 * @package wsatt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div class="notice notice-warning is-dismissible">
	<p><?php esc_html_e( 'The plugin may not effect in case you are using some other product search related plugin for woocommerce.', 'wsatt' ); ?></p>
</div>
