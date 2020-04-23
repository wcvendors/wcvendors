<?php
/**
 * Shipping Form Template
 *
 * This template can be overridden by copying it to yourtheme/wc-vendors/orders/shipping/shipping-form.php
 *
 * @author        Jamie Madden, WC Vendors
 * @package       WCVendors/Templates/Emails/HTML
 * @version       2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<form method="post" name="track_shipment" id="track-shipment_<?php echo esc_attr( $order_id ); ?>">

	<?php
	wp_nonce_field( 'track-shipment' );

	// Providers.
	echo '<p class="form-field tracking_provider_field"><label for="tracking_provider">' . esc_attr__( 'Provider:', 'wc-vendors' ) . '</label><br/><select id="tracking_provider" name="tracking_provider" class="tracking_provider" style="width:100%;">';

	echo '<option value="">' . esc_attr__( 'Custom Provider', 'wc-vendors' ) . '</option>';

	$selected_provider = get_post_meta( $order_id, '_tracking_provider', true );

	$class = '';

	foreach ( $providers as $provider_group => $providerss ) {

		echo '<optgroup label="' . $provider_group . '">';

		foreach ( $providerss as $provider => $url ) {
			echo '<option value="' . sanitize_title( $provider ) . '" ' . selected( sanitize_title( $provider ), $selected_provider, true ) . '>' . $provider . '</option>';
			if ( selected( sanitize_title( $provider ), $selected_provider ) ) {
				$class = 'hidden';
			}
		}

		echo '</optgroup>';

	}

	echo '</select> ';

	woocommerce_wp_text_input(
		array(
			'id'            => 'custom_tracking_provider_name',
			'label'         => __( 'Provider Name:', 'wc-vendors' ),
			'wrapper_class' => $class,
			'placeholder'   => '',
			'description'   => '',
			'value'         => get_post_meta( $order_id, '_custom_tracking_provider', true ),
		)
	);

	woocommerce_wp_text_input(
		array(
			'id'          => 'tracking_number',
			'label'       => __( 'Tracking number:', 'wc-vendors' ),
			'placeholder' => '',
			'description' => '',
			'value'       => get_post_meta( $order_id, '_tracking_number', true ),
		)
	);

	woocommerce_wp_text_input(
		array(
			'id'            => 'custom_tracking_url',
			'label'         => __( 'Tracking link:', 'wc-vendors' ),
			'placeholder'   => 'http://',
			'wrapper_class' => $class,
			'description'   => '',
			'value'         => get_post_meta( $order_id, '_custom_tracking_link', true ),
		)
	);

	woocommerce_wp_text_input(
		array(
			'type'        => 'date',
			'id'          => 'date_shipped',
			'label'       => __( 'Date shipped:', 'wc-vendors' ),
			'placeholder' => 'YYYY-MM-DD',
			'description' => '',
			'class'       => 'date-picker-field',
			'value'       => ( $date = get_post_meta( $order_id, '_date_shipped', true ) ) ? gmdate( 'Y-m-d', $date ) : '',
		)
	);

	// Live preview.
	echo '<p class="preview_tracking_link" style="display:none;">' . esc_attr__( 'Preview:', 'wc-vendors' ) . ' <a href="" target="_blank">' . __( 'Click here to track your shipment', 'wc-vendors' ) . '</a></p>';

	?>


	<input type="hidden" name="product_id" value="<?php echo esc_attr( $product_id ); ?>">
	<input type="hidden" name="order_id" value="<?php echo esc_attr( $order_id ); ?>">

	<input class="button" type="submit" name="update_tracking"
		value="<?php esc_attr_e( 'Update tracking number', 'wc-vendors' ); ?>">
	<input class="button" type="submit" name="mark_shipped"
		value="<?php esc_attr_e( 'Mark as shipped', 'wc-vendors' ); ?>">

</form>
