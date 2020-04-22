<?php
/**
 * WCV_Admin_Reports class.
 *
 * Shows reports related to software in the woocommerce backend
 *
 * @author Matt Gates <http://mgates.me>
 * @package
 */


class WCV_Admin_Reports {


	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 *
	 * @param bool $debug (optional) (default: false)
	 */
	function __construct( $debug = false ) {

		add_filter( 'woocommerce_admin_reports', array( $this, 'reports_tab' ) );
	}

	/**
	 * reports_tab function.
	 *
	 * @access public
	 *
	 * @param unknown $reports
	 *
	 * @return void
	 */
	function reports_tab( $reports ) {

		$reports['vendors'] = array(
			'title'  => __( 'WC Vendors', 'wc-vendors' ),
			'charts' => array(
				array(
					'title'       => __( 'Overview', 'wc-vendors' ),
					'description' => '',
					'hide_title'  => true,
					'function'    => array( $this, 'sales' ),
				),
				array(
					'title'       => sprintf( __( 'Commission by %s', 'wc-vendors' ), wcv_get_vendor_name( true, false ) ),
					'description' => '',
					'hide_title'  => true,
					'function'    => array( $this, 'commission' ),
				),
				array(
					'title'       => __( 'Commission by product', 'wc-vendors' ),
					'description' => '',
					'hide_title'  => true,
					'function'    => array( $this, 'commission' ),
				),
				array(
					'title'       => __( 'Commission Totals', 'wc-vendors' ),
					'description' => __( 'Commission totals for all vendors includes shipping and taxes. By default no date range is used and all due commissions are returned. Use the date range to filter.', 'wc-vendors' ),
					'hide_title'  => true,
					'function'    => array( $this, 'commission_totals' ),
				),
			),
		);

		return apply_filters( 'wcvendors_admin_reports_tab', $reports );
	}


