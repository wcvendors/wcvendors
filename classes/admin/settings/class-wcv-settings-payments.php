<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The display settings class
 *
 * @author      Jamie Madden, WC Vendors
 * @category    Settings
 * @package     WCVendors/Admin/Settings
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WCVendors_Settings_Payments', false ) ) :

	/**
	 * WC_Admin_Settings_General.
	 */
	class WCVendors_Settings_Payments extends WCVendors_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {

			$paypalap_settings = get_option( 'woocommerce_paypalap_settings', false );
			if ( $paypalap_settings && array_key_exists('username_live', $paypalap_settings ) && $paypalap_settings[ 'username_live' ] !== '' ) {
				$this->id    = 'payments';
				$this->label = __( 'Payments', 'wc-vendors' );

				parent::__construct();	
			}
			
		}


		/**
		 * Get sections.
		 *
		 * @return array
		 */
		public function get_sections() {

			$sections = array(
				''       => __( 'General', 'wc-vendors' )
			);

			$paypalap_settings = get_option( 'woocommerce_paypalap_settings', false );
			if ( $paypalap_settings && array_key_exists('username_live', $paypalap_settings ) && $paypalap_settings[ 'username_live' ] !== '' ) {
				$sections[ 'paypal' ] = __( 'PayPal Adaptive Payments', 'wc-vendors' );
			}

			return apply_filters( 'wcvendors_get_sections_' . $this->id, $sections );
		}

		/**
		 * Get settings array.
		 *
		 * @return array
		 */
		public function get_settings( $current_section = '' ) {

			if ( 'paypal' === $current_section ) {

				$settings = apply_filters(
					'wcvendors_settings_payments_paypal', array(

						// Shop Display Options
						array(
							'title' => __( '', 'wc-vendors' ),
							'type'  => 'title',
							'desc'  => sprintf( __( '<h3>PayPal Adaptive Payments - Please Note: PayPal Adaptive Payments has been deprecated by PayPal as of September 2017. These options are for existing users only. This will be completely removed in a future version.</h3>', 'wc-vendors' ), wcv_get_vendor_name( true, false ) ),
							'id'    => 'paypal_options',
						),

						array(
							'title' => __( '', 'wc-vendors' ),
							'type'  => 'title',
							'desc'  => sprintf( __( 'Total Commission due: %s', 'wc-vendors' ), wc_price( WCV_Commission::get_totals( 'due' ) ) ),
							'id'    => 'paypal_options',
						),

						array(
							'title'    => __( 'Instant Pay', 'wc-vendors' ),
							'desc'     => __( 'Enable instantpay', 'wc-vendors' ),
							'desc_tip' => sprintf( __( 'Instantly pay %1$s their commission when an order is made, and if a %1$s has a valid PayPal email added on their Shop Settings page.', 'wc-vendors' ), wcv_get_vendor_name( true, false ) ),
							'id'       => 'wcvendors_payments_paypal_instantpay_enable',
							'default'  => 'no',
							'type'     => 'checkbox',
						),

						array(
							'title'   => __( 'Payment schedule', 'wc-vendors' ),
							'desc'    => __( 'Note: Schedule will only work if instant pay is unchecked', 'wc-vendors' ),
							'id'      => 'wcvendors_payments_paypal_schedule',
							'type'    => 'radio',
							'options' => array(
								'daily'    => __( 'Daily', 'wc-vendors' ),
								'weekly'   => __( 'Weekly', 'wc-vendors' ),
								'biweekly' => __( 'Biweekly', 'wc-vendors' ),
								'monthly'  => __( 'Monthly', 'wc-vendors' ),
								'manual'   => __( 'Manual', 'wc-vendors' ),
								'now'      => '<span style="color:green;"><strong>' . __( 'Now', 'wc-vendors' ) . '</strong></span>',
							),
						),

						array(
							'title'    => __( 'Email notification', 'wc-vendors' ),
							'desc'     => __( 'Enable notify the admin', 'wc-vendors' ),
							'desc_tip' => __( 'Send the marketplace admin an email each time a payment has been made via the payment schedule options above', 'wc-vendors' ),
							'id'       => 'wcvendors_payments_paypal_email_enable',
							'default'  => 'no',
							'type'     => 'checkbox',
						),

						array(
							'type' => 'sectionend',
							'id'   => 'paypal_options',
						),
					)
				);

			} else {

				$settings = apply_filters(
					'wcvendors_settings_payments_general', array(

						// Shop Display Options
						array(
							'title' => __( '', 'wc-vendors' ),
							'type'  => 'title',
							'desc'  => sprintf( __( '<strong>Payments controls how your %s commission is paid out. These settings only function if you are using a supported gateway.</strong> ', 'wc-vendors' ), wcv_get_vendor_name( true, false ) ),
							'id'    => 'payment_general_options',
						),

						array(
							'type' => 'sectionend',
							'id'   => 'payment_general_options',
						),

					)
				);

			}

			return apply_filters( 'wcvendors_get_settings_' . $this->id, $settings, $current_section );

		}

	}

endif;

return new WCVendors_Settings_Payments();
