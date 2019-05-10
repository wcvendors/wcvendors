<?php
/**
 * DEPRECAITED - Admin new order email
 *
 * @author  WooThemes
 * @package WooCommerce/Templates/Emails/HTML
 * @version 2.1.8
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

$order_date = $order->get_date_created();

?>

<?php do_action( 'woocommerce_email_header', $email_heading ); ?>

<p><?php printf( __( 'A %s has marked part of your order as shipped. The items that are shipped are as follows:', 'wc-vendors' ), wcv_get_vendor_name( true, false ) ); ?></p>

<?php do_action( 'woocommerce_email_before_order_table', $order, true ); ?>

<h2><?php printf( __( 'Order: %s', 'wc-vendors' ), $order->get_order_number() ); ?>
	(<?php printf( '<time datetime="%s">%s</time>', date_i18n( 'c', strtotime( $order_date ) ), date_i18n( wc_date_format(), strtotime( $order_date ) ) ); ?>
	)</h2>

<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">
	<thead>
	<tr>
		<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Product', 'wc-vendors' ); ?></th>
		<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Quantity', 'wc-vendors' ); ?></th>
		<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Price', 'wc-vendors' ); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	echo wc_get_email_order_items(
		$order, array(
			'show_sku'      => true,
			'show_image'    => false,
			'image_size'    => array( 32, 32 ),
			'plain_text'    => false,
			'sent_to_admin' => false,
		)
	);
	?>
	</tbody>
	<tfoot>
	<?php
	if ( $totals = $order->get_order_item_totals() ) {
		$i = 0;
		foreach ( $totals as $total ) {
			$i ++;
			?>
			<tr>
				<th scope="row" colspan="2" style="text-align:left; border: 1px solid #eee;
				<?php
				if ( $i == 1 ) {
					echo 'border-top-width: 4px;';
				}
				?>
						"><?php echo $total['label']; ?></th>
				<td style="text-align:left; border: 1px solid #eee;
				<?php
				if ( $i == 1 ) {
					echo 'border-top-width: 4px;';
				}
				?>
						"><?php echo $total['value']; ?></td>
			</tr>
			<?php
		}
	}
	?>
	</tfoot>
</table>

<?php do_action( 'woocommerce_email_after_order_table', $order, true ); ?>

<?php do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email ); ?>

<h2><?php _e( 'Customer details', 'wc-vendors' ); ?></h2>

<?php if ( $order->get_billing_email() ) : ?>
	<p><strong><?php _e( 'Email:', 'wc-vendors' ); ?></strong> <?php echo $order->get_billing_email(); ?></p>
<?php endif; ?>
<?php if ( $order->get_billing_phone() ) : ?>
	<p><strong><?php _e( 'Tel:', 'wc-vendors' ); ?></strong> <?php echo $order->get_billing_phone(); ?></p>
<?php endif; ?>

<?php wc_get_template( 'emails/email-addresses.php', array( 'order' => $order ) ); ?>

<?php do_action( 'woocommerce_email_footer' ); ?>
