<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The admin class handles all admin custom page functions for admin view
 *
 * @author      Jamie Madden, WC Vendors
 * @category    Admin
 * @package     WCVendors/Admin
 * @version     2.0.0
 */
class WCVendors_Admin_Menus {

	public $commissions_table;

	/**
	 * Constructor
	 */
	public function __construct() {

		// Add menus
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_menu', array( $this, 'commissions_menu' ), 50 );
		add_action( 'admin_menu', array( $this, 'settings_menu' ), 70 );
		add_action( 'admin_menu', array( $this, 'extensions_menu' ), 80 );
		if ( ! class_exists( 'WCVendors_Pro' ) ){ 
			add_action( 'admin_menu', array( $this, 'go_pro_menu' ), 80 );
		}
		

		add_action( 'admin_head', array( $this, 'commission_table_header_styles' ) );
		add_action( 'admin_footer', array( $this, 'commission_table_script' ) );

		add_filter( 'set_screen_option_wcvendor_commissions_perpage', array( __CLASS__, 'set_commissions_screen' ), 10, 3 );

	}

	/**
	 * WC Vendors menu
	 */
	public function admin_menu() {

		global $menu;

		if ( current_user_can( 'manage_woocommerce' ) ) {
			$menu[] = array( '', 'read', 'separator-woocommerce', '', 'wp-menu-separator wcvendors' );
		}

		add_menu_page(
			__( 'WC Vendors', 'wc-vendors' ),
			__( 'WC Vendors', 'wc-vendors' ),
			'manage_woocommerce',
			'wc-vendors',
			array( $this, 'extensions_page' ),
			'dashicons-cart',
			'50'
		);
	}

	/**
	 * Addons menu item.
	 */
	public function extensions_menu() {

		add_submenu_page(
			'wc-vendors',
			__( 'WC Vendors Extensions', 'wc-vendors' ),
			__( 'Extensions', 'wc-vendors' ),
			'manage_woocommerce',
			'wcv-extensions',
			array( $this, 'extensions_page' )
		);
		remove_submenu_page( 'wc-vendors', 'wc-vendors' );
	}

	/**
	 *    Addons Page
	 */
	public function extensions_page() {
		WCVendors_Admin_Extensions::output();
	}

	/**
	 * Go Pro Menu.
	 * 
	 * @since 2.2.2 
	 */
	public function go_pro_menu() {

		add_submenu_page(
			'wc-vendors',
			__( 'Upgrade To WC Vendors Pro Today', 'wc-vendors' ),
			__( 'Go PRO', 'wc-vendors' ),
			'manage_woocommerce',
			'wcv-go-pro',
			array( $this, 'go_pro_page' )
		);
		remove_submenu_page( 'wc-vendors', 'wc-vendors' );
	}

	/**
	 * Go Pro Page output
	 *
	 * @since 2.2.2
	 */
	public function go_pro_page(){ 
		WCVendors_Admin_GoPro::output();
	}

	/**
	 * Add the commissions sub menu
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public function commissions_menu() {

		$commissions_page = add_submenu_page(
			'wc-vendors',
			__( 'Commissions', 'wc-vendors' ),
			__( 'Commissions', 'wc-vendors' ),
			'manage_woocommerce',
			'wcv-commissions',
			array(
				$this,
				'commissions_page',
			)
		);

		add_action( "load-$commissions_page", array( $this, 'commission_screen_options' ) );

	} // commissions_menu()


	/**
	 * Settings menu item
	 */
	public function settings_menu() {

		$settings_page = add_submenu_page(
			'wc-vendors',
			__( 'WC Vendors Settings', 'wc-vendors' ),
			__( 'Settings', 'wc-vendors' ),
			'manage_woocommerce',
			'wcv-settings',
			array( $this, 'settings_page' )
		);

		add_action( 'load-' . $settings_page, array( $this, 'settings_page_init' ) );
	}


	/**
	 *  Loads required objects into memory for use within settings
	 */
	public function settings_page_init() {

		global $current_tab, $current_section;

		// Include settings pages.
		WCVendors_Admin_Settings::get_settings_pages();

		// Get current tab/section.
		$current_tab     = empty( $_GET['tab'] ) ? 'general' : sanitize_title( $_GET['tab'] );
		$current_section = empty( $_REQUEST['section'] ) ? '' : sanitize_title( $_REQUEST['section'] );

		// Save settings if data has been posted.
		if ( ! empty( $_POST ) ) {
			WCVendors_Admin_Settings::save();
		}

		// Add any posted messages.
		if ( ! empty( $_GET['wcv_error'] ) ) {
			WCVendors_Admin_Settings::add_error( stripslashes( $_GET['wcv_error'] ) );
		}

		if ( ! empty( $_GET['wcv_message'] ) ) {
			WCVendors_Admin_Settings::add_message( stripslashes( $_GET['wcv_message'] ) );
		}
	}

	/**
	 * Settings Page
	 */
	public function settings_page() {

		WCVendors_Admin_Settings::output();
	}

	/**
	 * Commission page output
	 *
	 * @since 2.0.0
	 */
	public function commissions_page() {

		include WCV_ABSPATH_ADMIN . 'views/html-admin-commission-page.php';
	}


	/**
	 * Screen options
	 */
	public function commission_screen_options() {

		$option = 'per_page';
		$args   = [
			'label'   => 'Commissions',
			'default' => 10,
			'option'  => 'wcvendor_commissions_perpage',
		];

		add_screen_option( $option, $args );

		$this->commissions_table = new WCVendors_Commissions_Page();
	}

	public static function set_commissions_screen( $status, $option, $value ) {

		return $value;
	}

	/**
	 * Load styles for the commissions table page
	 */
	public function commission_table_header_styles() {

		$page = ( isset( $_GET['page'] ) ) ? esc_attr( $_GET['page'] ) : false;

		wp_enqueue_style( 'wcv-admin-styles', wcv_assets_url . 'css/wcv-admin.css', array(), WCV_VERSION );

		// Only load the styles on the license table page
		if ( 'wcv-commissions' !== $page ) {
			return;
		}

		echo '<style type="text/css">';
		echo '.wp-list-table .column-qty { width: 8%; }';
		echo '.wp-list-table .column-order_id { width: 8%; }';
		echo '.wp-list-table .column-vendor_id { width: 12%; }';
		echo '.wp-list-table .column-total_due { width: 10%;}';
		echo '.wp-list-table .column-total_shipping { width: 8%;}';
		echo '.wp-list-table .column-tax { width: 5%;}';
		echo '.wp-list-table .column-totals { width: 6%;}';
		echo '.wp-list-table .column-status { width: 7%;}';
		echo '.wp-list-table .column-time { width: 10%;}';
		echo '</style>';

	} //table_header_styles

	/**
	 * Print script required by commission.
	 *
	 * @return  void
	 * @version 2.1.20
	 * @since   2.1.20
	 */
	public function commission_table_script() {
		wp_enqueue_script( 'jquery-ui-datepicker' );
		?>
		<script>
			jQuery(document).ready(
				function() {
					jQuery('#from_date, #to_date').datepicker({
						dateFormat: 'yy-mm-dd'
					});

					jQuery("#vendor_id").select2();

					jQuery('#reset').click( function(e){
						e.preventDefault();
						jQuery('#from_date, #to_date').val('');
						jQuery('#com_status_dropdown, #vendor_id').val('').select2();

						jQuery('#posts-filter').submit();
					});
				}
			);
		</script>
		<?php
	}
}

new WCVendors_Admin_Menus();
