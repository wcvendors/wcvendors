<?php
/**
 * Admin setup wziard
 *
 * @author      WooCommerce, Jamie Madden, WC Vendors
 * @category    Admin
 * @package     WCVendors/Admin
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCVendors_Admin_Setup_Wizard class.
 */
class WCVendors_Admin_Setup_Wizard {

	/**
	 * Current step
	 *
	 * @var string
	 */
	private $step   = '';

	/**
	 * Steps for the setup wizard
	 *
	 * @var array
	 */
	private $steps  = array();

	/**
	 * Actions to be executed after the HTTP response has completed
	 *
	 * @var array
	 */
	private $deferred_actions  = array();

	/**
	 * Hook in tabs.
	 */
	public function __construct() {

		if ( apply_filters( 'wcv_enable_setup_wizard', true ) && current_user_can( 'manage_woocommerce' ) ) {
			add_action( 'admin_menu', array( $this, 'admin_menus' ) );
			add_action( 'admin_init', array( $this, 'setup_wizard' ) );
		}
	}

	/**
	 * Add admin menus/screens.
	 */
	public function admin_menus() {
		add_dashboard_page( '', '', 'manage_options', 'wcv-setup', '' );
	}

	public function get_plugin_settings(){
		return get_option( 'wc_prd_vendor_options' );
	}

	public function set_plugin_settings( $settings ){
		$existing_settings = $this->get_plugin_settings();
		$added_settings = $settings + $existing_settings;
		$updated = update_option( 'wc_prd_vendor_options', $added_settings );
		return $updated;
	}

	public function get_option( $key, $default = false ){
		$settings = $this->get_plugin_settings();
		$option = isset( $settings[ $key ] ) ? maybe_unserialize( $settings[ $key ] ) : $default;
		return $option;
	}


