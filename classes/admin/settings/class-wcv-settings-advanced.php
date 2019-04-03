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
				'' => __( 'Advanced', 'wc-vendors' ),
			);

			return apply_filters( 'wcvendors_get_sections_' . $this->id, $sections );
		}

		/**
		 * Uninstall settings
		 *
		 * @param string $current_section
		 *
		 * @return array settings
		 * @since 2.0.8
		 */
		public function get_settings( $current_section = '' ) {

			$settings = array();

			if ( '' === $current_section ) {

				$settings = apply_filters(
					'wcvendors_settings', array(

						// Advanced Options
						array(
							'title' => __( 'Plugin Uninstall Options', 'wc-vendors' ),
							'type'  => 'title',
							'desc'  => __( 'These options are effective when uninstalling the plugin. If "Delete All Data" is checked all this plugin\'s data will be removed, uncheck it to choose what to delete when uninstalling the plugin.', 'wc-vendors' ),
							'id'    => 'advanced_options',
						),
						array(
							'title'   => __( 'Delete all data', 'wc-vendors' ),
							'desc'    => __( 'Delete all WC Vendors data when deactivating the plugin.', 'wc-vendors' ),
							'id'      => 'wcvendors_uninstall_delete_all_data',
							'default' => 'no',
							'type'    => 'checkbox',
						),
						array(
							'title'   => __( 'Delete custom table', 'wc-vendors' ),
							'desc'    => __( 'Delete all data included in the custom tables. This will delete all commissions', 'wc-vendors' ),
							'id'      => 'wcvendors_uninstall_delete_custom_table',
							'default' => 'no',
							'type'    => 'checkbox',
						),
						array(
							'title'   => __( 'Delete settings options', 'wc-vendors' ),
							'desc'    => __( 'Delete all plugin options when uninstalling the plugin.', 'wc-vendors' ),
							'id'      => 'wcvendors_uninstall_delete_settings_options',
							'default' => 'no',
							'type'    => 'checkbox',
						),
						array(
							'title'   => __( 'Delete WC Vendors pages', 'wc-vendors' ),
							'desc'    => __( 'Delete all pages created by WC Vendors.', 'wc-vendors' ),
							'id'      => 'wcvendors_uninstall_delete_custom_pages',
							'default' => 'no',
							'type'    => 'checkbox',
						),
						array(
							'title'   => __( 'Remove custom Roles', 'wc-vendors' ),
							'desc'    => __( 'Remove custom roles registered by WC Vendors.', 'wc-vendors' ),
							'id'      => 'wcvendors_uninstall_delete_vendor_roles',
							'default' => 'no',
							'type'    => 'checkbox',
						),

						array(
							'type' => 'sectionend',
							'id'   => 'advanced_options',
						),

					)
				);
			}

			return apply_filters( 'wcvendors_get_settings_' . $this->id, $settings, $current_section );
		}

	}

endif;

return new WCVendors_Settings_Advanced();
