<?php
/**
 * Vendor Order Customer Details
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/vendor-order-addresses.php.
 *
 * @author  Jamie Madden, WC Vendors
 * @package WCVendors/Templates/Emails
 * @version 2.0.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$text_align = is_rtl() ? 'right' : 'left';

?>
<table id="addresses" cellspacing="0" cellpadding="0"
       style="width: 100%; vertical-align: top; margin-bottom: 40px; padding:0;" border="0">
	<tr>
		<td style="text-align:<?php echo $text_align; ?>; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; border:0; padding:0;"
		    valign="top" width="50%">
			<?php if ( $show_billing_address ) : ?>
				<h2><?php _e( 'Billing address', 'wc-vendors' ); ?></h2>

				<address class="address">
					<?php if ( $show_customer_billing_name ) : ?>
						<?php echo esc_html( $customer_billing_name ); ?><br/>
					<?php endif; ?>
					<?php echo ( $address = $order->get_formatted_billing_address() ) ? $address : __( 'N/A', 'wc-vendors' ); ?>
					<?php if ( $show_customer_phone ) : ?>
						<?php if ( $order->get_billing_phone() ) : ?>
							<br/><?php echo esc_html( $order->get_billing_phone() ); ?>
						<?php endif; ?>
					<?php endif; ?>
					<?php if ( $show_customer_email ) : ?>
						<?php if ( $order->get_billing_email() ) : ?>
							<p><?php echo esc_html( $order->get_billing_email() ); ?></p>
						<?php endif; ?>
					<?php endif; ?>
				</address>
			<?php endif; ?>
		</td>
		<?php if ( $show_shipping_address ) : ?>
			<?php if ( ! wc_ship_to_billing_address_only() && $order->needs_shipping_address() && ( $shipping = $order->get_formatted_shipping_address() ) ) : ?>
				<td style="text-align:<?php echo $text_align; ?>; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; padding:0;"
				    valign="top" width="50%">
					<h2><?php _e( 'Shipping address', 'wc-vendors' ); ?></h2>
					<?php if ( $show_customer_shipping_name ) : ?>
						<?php echo esc_html( $customer_shipping_name ); ?>
					<?php endif; ?>

					<address class="address"><?php echo $shipping; ?></address>
				</td>
			<?php endif; ?>
		<?php endif; ?>
	</tr>
</table>
