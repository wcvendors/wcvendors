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

		return apply_filters(
			'wcv_commissions_sum_export_columns', array(
				'vendor_id'           => sprintf( __( '%s', 'wc-vendors' ), wcv_get_vendor_name() ),
				'total_due'           => __( 'Total', 'wc-vendors' ),
				'paypal_email'        => __( 'PayPal Email', 'wc-vendors' ),
				'bank_account_name'   => __( 'Bank Account Name', 'wc-vendors' ),
				'bank_account_number' => __( 'Bank Account Number', 'wc-vendors' ),
				'bank_name'           => __( 'Bank Name', 'wc-vendors' ),
				'bank_routing'        => __( 'Routing Number', 'wc-vendors' ),
				'bank_iban'           => __( 'IBAN', 'wc-vendors' ),
				'bank_swift'          => __( 'BIC/SWIFT', 'wc-vendors' ),
				'status'              => __( 'Commission Status', 'wc-vendors' ),
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

		$sum_totals       = WCV_Commission::get_sum_vendor_totals();
		$this->total_rows = count( $sum_totals );
		$this->row_data   = array();

		foreach ( $sum_totals as $status => $totals ) {
			$row = array();
			foreach ( $totals as $vendor_id => $total ) {

				foreach ( $columns as $column_id => $column_name ) {

					switch ( $column_id ) {
						case 'vendor_id':
							$value = WCV_Vendors::get_vendor_shop_name( $vendor_id );
							break;
						case 'paypal_email':
							$value = get_user_meta( $vendor_id, 'pv_paypal', 'true' );
							break;
						case 'bank_account_name':
							$value = get_user_meta( $vendor_id, 'wcv_bank_account_name', 'true' );
							break;
						case 'bank_account_number':
							$value = get_user_meta( $vendor_id, 'wcv_bank_account_number', 'true' );
							break;
						case 'bank_name':
							$value = get_user_meta( $vendor_id, 'wcv_bank_name', 'true' );
							break;
						case 'bank_routing':
							$value = get_user_meta( $vendor_id, 'wcv_bank_routing_number', 'true' );
							break;
						case 'bank_iban':
							$value = get_user_meta( $vendor_id, 'wcv_bank_iban', 'true' );
							break;
						case 'bank_swift':
							$value = get_user_meta( $vendor_id, 'wcv_bank_bic_swift', 'true' );
							break;
						case 'total_due':
							$value = wc_format_localized_price( $total );
							break;
						default:
							$value = $status;
							break;
					}

					$row[ $column_id ] = $value;
				}

				$this->row_data[] = apply_filters( 'wcv_sum_commissions_export_row_data', $row, $vendor_id, $total, $status );
			}
		}

	}
}
