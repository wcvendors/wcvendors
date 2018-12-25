<?php
/**
 * Orders Template
 *
 * This template can be overridden by copying it to yourtheme/wc-vendors/dashboard/orders.php
 *
 * @author        Jamie Madden, WC Vendors
 * @package       WCVendors/Templates/dashboard/
 * @version       2.0.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<script type="text/javascript">
	jQuery(function () {
		jQuery('a.view-items').on('click', function (e) {
			e.preventDefault();
			var id = jQuery(this).closest('tr').data('order-id');

			if (jQuery(this).text() == "<?php _e( 'Hide items', 'wc-vendors' ); ?>") {
				jQuery(this).text("<?php _e( 'View items', 'wc-vendors' ); ?>");
			} else {
				jQuery(this).text("<?php _e( 'Hide items', 'wc-vendors' ); ?>");
			}

			jQuery("#view-items-" + id).fadeToggle();
		});

		jQuery('a.view-order-tracking').on('click', function (e) {
			e.preventDefault();
			var id = jQuery(this).closest('tr').data('order-id');
			jQuery("#view-tracking-" + id).fadeToggle();
		});
	});
</script>

<h2><?php _e( 'Orders', 'wc-vendors' ); ?></h2>


<?php
if ( function_exists( 'wc_print_notices' ) ) {
	wc_print_notices();
}
?>

<table class="table table-condensed table-vendor-sales-report">
	<thead>
	<tr>
		<th class="product-header"><?php _e( 'Order', 'wc-vendors' ); ?></th>
		<?php if ( $can_view_address ) : ?>
			<th class="quantity-header"><?php _e( 'Shipping', 'wc-vendors' ); ?></th>
		<?php endif; ?>
		<th class="commission-header"><?php _e( 'Total', 'wc-vendors' ); ?></th>
		<th class="rate-header"><?php _e( 'Date', 'wc-vendors' ); ?></th>
		<th class="rate-header"><?php _e( 'Links', 'wc-vendors' ); ?></th>
	</thead>
	<tbody>

	<?php
	if ( ! empty( $order_summary ) ) :
		$totals = 0;
		$user_id = get_current_user_id();
		?>

		<?php
		foreach ( $order_summary as $order ) :

			$order = wc_get_order( $order->order_id );
			$order_id = $order->get_id();
			$valid_items = WCV_Queries::get_products_for_order( $order_id );
			$valid = array();
			$needs_shipping = false;

			$items = $order->get_items();

			foreach ( $items as $key => $value ) {
				if ( in_array( $value['variation_id'], $valid_items ) || in_array( $value['product_id'], $valid_items ) ) {
					$valid[] = $value;
				}
				// See if product needs shipping
				$product        = new WC_Product( $value['product_id'] );
				$needs_shipping = ( ! $product->needs_shipping() || $product->is_downloadable( 'yes' ) ) ? false : true;

			}

			$shippers = (array) get_post_meta( $order_id, 'wc_pv_shipped', true );
			$shipped  = in_array( $user_id, $shippers );

			$order_date = $order->get_date_created();

			?>

			<tr id="order-<?php echo $order_id; ?>" data-order-id="<?php echo $order_id; ?>">
				<td><?php echo $order->get_order_number(); ?></td>
				<?php if ( $can_view_address ) : ?>
					<td><?php echo apply_filters( 'wcvendors_dashboard_google_maps_link', '<a target="_blank" href="' . esc_url( 'http://maps.google.com/maps?&q=' . urlencode( esc_html( preg_replace( '#<br\s*/?>#i', ', ', $order->get_formatted_shipping_address() ) ) ) . '&z=16' ) . '">' . esc_html( preg_replace( '#<br\s*/?>#i', ', ', $order->get_formatted_shipping_address() ) ) . '</a>' ); ?></td>
				<?php endif; ?>
				<td>
					<?php
					$sum    = WCV_Queries::sum_for_orders( array( $order_id ), array( 'vendor_id' => get_current_user_id() ) );
					$total  = $sum[0]->line_total;
					$totals += $total;
					echo wc_price( $total );
					?>
				</td>
				<td><?php echo date_i18n( wc_date_format(), strtotime( $order_date ) ); ?></td>
				<td>
					<?php
					$order_actions = array(
						'view' => array(
							'class'   => 'view-items',
							'content' => __( 'View items', 'wc-vendors' ),
						),
					);
					if ( $needs_shipping ) {
						$order_actions['shipped'] = array(
							'class'   => 'mark-shipped',
							'content' => __( 'Mark shipped', 'wc-vendors' ),
							'url'     => '?wc_pv_mark_shipped=' . $order_id,
						);
					}
					if ( $shipped ) {
						$order_actions['shipped'] = array(
							'class'   => 'mark-shipped',
							'content' => __( 'Shipped', 'wc-vendors' ),
							'url'     => '#',
						);
					}

					if ( $providers && $needs_shipping && class_exists( 'WC_Shipment_Tracking' ) ) {
						$order_actions['tracking'] = array(
							'class'   => 'view-order-tracking',
							'content' => __( 'Tracking', 'wc-vendors' ),
						);
					}

					$order_actions = apply_filters( 'wcvendors_order_actions', $order_actions, $order );

					if ( $order_actions ) {
						$output = array();
						foreach ( $order_actions as $key => $data ) {
							$output[] = sprintf(
								'<a href="%s" id="%s" class="%s">%s</a>',
								( isset( $data['url'] ) ) ? $data['url'] : '#',
								( isset( $data['id'] ) ) ? $data['id'] : $key . '-' . $order_id,
								( isset( $data['class'] ) ) ? $data['class'] : '',
								$data['content']
							);
						}
						echo implode( ' | ', $output );
					}
					?>
				</td>
			</tr>

			<tr id="view-items-<?php echo $order_id; ?>" style="display:none;">
				<td colspan="5">
					<?php
					$product_id = '';
					foreach ( $valid as $key => $item ) :

						// Get variation data if there is any.
						$variation_detail = ! empty( $item['variation_id'] ) ? WCV_Orders::get_variation_data( $item['variation_id'] ) : '';

						?>
						<?php echo $item['qty'] . 'x ' . $item['name']; ?>
						<?php
						if ( ! empty( $variation_detail ) ) {
							echo '<br />' . $variation_detail;
						}
						?>


					<?php endforeach; ?>

				</td>
			</tr>

			<?php if ( class_exists( 'WC_Shipment_Tracking' ) ) : ?>

			<?php if ( is_array( $providers ) ) : ?>
				<tr id="view-tracking-<?php echo $order_id; ?>" style="display:none;">
					<td colspan="5">
						<div class="order-tracking">
							<?php
							wc_get_template(
								'shipping-form.php', array(
								'order_id'   => $order_id,
								'product_id' => $product_id,
								'providers'  => $providers,
							), 'wc-vendors/orders/shipping/', wcv_plugin_dir . 'templates/orders/shipping/'
							);
							?>
						</div>

					</td>
				</tr>
			<?php endif; ?>

		<?php endif; ?>

		<?php endforeach; ?>

		<tr>
			<td><b>Total:</b></td>
			<td colspan="4"><?php echo wc_price( $totals ); ?></td>
		</tr>

	<?php else : ?>

		<tr>
			<td colspan="4"
			    style="text-align:center;"><?php _e( 'You have no orders during this period.', 'wc-vendors' ); ?></td>
		</tr>

	<?php endif; ?>

	</tbody>
</table>