	/**
	 *
	 */
	function sales() {

		global $start_date, $end_date, $woocommerce, $wpdb;

		$commission_status_labels = WCV_Commission::commission_status();

		$start_date = ! empty( $_POST['start_date'] ) ? $_POST['start_date'] : strtotime( gmdate( 'Ymd', strtotime( gmdate( 'Ym', current_time( 'timestamp' ) ) . '01' ) ) );
		$end_date   = ! empty( $_POST['end_date'] ) ? $_POST['end_date'] : strtotime( gmdate( 'Ymd', current_time( 'timestamp' ) ) );

		if ( ! empty( $_POST['start_date'] ) ) {
			$start_date = strtotime( $_POST['start_date'] );
		}

		if ( ! empty( $_POST['end_date'] ) ) {
			$end_date = strtotime( $_POST['end_date'] );
		}

		$after  = gmdate( 'Y-m-d', $start_date );
		$before = gmdate( 'Y-m-d', strtotime( '+1 day', $end_date ) );

		$commission_due = $wpdb->get_var(
			"
			SELECT SUM(total_due + total_shipping + tax) FROM {$wpdb->prefix}pv_commission WHERE status = 'due'
			AND     time >= '" . $after . "'
			AND     time <= '" . $before . "'
		"
		);

		$reversed = $wpdb->get_var(
			"
			SELECT SUM(total_due + total_shipping + tax) FROM {$wpdb->prefix}pv_commission WHERE status = 'reversed'
			AND     time >= '" . $after . "'
			AND     time <= '" . $before . "'
		"
		);

		$paid = $wpdb->get_var(
			"
			SELECT SUM(total_due + total_shipping + tax) FROM {$wpdb->prefix}pv_commission WHERE status = 'paid'
			AND     time >= '" . $after . "'
			AND     time <= '" . $before . "'
		"
		);

		?>

		<form method="post" action="">
			<p><label for="from"><?php _e( 'From:', 'wc-vendors' ); ?></label>
				<input type="text" size="9" placeholder="yyyy-mm-dd"
				       value="<?php echo esc_attr( gmdate( 'Y-m-d', $start_date ) ); ?>" name="start_date"
				       class="range_datepicker from" id="from"/>
				<label for="to"><?php _e( 'To:', 'wc-vendors' ); ?></label>
				<input type="text" size="9" placeholder="yyyy-mm-dd"
				       value="<?php echo esc_attr( gmdate( 'Y-m-d', $end_date ) ); ?>" name="end_date"
				       class="range_datepicker to" id="to"/>
				<input type="submit" class="button" value="<?php _e( 'Show', 'wc-vendors' ); ?>"/></p>
		</form>

		<div id="poststuff" class="woocommerce-reports-wrap">
			<div class="woocommerce-reports-sidebar">
				<div class="postbox">
					<h3><span><?php _e( 'Total paid in range', 'wc-vendors' ); ?></span></h3>

					<div class="inside">
						<p class="stat">
							<?php
							if ( $paid > 0 ) {
								echo wc_price( $paid );
							} else {
								_e( 'n/a', 'wc-vendors' );
							}
							?>
						</p>
					</div>
				</div>
				<div class="postbox">
					<h3><span><?php _e( 'Total due in range', 'wc-vendors' ); ?></span></h3>

					<div class="inside">
						<p class="stat">
							<?php
							if ( $commission_due > 0 ) {
								echo wc_price( $commission_due );
							} else {
								_e( 'n/a', 'wc-vendors' );
							}
							?>
						</p>
					</div>
				</div>
				<div class="postbox">
					<h3><span><?php _e( 'Total reversed in range', 'wc-vendors' ); ?></span></h3>

					<div class="inside">
						<p class="stat">
							<?php
							if ( $reversed > 0 ) {
								echo wc_price( $reversed );
							} else {
								_e( 'n/a', 'wc-vendors' );
							}
							?>
						</p>
					</div>
				</div>
			</div>

			<div class="woocommerce-reports-main">
				<div class="postbox">
					<h3><span><?php _e( 'Recent Commission', 'wc-vendors' ); ?></span></h3>

					<div>
						<?php
						$commission = $wpdb->get_results(
							"
								SELECT * FROM {$wpdb->prefix}pv_commission
								WHERE     time >= '" . $after . "'
								AND     time <= '" . $before . "'
								ORDER BY time DESC
							"
						);

						if ( sizeof( $commission ) > 0 ) {

							?>
							<div class="woocommerce_order_items_wrapper">
								<table id="commission-table" class="woocommerce_order_items" cellspacing="0">
									<thead>
									<tr>
										<th><?php _e( 'Order', 'wc-vendors' ); ?></th>
										<th><?php _e( 'Product', 'wc-vendors' ); ?></th>
										<th><?php printf( __( '%s', 'wc-vendors' ), wcv_get_vendor_name() ); ?></th>
										<th><?php _e( 'Total', 'wc-vendors' ); ?></th>
										<th><?php _e( 'Date &amp; Time', 'wc-vendors' ); ?></th>
										<th><?php _e( 'Status', 'wc-vendors' ); ?></th>
									</tr>
									</thead>
									<tbody>
									<?php
									$i = 1;
									foreach ( $commission as $row ) :
										$i ++
										?>
										<tr
											<?php
											if ( $i % 2 == 1 ) {
												echo ' class="alternate"';
											}
											?>
										>
											<td>
												<?php
												if ( $row->order_id ) :
													?>
													<a
															href="<?php echo admin_url( 'post.php?post=' . $row->order_id . '&action=edit' ); ?>"><?php echo $row->order_id; ?></a>
												<?php
												else :
													_e( 'N/A', 'wc-vendors' );
												endif;
												?>
											</td>
											<td><?php echo get_the_title( $row->product_id ); ?></td>
											<td><?php echo WCV_Vendors::get_vendor_shop_name( $row->vendor_id ); ?></td>
											<td><?php echo wc_price( $row->total_due + $row->total_shipping + $row->tax ); ?></td>
											<td><?php echo date_i18n( __( 'D j M Y \a\t h:ia', 'wc-vendors' ), strtotime( $row->time ) ); ?></td>
											<td><?php echo $commission_status_labels[ $row->status ]; ?></td>
										</tr>
									<?php endforeach; ?>
									</tbody>
								</table>
							</div>
							<?php
						} else {
							?>
							<p><?php _e( 'No commission yet', 'wc-vendors' ); ?></p>
							<?php
						}
						?>
					</div>
				</div>
			</div>
		</div>
		<?php

	}


