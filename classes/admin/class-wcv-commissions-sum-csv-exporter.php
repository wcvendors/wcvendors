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
class WCV_Commissions_Sum_CSV_Export extends WC_CSV_Exporter {

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

		return apply_filters( 'wcv_commissions_sum_export_columns', array(
			'vendor_id' 	    => __( 'Vendor', 'wc-vendors' ),
			'total_due'  		=> __( 'Total', 'wc-vendors' ),
			'status'     		=> __( 'Commission Status', 'wc-vendors' ),
		) );
	}

	/**
	 * Prepare data for export.
	 *
	 * @since 1.9.14
	 */
	public function prepare_data_to_export() {

		global $wpdb;

		$columns  = $this->get_column_names();

		if ( ! current_user_can( 'manage_options' ) ) return;

		$sum_totals = WCV_Commission::get_sum_vendor_totals();
		$this->total_rows = count( $sum_totals );
		$this->row_data   = array();

		foreach ( $sum_totals as $status => $totals ) {
			$row = array();
			foreach ( $totals as $vendor_id => $total ) {

				foreach ( $columns as $column_id => $column_name ) {
					if ( $column_id == 'vendor_id' ) {
	 					$value = WCV_Vendors::get_vendor_shop_name( $vendor_id );
					} elseif ( $column_id == 'total_due' ){
					 	$value 	= wc_format_localized_price( $total );
					} else {
						$value = $status;
					}

					$row[ $column_id ] = $value;
				}

				$this->row_data[] = apply_filters( 'wcv_sum_commissions_export_row_data', $row, $vendor_id, $total, $status );
			}
		}

	}
}
