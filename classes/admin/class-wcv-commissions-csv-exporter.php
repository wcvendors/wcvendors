<?php
/**
 * Handles commission CSV export.
 *
 * @version  1.9.14
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Include dependencies.
 */
if ( ! class_exists( 'WC_CSV_Exporter', false ) ) {
	include_once WC_ABSPATH . 'includes/export/abstract-wc-csv-exporter.php';
}

/**
 * WCV_Commissions_CSV_Export Class.
 */
class WCV_Commissions_CSV_Export extends WC_CSV_Exporter {

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->column_names = $this->get_default_column_names();
	}


	/**
	 * Return an array of columns to export.
	 *
	 * @since 1.9.14
	 * @return array
	 */
	public function get_default_column_names() {

		return apply_filters(
			'wcv_commissions_export_columns', array(
				'order_id'       	=> __( 'Order ID', 'wc-vendors' ),
				'vendor_id'     	=> sprintf( __( '%s', 'wc-vendors' ), wcv_get_vendor_name() ),
				'product_id'     	=> __( 'Product', 'wc-vendors' ),
				'qty'				=> __( 'QTY', 'wc-vendors' ),
				'total_due'      	=> __( 'Commission', 'wc-vendors' ),
				'total_shipping' 	=> __( 'Shipping', 'wc-vendors' ),
				'tax'            	=> __( 'Tax', 'wc-vendors' ),
				'totals'         	=> __( 'Total', 'wc-vendors' ),
				'status'         	=> __( 'Status', 'wc-vendors' ),
				'time'           	=> __( 'Date', 'wc-vendors' ),
			)
		);
	}

	/**
	 * Prepare data for export.
	 *
	 * @since 1.9.14
	 */
	public function prepare_data_to_export() {

		global $wpdb;

		$columns = $this->get_column_names();

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$order      = ( ! empty( $_REQUEST['order'] ) && $_REQUEST['order'] == 'asc' ) ? 'ASC' : 'DESC';
		$orderby    = ! empty( $_REQUEST['orderby']    ) ? esc_attr( $_REQUEST['orderby'] )    : 'time';
		$com_status = ! empty( $_REQUEST['com_status'] ) ? esc_attr( $_REQUEST['com_status'] ) : '';
		$vendor_id  = ! empty( $_REQUEST['vendor_id']  ) ? esc_attr( $_REQUEST['vendor_id'] )  : '';
		$status_sql = '';
		$time_sql   = '';

		/**
		 * Get items
		 */
		$sql = "SELECT COUNT(id) FROM {$wpdb->prefix}pv_commission";

		if ( ! empty( $_GET['m'] ) ) {

			$year  = substr( $_GET['m'], 0, 4 );
			$month = substr( $_GET['m'], 4, 2 );

			$time_sql = "
				WHERE MONTH(`time`) = '$month'
				AND YEAR(`time`) = '$year'
			";

			$sql .= $time_sql;
		}

		if ( ! empty( $_GET['com_status'] ) ) {

			if ( $time_sql == '' ) {
				$status_sql = "
				WHERE status = '$com_status'
				";
			} else {
				$status_sql = "
				AND status = '$com_status'
				";
			}

			$sql .= $status_sql;
		}

		if ( ! empty( $_GET['vendor_id'] ) ) {

			if ( $time_sql == '' && $status_sql == '' ) {
				$vendor_sql = "
				WHERE vendor_id = '$vendor_id'
				";
			} else {
				$vendor_sql = "
				AND vendor_id = '$vendor_id'
				";
			}

			$sql .= $vendor_sql;
		}

		$max = $wpdb->get_var( $sql );

		$sql
			= "
			SELECT * FROM {$wpdb->prefix}pv_commission
		";

		if ( ! empty( $_GET['m'] ) ) {
			$sql .= $time_sql;
		}

		if ( ! empty( $_GET['com_status'] ) ) {
			$sql .= $status_sql;
		}

		if ( ! empty( $_GET['vendor_id'] ) ) {
			$sql .= $vendor_sql;
		}

		// $offset = ( $current_page - 1 ) * $per_page;
		$sql .= "
			ORDER BY `{$orderby}` {$order}
		";

		$commissions = $wpdb->get_results( $sql );

		$this->total_rows = count( $commissions );
		$this->row_data   = array();

		foreach ( $commissions as $commission ) {

			$row = array();

			foreach ( $columns as $column_id => $column_name ) {

				switch ( $column_id ) {
					case 'vendor_id':
						$value = WCV_Vendors::get_vendor_shop_name( $commission->vendor_id );
						break;
					case 'totals':
						$totals = ( wc_tax_enabled() ) ? $commission->total_due + $commission->total_shipping + $commission->tax : $commission->total_due + $commission->total_shipping;
						$value  = wc_format_localized_price( $totals );
						break;
					case 'order_id':
						$order = wc_get_order( $commission->order_id );
						$value = ( $order )? $order->get_order_number() :  $commission->order_id;
						break;
					case 'product_id':
						$parent          = get_post_ancestors( $commission->product_id );
						$product_id      = $parent ? $parent[0] : $commission->product_id;
						$wcv_total_sales = get_post_meta( $product_id, 'total_sales', true );
						if ( ! get_post_status( $product_id ) ) {
							$product_id = WCV_Vendors::find_parent_id_from_order( $commission->order_id, $product_id );
						}
						$value = get_the_title( $product_id ) . '(' . $wcv_total_sales . ')';
						break;
					default:
						$value = $commission->$column_id;
				}

				$row[ $column_id ] = $value;
			}

			$this->row_data[] = apply_filters( 'wcv_commissions_export_row_data', $row, $commission );
		}

	}
}
