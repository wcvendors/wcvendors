<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The advanced admin settings
 *
 * @author      Lindeni Mahlalela, WC Vendors
 * @category    Settings
 * @package     WCVendors/Admin/Settings
 * @version     2.0.0
 */

if ( ! class_exists( 'WCVendors_Settings_Advanced', false ) ) :

/**
 * WC_Admin_Settings_Advanced.
 */
class WCVendors_Settings_Advanced extends WCVendors_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'advanced';
		$this->label = __( 'Advanced', 'wc-vendors' );

		parent::__construct();
	}

	/**
	 * Get sections.
	 *
	 * @return array
	 */
	public function get_sections() {
		$sections = array(
			''          => __( 'Advanced', 'wc-vendors' ),
		);

		return apply_filters( 'wcvendors_get_sections_' . $this->id, $sections );
	}

    /**
     * 
     */
    
    public function get_settings( $current_section = '' ) {

		$settings = array();

		if ( '' === $current_section ) {

			$settings = apply_filters( 'wcvendors_settings', array(

				//  Advanced Options
				array(
					'title'    => __( 'Plugin Uninstall Options', 'wc-vendors' ),
					'type'     => 'title',
					'desc'     => __( 'These options are effective when uninstalling the plugin', 'wc-vendors' ),
					'id'       => 'advanced_options',
				),				
				array(
					'title'   => __( 'Delete all Data', 'wc-vendors' ),
					'desc'    => __( 'delete all WC Vendors data when deactivating the plugin.', 'wc-vendors' ),
					'id'      => 'wcvendors_delete_all',
					'default' => 'no',
					'type'    => 'radio',
				),
				array(
					'title'   => __( 'Do not delete data', 'wc-vendors' ),
					'desc'    => __( 'Leave all data as is when deactivating the plugin.', 'wc-vendors' ),
					'id'      => 'wcvendors_leave_all',
					'default' => 'no',
					'type'    => 'radio',
                ),
                array(
					'title'   => __( 'Do not delete data', 'wc-vendors' ),
					'desc'    => __( 'Leave all data as is when deactivating the plugin.', 'wc-vendors' ),
					'id'      => 'wcvendors_leave_all',
					'default' => 'no',
					'type'    => 'radio',
				),
				array( 'type' => 'sectionend', 'id' => 'advanced_options' ),

			) );
		}

		return apply_filters( 'wcvendors_get_settings_' . $this->id, $settings, $current_section );
	}

}

endif;

return new WCVendors_Settings_Advanced();