	/**
	 * Show the setup wizard.
	 */
	public function setup_wizard() {

		if ( empty( $_GET['page'] ) || 'wcv-setup' !== $_GET['page'] ) {
			return;
		}
		$default_steps = array(
			'store_setup' => array(
				'name'    => __( 'Start', 'wcvendors' ),
				'view'    => array( $this, 'wcv_setup_general' ),
				'handler' => array( $this, 'wcv_setup_general_save' ),
			),
			'capabilities' => array(
				'name'    => __( 'Capabilities', 'wcvendors' ),
				'view'    => array( $this, 'wcv_setup_capabilities' ),
				'handler' => array( $this, 'wcv_setup_capabilities_save' ),
			),
			'shipping' => array(
				'name'    => __( 'Pages', 'wcvendors' ),
				'view'    => array( $this, 'wcv_setup_pages' ),
				'handler' => array( $this, 'wcv_setup_pages_save' ),
			),
			'ready' => array(
				'name'    => __( 'Ready!', 'wcvendors' ),
				'view'    => array( $this, 'wcv_setup_ready' ),
				'handler' => '',
			),
		);

		$this->steps = apply_filters( 'wcv_setup_wizard_steps', $default_steps );
		$this->step = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) );
		$suffix     = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array( 'jquery' ), '2.70', true );
		wp_register_script( 'selectWoo', WC()->plugin_url() . '/assets/js/selectWoo/selectWoo.full' . $suffix . '.js', array( 'jquery' ), '1.0.0' );

		wp_register_script( 'wc-enhanced-select', WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select' . $suffix . '.js', array( 'jquery', 'selectWoo' ), WC_VERSION );
		wp_localize_script( 'wc-enhanced-select', 'wc_enhanced_select_params', array(
			'i18n_no_matches'           => _x( 'No matches found', 'enhanced select', 'wcvendors' ),
			'i18n_ajax_error'           => _x( 'Loading failed', 'enhanced select', 'wcvendors' ),
			'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'enhanced select', 'wcvendors' ),
			'i18n_input_too_short_n'    => _x( 'Please enter %qty% or more characters', 'enhanced select', 'wcvendors' ),
			'i18n_input_too_long_1'     => _x( 'Please delete 1 character', 'enhanced select', 'wcvendors' ),
			'i18n_input_too_long_n'     => _x( 'Please delete %qty% characters', 'enhanced select', 'wcvendors' ),
			'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'wcvendors' ),
			'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'enhanced select', 'wcvendors' ),
			'i18n_load_more'            => _x( 'Loading more results&hellip;', 'enhanced select', 'wcvendors' ),
			'i18n_searching'            => _x( 'Searching&hellip;', 'enhanced select', 'wcvendors' ),
			'ajax_url'                  => admin_url( 'admin-ajax.php' ),
			'search_products_nonce'     => wp_create_nonce( 'search-products' ),
			'search_customers_nonce'    => wp_create_nonce( 'search-customers' ),
		) );

		// @todo fix the select2 styles in our admin.css
		wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );

		wp_enqueue_style( 'wcv-setup', wcv_assets_url . 'css/wcv-setup.css', array( 'dashicons', 'install' ), WCV_VERSION );
		wp_register_script( 'wcv-setup', wcv_assets_url . 'js/admin/wcv-setup.js', array( 'jquery', 'wc-enhanced-select', 'jquery-blockui', 'wp-util' ), WCV_VERSION );

		if ( ! empty( $_POST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) {
			call_user_func( $this->steps[ $this->step ]['handler'], $this );
		}

		ob_start();
		$this->setup_wizard_header();
		$this->setup_wizard_steps();
		$this->setup_wizard_content();
		$this->setup_wizard_footer();
		exit;
	}

	/**
	 * Get the URL for the next step's screen.
	 *
	 * @param string $step  slug (default: current step).
	 * @return string       URL for next step if a next step exists.
	 *                      Admin URL if it's the last step.
	 *                      Empty string on failure.
	 * @since 3.0.0
	 */
	public function get_next_step_link( $step = '' ) {
		if ( ! $step ) {
			$step = $this->step;
		}

		$keys = array_keys( $this->steps );
		if ( end( $keys ) === $step ) {
			return admin_url();
		}

		$step_index = array_search( $step, $keys );
		if ( false === $step_index ) {
			return '';
		}

		return add_query_arg( 'step', $keys[ $step_index + 1 ], remove_query_arg( 'activate_error' ) );
	}

	/**
	 * Setup Wizard Header.
	 */
	public function setup_wizard_header() {

		include( WCV_ABSPATH_ADMIN . 'views/setup/header.php' );
	}

	/**
	 * Setup Wizard Footer.
	 */
	public function setup_wizard_footer() {
		include( WCV_ABSPATH_ADMIN . 'views/setup/footer.php' );
	}

	/**
	 * Output the steps.
	 */
	public function setup_wizard_steps() {
		$output_steps = $this->steps;
		include( WCV_ABSPATH_ADMIN . 'views/setup/steps.php' );
	}

	/**
	 * Output the content for the current step.
	 */
	public function setup_wizard_content() {
		echo '<div class="wcv-setup-content">';
		if ( ! empty( $this->steps[ $this->step ]['view'] ) ) {
			call_user_func( $this->steps[ $this->step ]['view'], $this );
		}
		echo '</div>';
	}

	/**
	 * Helper method to retrieve the current user's email address.
	 *
	 * @return string Email address
	 */
	protected function get_current_user_email() {
		$current_user = wp_get_current_user();
		$user_email   = $current_user->user_email;

		return $user_email;
	}

	/**
	 * Initial "marketplace setup" step.
	 * Vendor registration, taxes and shipping
	 */
	public function wcv_setup_general() {

		$allow_registration = $this->get_option( 'show_vendor_registration' );
		$manual_approval 	= $this->get_option( 'manual_vendor_registration' );
		$vendor_taxes		= $this->get_option( 'give_tax' );
		$vendor_shipping	= $this->get_option( 'give_shipping' );
		$commission_rate 	= $this->get_option( 'default_commission' );

		include( WCV_ABSPATH_ADMIN . 'views/setup/general.php' );
	}

	/**
	 * Save initial marketplace settings.
	 */
	public function wcv_setup_general_save() {

		check_admin_referer( 'wcv-setup' );

		$allow_registration = isset( $_POST[ 'wcv_vendor_allow_registration' ] ) ? sanitize_text_field( $_POST[ 'wcv_vendor_allow_registration' ] ) : '';
		$manual_approval 	= isset( $_POST[ 'wcv_vendor_approve_registration'] )  ? sanitize_text_field( $_POST[ 'wcv_vendor_approve_registration' ] ) : '';
		$vendor_taxes		= isset( $_POST[ 'wcv_vendor_give_taxes' ] ) ? sanitize_text_field( $_POST[ 'wcv_vendor_give_taxes' ] ) : '';
		$vendor_shipping	= isset( $_POST[ 'wcv_vendor_give_shipping' ] ) ? sanitize_text_field( $_POST[ 'wcv_vendor_give_shipping' ] ) : '';
		$commission_rate 	= sanitize_text_field( $_POST[ 'wcv_vendor_commission_rate' ] );

		$settings = array(
			'show_vendor_registration'		=>  $allow_registration,
			'manual_vendor_registration'	=>  $manual_approval,
			'give_tax'						=>  $vendor_taxes,
	 		'give_shipping' 				=>  $vendor_shipping,
			'default_commission' 			=>  $commission_rate,
		);

 		$this->set_plugin_settings( $settings );
		WCV_Install::create_pages();
		wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}

	/**
	 * General setup
	 * Vendor registration, taxes and shipping
	 */
	public function wcv_setup_capabilities() {

		$products_enabled 	= $this->get_option( 'can_submit_products' );
		$live_products 		= $this->get_option( 'can_edit_published_products' );
		$products_approval	= $this->get_option( 'can_submit_live_products' );
		$orders_enabled		= $this->get_option( 'can_show_orders' );
		$export_orders 		= $this->get_option( 'can_export_csv' );
		$view_order_notes 	= $this->get_option( 'can_view_order_comments' );
		$add_order_notes 	= $this->get_option( 'can_submit_order_comments' );

		include( WCV_ABSPATH_ADMIN . 'views/setup/capabilities.php' );
	}

	/**
	 * Save capabilities settings.
	 */
	public function wcv_setup_capabilities_save() {

		check_admin_referer( 'wcv-setup' );

		$products_enabled 	= isset( $_POST[ 'wcv_capability_products_enabled' ] ) ? sanitize_text_field( $_POST[ 'wcv_capability_products_enabled' ] ) : '';
		$live_products 		= isset( $_POST[ 'wcv_capability_products_edit' ] ) ? sanitize_text_field( $_POST[ 'wcv_capability_products_edit' ] ) : '';
		$products_approval	= isset( $_POST[ 'wcv_capability_products_live' ] ) ? sanitize_text_field( $_POST[ 'wcv_capability_products_live' ] ) : '';
		$orders_enabled		= isset( $_POST[ 'wcv_capability_orders_enabled' ] ) ? sanitize_text_field( $_POST[ 'wcv_capability_orders_enabled' ] ) : '';
		$export_orders 		= isset( $_POST[ 'wcv_capability_orders_export' ] ) ? sanitize_text_field( $_POST[ 'wcv_capability_orders_export' ] ) : '';
		$view_order_notes 	= isset( $_POST[ 'wcv_capability_order_read_notes' ] ) ? sanitize_text_field( $_POST[ 'wcv_capability_order_read_notes' ] ) : '';
		$add_order_notes 	= isset( $_POST[ 'wcv_capability_order_update_notes' ] ) ? sanitize_text_field( $_POST[ 'wcv_capability_order_update_notes' ] ) : '';

		$settings = array(
			'can_submit_products' => $products_enabled,
			'can_edit_published_products' => $live_products,
			'can_submit_live_products' => $products_approval,
			'can_show_orders' => $orders_enabled,
			'can_export_csv' => $export_orders,
			'can_view_order_comments' => $view_order_notes,
			'can_submit_order_comments' => $add_order_notes,
		);

		$this->set_plugin_settings( $settings );

		wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}

	/**
	 * Initial "marketplace setup" step.
	 * Vendor registration, taxes and shipping
	 */
	public function wcv_setup_pages() {

		$dashboard_page 	= $this->get_option( 'vendor_dashboard_page' );
		$shop_settings_page = $this->get_option( 'shop_settings_page' );
		$orders_page 		= $this->get_option( 'product_orders_page' );

		include( WCV_ABSPATH_ADMIN . 'views/setup/pages.php' );
	}

	/**
	 * Initial "marketplace setup" step.
	 * Vendor registration, taxes and shipping
	 */
	public function wcv_setup_pages_save() {

		$dashboard_page 	= sanitize_text_field( $_POST[ 'vendor_dashboard_page' ] );
		$shop_settings_page = sanitize_text_field( $_POST[ 'shop_setttings_page' ] );
		$orders_page		= sanitize_text_field( $_POST[ 'product_orders_page' ] );

		$settings = array(
			'vendor_dashboard_page' => $dashboard_page,
			'shop_settings_page' 	=> $shop_settings_page,
			'product_orders_page' 	=> $orders_page,
		);

		$this->set_plugin_settings( $settings );

		wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;

	}

	/**
	 * Final step.
	 */
	public function wcv_setup_ready() {

		$setup_done = array( 'db_version' => '2.0.0' );
		$this->set_plugin_settings( $setup_done );

		$user_email   	= $this->get_current_user_email();
		$forums   		= 'https://wordpress.org/support/plugin/wc-vendors';
		$docs_url     	= 'https://docs.wcvendors.com/?utm_source=setupwizard&utm_medium=product&utm_content=docs&utm_campaign=plugin';
		$help_text    = sprintf(
			/* translators: %1$s: link to videos, %2$s: link to docs */
			__( 'Don\'t forget to check our <a href="%1$s" target="_blank">documentation</a> to learn more about setting up WC Vendors and if you need help, be sure to visit our <a href="%2$s" target="_blank">free support forums</a>.', 'wcvendors' ),
			$docs_url,
			$forums
		);
		include( WCV_ABSPATH_ADMIN . 'views/setup/ready.php' );
	}
}

new WCVendors_Admin_Setup_Wizard();
