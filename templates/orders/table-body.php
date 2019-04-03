<?php
/**
 * Orders table-body
 *
 * This template can be overridden by copying it to yourtheme/wc-vendors/orders/table-body.php
 *
 * @author  WC Vendors
 * @package WCVendors/Templates/Orders/
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $count > 1 ) : ?>

<tr>

	<?php endif; ?>

	<?php if ( $item->get_formatted_meta_data() ) : ?>

	<td colspan="5">
		<?php echo wc_display_item_meta( $item ); ?>
	</td>

<td colspan="3">

<?php else : ?>

	<td colspan="100%">

		<?php endif; ?>

		<?php printf( __( 'Quantity: %d', 'wc-vendors' ), $item['qty'] ); ?>
	</td>

	<?php if ( $count > 1 ) : ?>

</tr>

<?php endif; ?>
