<?php
/**
 * The general admin settings.
 *
 * @package WCVendors/Admin/Settings
 * @phpcs:disable WordPress.WP.I18n
 */

namespace WCVendors\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The general admin settings.
 */
class General extends Base {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'general';
		$this->label = __( 'General', 'wc-vendors' );

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
				'title' => __( 'Marketplace Options', 'wc-vendors' ),
				'type'  => 'title',
				'desc'  => __( 'These are the general settings for your marketplace', 'wc-vendors' ),
				'id'    => 'general_options',
			),
			array(
				'title'   => sprintf( __( '%s Registration', 'wc-vendors' ), wcv_get_vendor_name() ),
				'desc'    => sprintf( __( 'Allow users to apply to become a %s', 'wc-vendors' ), lcfirst( wcv_get_vendor_name() ) ),
				'id'      => 'wcvendors_vendor_allow_registration',
				'default' => 'no',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Terms & Conditions', 'wc-vendors' ),
				'desc'    => sprintf( __( 'Make the terms and conditions checkbox always visible even if become a %s is not checked', 'wc-vendors' ), wcv_get_vendor_name() ),
				'id'      => 'wcvendors_terms_and_conditions_visibility',
				'default' => 'yes',
				'type'    => 'checkbox',
			),
			array(
				'title'   => sprintf( __( '%s Approval', 'wc-vendors' ), wcv_get_vendor_name() ),
				'desc'    => sprintf( __( 'Manually approve all %s applications', 'wc-vendors' ), lcfirst( wcv_get_vendor_name() ) ),
				'id'      => 'wcvendors_vendor_approve_registration',
				'default' => 'no',
				'type'    => 'checkbox',
			),
			array(
				'title'   => sprintf( __( '%s Taxes', 'wc-vendors' ), wcv_get_vendor_name() ),
				'desc'    => sprintf( __( 'Give any taxes to the %s', 'wc-vendors' ), wcv_get_vendor_name() ),
				'id'      => 'wcvendors_vendor_give_taxes',
				'default' => 'no',
				'type'    => 'checkbox',
			),
			array(
				'title'   => sprintf( __( '%s Shipping', 'wc-vendors' ), wcv_get_vendor_name() ),
				'desc'    => sprintf( __( 'Give any shipping to the %s', 'wc-vendors' ), wcv_get_vendor_name() ),
				'id'      => 'wcvendors_vendor_give_shipping',
				'default' => 'no',
				'type'    => 'checkbox',
			),
			array(
				'type' => 'sectionend',
				'id'   => 'general_options',
			),
		);

		return apply_filters( 'wcvendors_get_settings_' . $this->id, $settings );
	}
}