	/**
	 *
	 */
	function commission() {

		global $start_date, $end_date, $woocommerce, $wpdb;

		$latest_woo = version_compare( $woocommerce->version, '2.3', '>' );

		$first_year   = $wpdb->get_var( "SELECT time FROM {$wpdb->prefix}pv_commission ORDER BY time ASC LIMIT 1;" );
		$first_year   = $first_year ? gmdate( 'Y', strtotime( $first_year ) ) : gmdate( 'Y' );
		$current_year = isset( $_POST['show_year'] ) ? $_POST['show_year'] : gmdate( 'Y', current_time( 'mysql' ) );
		$start_date   = strtotime( $current_year . '0101' );

		$vendors         = get_users( array( 'role' => 'vendor' ) );
		$vendors         = apply_filters( 'pv_commission_vendors_list', $vendors );
		$selected_vendor = ! empty( $_POST['show_vendor'] ) ? (int) $_POST['show_vendor'] : false;
		$products        = ! empty( $_POST['product_ids'] ) ? (array) $_POST['product_ids'] : array();

		?>

		<form method="post" action="" class="report_filters">
			<label for="show_year"><?php _e( 'Show:', 'wc-vendors' ); ?></label>
			<select name="show_year" id="show_year">
				<?php
				for ( $i = $first_year; $i <= gmdate( 'Y' ); $i ++ ) {
					printf( '<option value="%s" %s>%s</option>', $i, selected( $current_year, $i, false ), $i );
				}
				?>
			</select>
			<?php
			if ( $_GET['report'] == 2 ) {
			if ( $latest_woo ) {
				?>
				<select id="product_ids" name="product_ids[]" class="wc-product-search ajax_chosen_select_products"
				        multiple="multiple"
				        data-placeholder="<?php _e( 'Type in a product name to start searching...', 'wc-vendors' ); ?>"
				        style="width: 400px;"></select>
			<?php } else { ?>
				<select id="product_ids" name="product_ids[]" class="ajax_chosen_select_products" multiple="multiple"
				        data-placeholder="<?php _e( 'Type in a product name to start searching...', 'wc-vendors' ); ?>"
				        style="width: 400px;"></select>
				<script type="text/javascript">
					jQuery(function () {

						// Ajax Chosen Product Selectors
						jQuery("select.ajax_chosen_select_products").ajaxChosen({
							method: 'GET',
							url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
							dataType: 'json',
							afterTypeDelay: 100,
							data: {
								action: 'woocommerce_json_search_products',
								security: '<?php echo wp_create_nonce( 'search-products' ); ?>'
							}
						}, function (data) {

							var terms = {};

							jQuery.each(data, function (i, val) {
								terms[i] = val;
							});

							return terms;
						});

					});
				</script>

			<?php
			}
			} else {
			?>
				<select class="chosen_select" id="show_vendor" name="show_vendor" style="width: 300px;"
				        data-placeholder="<?php echo sprintf( __( 'Select a %s&hellip;', 'wc-vendors' ), wcv_get_vendor_name() ); ?>">
					<option></option>
					<?php
					foreach ( $vendors as $key => $vendor ) {
						printf( '<option value="%s" %s>%s</option>', $vendor->ID, selected( $selected_vendor, $vendor->ID, false ), $vendor->display_name );
					}
					?>
				</select>
			<?php } ?>
			<input type="submit" class="button" value="<?php _e( 'Show', 'wc-vendors' ); ?>"/>
		</form>

		<?php

		if ( ! empty( $selected_vendor ) || ! empty( $products ) ) {

			foreach ( $products as $key => $product_id ) {
				$_product = wc_get_product( $product_id );
				$childs   = $_product->get_children();
				$products = array_merge( $childs, $products );
			}

			$commissions = array();
			$filter      = ! empty( $selected_vendor ) ? ( ' WHERE vendor_id = ' . $selected_vendor ) : ( ' WHERE product_id IN ( ' . implode( ', ', $products ) . ' )' );

			$sql
				= "SELECT
				SUM(total_due + total_shipping + tax) as total,
				SUM(total_due) as commission,
				SUM(total_shipping) as shipping,
				SUM(tax) as tax
				FROM {$wpdb->prefix}pv_commission
			";

			$paid_sql     = "SELECT SUM(total_due + total_shipping + tax) FROM {$wpdb->prefix}pv_commission " . $filter . " AND status = 'paid'";
			$reversed_sql = "SELECT SUM(total_due + total_shipping + tax) FROM {$wpdb->prefix}pv_commission" . $filter . " AND status = 'reversed'";
			$date_sql     = " AND date_format(`time`,'%%Y%%m') = %d";

			for ( $count = 0; $count < 12; $count ++ ) {
				$time = strtotime( gmdate( 'Ym', strtotime( '+ ' . $count . ' MONTH', $start_date ) ) . '01' );
				if ( $time > current_time( 'timestamp' ) ) {
					continue;
				}

				$month = gmdate( 'Ym', strtotime( gmdate( 'Ym', strtotime( '+ ' . $count . ' MONTH', $start_date ) ) . '01' ) );

				$fetch_results = $wpdb->prepare( $sql . $filter . $date_sql, $month );

				$results = $wpdb->get_results( $fetch_results );
				if ( ! empty( $results[0] ) ) {
					extract( get_object_vars( $results[0] ) );
				}

				$paid     = $wpdb->get_var( $wpdb->prepare( $paid_sql . $date_sql, $month ) );
				$reversed = $wpdb->get_var( $wpdb->prepare( $reversed_sql . $date_sql, $month ) );

				$commissions[ gmdate( 'M', strtotime( $month . '01' ) ) ] = array(
					'commission' => $commission,
					'tax'        => $tax,
					'shipping'   => $shipping,
					'reversed'   => $reversed,
					'paid'       => $paid,
					'total'      => $total - $reversed - $paid,
				);

			}

			?>

			<div class="woocommerce-reports-main">
				<table class="widefat">
					<thead>
					<tr>
						<th><?php _e( 'Month', 'wc-vendors' ); ?></th>
						<th class="total_row"><?php _e( 'Commission Totals', 'wc-vendors' ); ?></th>
						<th class="total_row"><?php _e( 'Tax', 'wc-vendors' ); ?></th>
						<th class="total_row"><?php _e( 'Shipping', 'wc-vendors' ); ?></th>
						<th class="total_row"><?php _e( 'Reversed', 'wc-vendors' ); ?></th>
						<th class="total_row"><?php _e( 'Paid', 'wc-vendors' ); ?></th>
						<th class="total_row"><?php _e( 'Due', 'wc-vendors' ); ?></th>
					</tr>
					</thead>
					<tfoot>
					<tr>
						<?php
						$total = array(
							'commission' => 0,
							'tax'        => 0,
							'shipping'   => 0,
							'reversed'   => 0,
							'paid'       => 0,
							'total'      => 0,
						);

						foreach ( $commissions as $month => $commission ) {
							$total['commission'] += $commission['commission'];
							$total['tax']        += $commission['tax'];
							$total['shipping']   += $commission['shipping'];
							$total['reversed']   += $commission['reversed'];
							$total['paid']       += $commission['paid'];
							$total['total']      += $commission['total'];
						}

						echo '<td>' . __( 'Total', 'wc-vendors' ) . '</td>';

						foreach ( $total as $value ) {
							echo '<td class="total_row">' . wc_price( $value ) . '</td>';
						}
						?>
					</tr>
					</tfoot>
					<tbody>
					<?php
					foreach ( $commissions as $month => $commission ) {
						$alt = ( isset( $alt ) && $alt == 'alt' ) ? '' : 'alt';

						echo '<tr class="' . $alt . '"><td>' . $month . '</td>';

						foreach ( $commission as $value ) {
							echo '<td class="total_row">' . wc_price( $value ) . '</td>';
						}

						echo '</tr>';
					}
					?>
					</tbody>
				</table>
			</div>

		<?php } ?>
		<?php

	}


