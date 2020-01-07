<?php
/**
 * The commission admin settings
 *
 * @package WCVendors/Admin/Settings
 * @phpcs:disable WordPress.WP.I18n
 */

namespace WCVendors\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The commission admin settings
 */
class Commission extends Base {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'commission';
		$this->label = __( 'Commission', 'wc-vendors' );

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
			// General Options.
			array(
				'type' => 'title',
				'desc' => __( 'These are the commission settings for your marketplace', 'wc-vendors' ),
				'id'   => 'commission_options',
			),
			array(
				'title'   => sprintf( __( '%s Commission %%', 'wc-vendors' ), wcv_get_vendor_name() ),
				'desc'    => sprintf( __( 'The global commission rate for your %s', 'wc-vendors' ), lcfirst( wcv_get_vendor_name( false ) ) ),
				'id'      => 'wcvendors_vendor_commission_rate',
				'css'     => 'width:50px;',
				'default' => '50',
				'type'    => 'number',
			),
			array(
				'type' => 'sectionend',
				'id'   => 'commission_options',
			),
		);

		return apply_filters( 'wcvendors_get_settings_' . $this->id, $settings );
	}
}
