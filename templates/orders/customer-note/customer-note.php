<?php
/**
 * Customer Note Template
 *
 * This template can be overridden by copying it to yourtheme/wc-vendors/orders/customer-note/customer-note.php
 *
 * @author        Jamie Madden, WC Vendors
 * @package       WCVendors/Templates/Emails/HTML
 * @version       2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<tr>
	<td colspan="100%">
		<h2>
			<?php _e( 'Customer note', 'wc-vendors' ); ?>
		</h2>

		<p>
			<?php echo $customer_note ? $customer_note : __( 'No customer note.', 'wc-vendors' ); ?>
		</p>
	</td>
</tr>
