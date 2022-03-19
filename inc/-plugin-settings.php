<?php
/**
 * Settings Page Template
 *
 * @package wsatt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<form action="options.php" method='post'>
	<?php settings_fields( 'wsatt-settings-group' ); ?>
	<?php do_settings_sections( 'wsatt-settings-group' ); ?>


	<?php $wsatt_status = esc_attr( get_option( 'wsatt_status' ) ); ?>
	<?php $wsatt_attributes = get_option( 'wsatt_attributes' ); ?>

	<table class="form-table" role="presentation">
		<tbody>
			<tr>
				<th scope="row"><?php esc_html_e( 'Status', 'wsatt' ); ?></th>
				<td>
					<fieldset class="flex justify-content-start">
						<p class=mr-1>
							<label>
								<input
									name="wsatt_status" 
									type="radio"
									value="1"
									class="tog"
									<?php echo ( ! empty( $wsatt_status ) ) ? 'checked' : ''; ?>
								/>
								<?php esc_html_e( 'Enable', 'wsatt' ); ?>
							</label>
						</p>
						<p>
							<label>
								<input
									name="wsatt_status"
									type="radio"
									value="0"
									class="tog"
									<?php echo ( empty( $wsatt_status ) ) ? 'checked' : ''; ?>
								/>
								<?php esc_html_e( 'Disable', 'wsatt' ); ?>
							</label>
						</p>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Select Product Attributes', 'wsatt' ); ?></th>
				<td>
					<?php
					foreach ( wsatt_get_all_product_attributes() as $attribute ) :
						?>
					<fieldset>
						<label for="wsatt_attributes_<?php echo esc_attr( $attribute->attribute_id ); ?>">
							<input name="wsatt_attributes[]" type="checkbox" id="wsatt_attributes_<?php echo esc_attr( $attribute->attribute_id ); ?>" value="<?php echo 'pa_' . esc_attr( $attribute->attribute_name ); ?>"
								<?php echo ( is_array( $wsatt_attributes ) && in_array( 'pa_' . esc_html( $attribute->attribute_name ), $wsatt_attributes, true ) ) ? 'checked' : ''; ?>>
							<?php echo esc_html( $attribute->attribute_label ); ?>
						</label>
					</fieldset>
						<?php
					endforeach;
					?>
				</td>
			</tr>
		</tbody>
	</table>

	<div class="submit-btn-wrapper">
		<?php submit_button(); ?>
	</div>


	<?php if ( isset( $_GET['settings-updated'] ) ) : // phpcs:ignore ?>
		<div class="notice notice-success is-dismissible">
		<p><strong><?php esc_html_e( 'Changes have been save.' ); ?></strong></p>
		</div>
	<?php endif; ?>

</form> 

<?php require 'notice.php'; ?>
