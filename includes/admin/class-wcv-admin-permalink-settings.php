<?php
/**
 * Adds settings to the permalinks admin settings page
 *
 * @author      Jamie Madden, WC Vendors
 * @category    Admin
 * @package     WCVendors/Admin
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WCVendors_Admin_Permalink_Settings', false ) ) :

	/**
	 * WCVendors_Admin_Permalink_Settings Class.
	 */
	class WCVendors_Admin_Permalink_Settings {

		/**
		 * Permalink settings.
		 *
		 * @var array
		 */
		private $permalinks = array();

		/**
		 * Hook in tabs.
		 */
		public function __construct() {
			$this->settings_init();
			$this->settings_save();
		}

		/**
		 * Init our settings.
		 */
		public function settings_init() {

			add_settings_section( 'wcvendors-permalink', __( 'WC Vendors permalinks', 'wcvendors' ), array( $this, 'settings' ), 'permalink' );

			// Add our settings
			add_settings_field(
				'wcvendors_vendor_shop_slug',               // id
				__( 'Vendor shop slug', 'wcvendors' ),      // setting title
				array( $this, 'vendor_shop_slug_input' ),   // display callback
				'permalink',                                // settings page
				'wcvendors-permalink'                       // settings section
			);

			do_action( 'wcvendors_permalinks_settings', 'wcvendors-permalink' );

			$this->permalinks = wcv_get_permalink_structure();
		}

		/**
		 *
		 */
		public function settings() {
			echo wpautop( __( 'These settings control the permalinks used specifically for WC Vendors.', 'wcvendors' ) );

		}

		/**
		 * Show a slug input box.
		 */
		public function vendor_shop_slug_input() {
			?>
		<label>
		<input name="vendor_shop_base" type="text" class="regular-text code" value="<?php echo esc_attr( $this->permalinks['vendor_shop_base'] ); ?>" placeholder="<?php echo esc_attr_x( 'vendors', 'slug', 'wcvendors' ); ?>" />
		<code><?php printf( __( 'If you enter "vendors" your vendors store will be %s/vendors/store-name/', 'woocommerce' ), esc_html( home_url() ) ); ?></code></label>
			<?php
		}


		/**
		 * Save the settings.
		 */
		public function settings_save() {

			if ( ! is_admin() ) {
				return;
			}

			// We need to save the options ourselves; settings api does not trigger save for the permalinks page.
			if ( isset( $_POST['permalink_structure'] ) ) {
				if ( function_exists( 'switch_to_locale' ) ) {
					switch_to_locale( get_locale() );
				}

				$permalinks                     = (array) get_option( 'wcvendors_permalinks', array() );
				$permalinks['vendor_shop_base'] = wc_sanitize_permalink( trim( $_POST['vendor_shop_base'] ) );

				update_option( 'wcvendors_permalinks', $permalinks );

				if ( function_exists( 'restore_current_locale' ) ) {
					restore_current_locale();
				}
			}
		}
	}

endif;

return new WCVendors_Admin_Permalink_Settings();
