<?php
/**
 * Vendor Order details table shown in emails.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/vendor-order-details.php.
 *
 * @author  Jamie Madden, WC Vendors
 * @package WCVendors/Templates/Emails
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$text_align = is_rtl() ? 'right' : 'left';

$colspan = ( 'both' === $totals_display ) ? 3 : 2;

do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email ); ?>

<h2>
	<?php
	$before = apply_filters( 'wcvendors_order_number_link_in_email_template_before', $before = '', $order, $sent_to_admin, $plain_text, $email );
	$after 	= apply_filters( 'wcvendors_order_number_link_in_email_template_after', $after = '', $order, $sent_to_admin, $plain_text, $email );
	
	/* translators: %s: Order ID. */
	echo wp_kses_post( $before . sprintf( __( 'Order #%s', 'wc-vendors' ) . $after . ' (<time datetime="%s">%s</time>)', $order->get_order_number(), $order->get_date_created()->format( 'c' ), wc_format_datetime( $order->get_date_created() ) ) );
	?>
</h2>

<div style="margin-bottom: 40px;">
	<table class="td" cellspacing="0" cellpadding="6"
	       style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
		<thead>
		<tr>
			<th class="td" scope="col"
			    style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Product', 'wc-vendors' ); ?></th>
			<th class="td" scope="col"
			    style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Quantity', 'wc-vendors' ); ?></th>
			<?php if ( 'both' === $totals_display || 'commission' === $totals_display ) : ?>
				<th class="td" scope="col"
				    style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Commission', 'wc-vendors' ); ?></th>
			<?php endif; ?>
			<?php if ( 'both' === $totals_display || 'product' === $totals_display ) : ?>
				<th class="td" scope="col"
				    style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Price', 'wc-vendors' ); ?></th>
			<?php endif; ?>

		</tr>
		</thead>
		<tbody>
		<?php do_action_deprecated( 'wcv_email_vendor_notify_order_before_order_items', array( $order, $sent_to_admin, $plain_text, $email ), '2.3.0', 'wcvendors_email_vendor_notify_order_before_order_items' ); ?>
		<?php do_action( 'wcvendors_email_vendor_notify_order_before_order_items', $order, $sent_to_admin, $plain_text, $email ); ?>
		<?php
		echo wcv_get_vendor_order_items(
			$order, array( // WPCS: XSS ok.
			               'show_sku'       => $sent_to_vendor,
			               'vendor_id'      => $vendor_id,
			               'vendor_items'   => $vendor_items,
			               'totals_display' => $totals_display,
			               'show_image'     => false,
			               'image_size'     => array( 32, 32 ),
			               'plain_text'     => $plain_text,
			               'sent_to_admin'  => $sent_to_admin,
			               'sent_to_vendor' => $sent_to_vendor,
			)
		);
		?>
		<?php do_action_deprecated( 'wcv_email_vendor_notify_order_after_order_items', array( $order, $sent_to_admin, $plain_text, $email ), '2.3.0', 'wcvendors_email_vendor_notify_order_after_order_items' ); ?>
		<?php do_action( 'wcvendors_email_vendor_notify_order_after_order_items', $order, $sent_to_admin, $plain_text, $email ); ?>
		</tbody>
		<tfoot>
		<?php
		$totals = wcv_get_vendor_item_totals( $order, $vendor_items, $vendor_id, $email, $totals_display );

		do_action_deprecated( 'wcv_before_vendor_item_totals', array( $order, $vendor_id, $email, $totals, $colspan, $text_align ), '2.3.0', 'wcvendors_before_vendor_item_totals' );
		do_action( 'wcvendors_before_vendor_item_totals', $order, $vendor_id, $email, $totals, $colspan, $text_align );

		if ( $totals ) {
			$i = 0;
			foreach ( $totals as $total ) {
				$i ++;
				?>
				<tr>
					<th class="td" scope="row"
					    style="text-align:<?php echo esc_attr( $text_align ); ?>; <?php echo ( 1 === $i ) ? 'border-top-width: 4px;' : ''; ?>"><?php echo wp_kses_post( $total['label'] ); ?></th>
					<td class="td" colspan="<?php echo $colspan; ?>"
					    style="text-align:<?php echo esc_attr( $text_align ); ?>; <?php echo ( 1 === $i ) ? 'border-top-width: 4px;' : ''; ?>"><?php echo wp_kses_post( $total['value'] ); ?></td>
				</tr>
				<?php
			}
		}

		do_action_deprecated( 'wcv_after_vendor_item_totals', array( $order, $vendor_id, $email, $totals, $colspan, $text_align ), '2.3.0', 'wcvendors_after_vendor_item_totals' );
		do_action( 'wcvendors_after_vendor_item_totals', $order, $vendor_id, $email, $totals, $colspan, $text_align );

		if ( $order->get_customer_note() ) {
			?>
			<tr>
				<th class="td" scope="row" colspan="<?php echo $colspan; ?>"
				    style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Note:', 'wc-vendors' ); ?></th>
				<td class="td"
				    style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php echo wp_kses_post( wptexturize( $order->get_customer_note() ) ); ?></td>
			</tr>
			<?php do_action_deprecated( 'wcv_vendor_notify_order_after_customer_note', array( $order, $vendor_id, $email ), '2.3.0', 'wcvendors_vendor_notify_order_after_customer_note' ); ?>
			<?php do_action( 'wcvendors_vendor_notify_order_after_customer_note', $order, $vendor_id, $email ); ?>
			<?php
		}
		?>
		</tfoot>
	</table>
</div>

<?php do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email ); ?>
