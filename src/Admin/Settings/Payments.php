<?php
/**
 * The display settings class.
 *
 * @package WCVendors/Admin/Settings
 * @phpcs:disable WordPress.WP.I18n
 */

namespace WCVendors\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The display settings class.
 */
class Payments extends Base {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'payments';
		$this->label = __( 'Payments', 'wc-vendors' );

		parent::__construct();
	}

	/**
	 * Get sections.
	 *
	 * @return array
	 */
	public function get_sections() {
		$sections = array(
			'' => __( 'General', 'wc-vendors' ),
		);

		return apply_filters( 'wcvendors_get_sections_' . $this->id, $sections );
	}

	/**
	 * Get settings array.
	 *
	 * @param string $current_section Current section ID.
	 *
	 * @return array
	 */
	public function get_settings( $current_section = '' ) {

		$settings = array(
			// Shop Display Options.
			array(
				'title' => __( '', 'wc-vendors' ),
				'type'  => 'title',
				'desc'  => sprintf( __( '<strong>Payments controls how your %s commission is paid out. To enable commission payments you will be required to purchase one of our available payment extensions. </strong> ', 'wc-vendors' ), lcfirst( wcv_get_vendor_name() ) ),
				'id'    => 'payment_general_options',
			),
			array(
				'type' => 'sectionend',
				'id'   => 'payment_general_options',
			),
		);

		return apply_filters( 'wcvendors_get_settings_' . $this->id, $settings, $current_section );

	}
}
