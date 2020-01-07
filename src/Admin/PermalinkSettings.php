<?php
/**
 * Adds settings to the permalinks admin settings page
 *
 * @package WCVendors/Admin
 */

namespace WCVendors\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PermalinkSettings Class.
 */
class PermalinkSettings {

	/**
	 * Permalink settings.
	 *
	 * @var array
	 */
	private $permalinks = array();

	/**
	 * Hook in tabs.
	 */
	public function init_hooks() {
		add_action( 'admin_init', array( $this, 'settings_init' ) );
		add_action( 'admin_init', array( $this, 'settings_save' ) );
	}

	/**
	 * Init our settings.
	 */
	public function settings_init() {

		add_settings_section( 'wcvendors-permalink', __( 'WC Vendors permalinks', 'wc-vendors' ), array( $this, 'settings' ), 'permalink' );

		// Add our settings.
		add_settings_field(
			'wcvendors_vendor_shop_slug',
			__( 'Vendor shop slug', 'wc-vendors' ),
			array( $this, 'vendor_shop_slug_input' ),
			'permalink',
			'wcvendors-permalink'
		);

		do_action( 'wcvendors_permalinks_settings', 'wcvendors-permalink' );

		$this->permalinks = wcv_get_permalink_structure();
	}

	/**
	 * Show a slug input box.
	 */
	public function vendor_shop_slug_input() {
		?>
	<label>
	<input name="vendor_shop_base" type="text" class="regular-text code" value="<?php echo esc_attr( $this->permalinks['vendor_shop_base'] ); ?>" placeholder="<?php echo esc_attr_x( 'vendors', 'slug', 'wc-vendors' ); ?>" />
		<?php /* translators: %s is the home URL. */ ?>
	<code><?php printf( esc_html__( 'If you enter "vendors" your vendors store will be %s/vendors/store-name/', 'wc-vendors' ), esc_url( home_url() ) ); ?></code></label>
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
