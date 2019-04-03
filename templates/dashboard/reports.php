<?php
/**
 * Reports Template
 *
 * This template can be overridden by copying it to yourtheme/wc-vendors/dashboard/reports.php
 *
 * @author        Jamie Madden, WC Vendors
 * @package       WCVendors/Templates/dashboard
 * @version       2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>


<h2><?php _e( 'Sales Report', 'wc-vendors' ); ?></h2>

<?php

if ( $datepicker !== 'false' ) {
	wc_get_template(
		'date-picker.php', array(
		'start_date' => $start_date,
		'end_date'   => $end_date,
	), 'wc-vendors/dashboard/', wcv_plugin_dir . 'templates/dashboard/'
	);
}

?>

<table class="table table-condensed table-vendor-sales-report">
	<thead>
	<tr>
		<th class="product-header"><?php _e( 'Product', 'wc-vendors' ); ?></th>
		<th class="quantity-header"><?php _e( 'Quantity', 'wc-vendors' ); ?></th>
		<th class="commission-header"><?php _e( 'Commission', 'wc-vendors' ); ?></th>
		<th class="rate-header"><?php _e( 'Rate', 'wc-vendors' ); ?></th>
		<th></th>
	</thead>
	<tbody>

	<?php if ( ! empty( $vendor_summary ) ) : ?>


		<?php if ( ! empty( $vendor_summary['products'] ) ) : ?>

			<?php
			foreach ( $vendor_summary['products'] as $product ) :
				$_product = wc_get_product( $product['id'] );
				?>

				<tr>

					<td class="product"><strong><a
									href="<?php echo esc_url( get_permalink( $_product->get_id() ) ); ?>"><?php echo $product['title']; ?></a></strong>
						<?php
						if ( ! empty( $_product->variation_id ) ) {
							echo woocommerce_get_formatted_variation( $_product->variation_data );
						}
						?>
					</td>
					<td class="qty"><?php echo $product['qty']; ?></td>
					<td class="commission"><?php echo wc_price( $product['cost'] ); ?></td>
					<td class="rate"><?php echo sprintf( '%.2f%%', $product['commission_rate'] ); ?></td>

					<?php if ( $can_view_orders ) : ?>
						<td>
							<a href="<?php echo $product['view_orders_url']; ?>"><?php _e( 'Show Orders', 'wc-vendors' ); ?></a>
						</td>
					<?php endif; ?>

				</tr>

			<?php endforeach; ?>

			<tr>
				<td><strong><?php _e( 'Totals', 'wc-vendors' ); ?></strong></td>
				<td><?php echo $vendor_summary['total_qty']; ?></td>
				<td><?php echo wc_price( $vendor_summary['total_cost'] ); ?></td>
				<td></td>

				<?php if ( $can_view_orders ) : ?>
					<td></td>
				<?php endif; ?>

			</tr>

		<?php else : ?>

			<tr>
				<td colspan="4"
				    style="text-align:center;"><?php _e( 'You have no sales during this period.', 'wc-vendors' ); ?></td>
			</tr>

		<?php endif; ?>


	<?php else : ?>

		<tr>
			<td colspan="4"
			    style="text-align:center;"><?php _e( 'You haven\'t made any sales yet.', 'wc-vendors' ); ?></td>
		</tr>

	<?php endif; ?>

	</tbody>
</table>
