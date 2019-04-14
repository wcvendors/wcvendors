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
		$this->id    = 'payments';
		$this->label = __( 'Payments', 'wcvendors' );

		parent::__construct();
	}

	/**
	 * Get sections.
	 *
	 * @return array
	 */
	public function get_sections() {
		$sections = array(
			''          => __( 'General', 'wcvendors' ),
		);

		return apply_filters( 'wcvendors_get_sections_' . $this->id, $sections );
	}

	/**
	 * Output the settings.
	 */
	public function output() {
		global $current_section;

		$settings = $this->get_settings( $current_section );
		WCVendors_Admin_Settings::output_fields( $settings );
	}

	/**
	 * Save settings.
	 */
	public function save() {
		global $current_section;

		$settings = $this->get_settings( $current_section );
		WCVendors_Admin_Settings::save_fields( $settings );
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings( $current_section = '' ) {

		$settings = apply_filters( 'wcvendors_settings_display_labels', array(

			// Shop Display Options 
			array(
				'title'    => __( '', 'wcvendors' ),
				'type'     => 'title',
				'desc'     => sprintf( __( '<strong>Payments controls how your %s commission is paid out. To enable commission payments you will be required to purchase one of our available payment extensions. </strong> ', 'wcvendors' ), lcfirst( wcv_get_vendor_name() ) ), 
				'id'       => 'payment_general_options',
			),

			array( 'type' => 'sectionend', 'id' => 'payment_general_options' ),

		) ); 

		return apply_filters( 'wcvendors_get_settings_' . $this->id, $settings, $current_section );

	}

}

endif;

return new WCVendors_Settings_Payments();