	/**
	 *  Commission Totals for vendors reports
	 *
	 * @since    1.8.4
	 */
	function commission_totals() {

		global $wpdb;

		$total_start_date  = ! empty( $_POST['total_start_date'] ) ? $_POST['total_start_date'] : strtotime( gmdate( 'Ymd', strtotime( gmdate( 'Ym', current_time( 'mysql' ) ) . '01' ) ) );
		$total_end_date    = ! empty( $_POST['total_end_date'] ) ? $_POST['total_end_date'] : strtotime( gmdate( 'Ymd', current_time( 'mysql' ) ) );
		$commission_status = ! empty( $_POST['commission_status'] ) ? $_POST['commission_status'] : 'due';
		$date_sql          = ( ! empty( $_POST['total_start_date'] ) && ! empty( $_POST['total_end_date'] ) ) ? " time BETWEEN '$total_start_date 00:00:00' AND '$total_end_date 23:59:59' AND" : '';

		$status_sql = " status='$commission_status'";

		$sql = "SELECT vendor_id, total_due, total_shipping, tax, status FROM {$wpdb->prefix}pv_commission WHERE";

		$commissions = $wpdb->get_results( $sql . $date_sql . $status_sql );

		if ( ! empty( $_POST['total_start_date'] ) ) {
			$total_start_date = strtotime( $_POST['total_start_date'] );
		}

		if ( ! empty( $_POST['total_end_date'] ) ) {
			$total_end_date = strtotime( $_POST['total_end_date'] );
		}

		$totals = $this->calculate_totals( $commissions );

		?>
		<form method="post" action="">
			<p><label for="from"><?php _e( 'From:', 'wc-vendors' ); ?></label>
				<input type="text" size="9" placeholder="yyyy-mm-dd"
				       value="<?php echo esc_attr( gmdate( 'Y-m-d', $total_start_date ) ); ?>" name="total_start_date"
				       class="range_datepicker from" id="from"/>
				<label for="to"><?php _e( 'To:', 'wc-vendors' ); ?></label>
				<input type="text" size="9" placeholder="yyyy-mm-dd"
				       value="<?php echo esc_attr( gmdate( 'Y-m-d', $total_end_date ) ); ?>" name="total_end_date"
				       class="range_datepicker to" id="to"/>

				<select name="commission_status">
					<option value="due"><?php _e( 'Due', 'wc-vendors' ); ?></option>
					<option value="paid"><?php _e( 'Paid', 'wc-vendors' ); ?></option>
					<option value="reversed"><?php _e( 'Reversed', 'wc-vendors' ); ?></option>
				</select>

				<input type="submit" class="button" value="<?php _e( 'Show', 'wc-vendors' ); ?>"/>

				<?php do_action( 'wcvendors_after_commission_reports', $commissions ); ?>
			</p>
		</form>

		<div class="woocommerce-reports-main">
		<table class="widefat">
			<thead>
			<tr>
				<th class="total_row"><?php printf( __( '%s', 'wc-vendors' ), wcv_get_vendor_name() ); ?></th>
				<th class="total_row"><?php _e( 'Tax Total', 'wc-vendors' ); ?></th>
				<th class="total_row"><?php _e( 'Shipping Total', 'wc-vendors' ); ?></th>
				<th class="total_row"><?php _e( 'Status', 'wc-vendors' ); ?></th>
				<th class="total_row"><?php _e( 'Commission Total', 'wc-vendors' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php

			if ( ! empty( $commissions ) ) {

				foreach ( $totals as $totals ) {

					echo '<tr>';
					echo '<td>' . $totals['user_login'] . '</td>';
					echo '<td>' . wc_price( $totals['tax'] ) . '</td>';
					echo '<td>' . wc_price( $totals['total_shipping'] ) . '</td>';
					echo '<td>' . $totals['status'] . '</td>';
					echo '<td>' . wc_price( $totals['total_due'] ) . '</td>';
					echo '</tr>';

				}
			} else {
				echo '<tr>';
				echo '<td colspan="5">' . __( 'No commissions found.', 'wc-vendors' ) . '</td>';
				echo '</tr>';

			}
			?>
			</tbody>
		</table>

		<?php

	} // commission_totals()

	/**
	 *   Calculate the totals of the commissions return an array with vendor id as the key with the totals
	 *
	 * @param    array $commissions total commissions array
	 *
	 * @return   array $totals    calculated totals
	 */
	function calculate_totals( $commissions ) {

		$totals = array();

		$vendors      = get_users(
			array(
				'role'   => 'vendor',
				'fields' => array( 'ID', 'user_login' ),
			)
		);
		$vendor_names = wp_list_pluck( $vendors, 'user_login', 'ID' );

		foreach ( $commissions as $commission ) {

			if ( array_key_exists( $commission->vendor_id, $totals ) ) {

				$totals[ $commission->vendor_id ]['total_due']      += $commission->total_due + $commission->tax + $commission->total_shipping;
				$totals[ $commission->vendor_id ]['tax']            += $commission->tax;
				$totals[ $commission->vendor_id ]['total_shipping'] += $commission->total_shipping;

			} else {

				if ( array_key_exists( $commission->vendor_id, $vendor_names ) ) {

					$temp_array = array(
						'user_login'     => $vendor_names[ $commission->vendor_id ],
						'total_due'      => $commission->total_due + $commission->tax + $commission->total_shipping,
						'tax'            => $commission->tax,
						'total_shipping' => $commission->total_shipping,
						'status'         => $commission->status,
					);

					$totals[ $commission->vendor_id ] = $temp_array;

				}
			}
		}

		usort(
			$totals, function ( $a, $b ) {

			return strcmp( strtolower( $a['user_login'] ), strtolower( $b['user_login'] ) );
		}
		);

		return $totals;

	} // calculate_totals()

}
