<?php

/**
 * Admin setup
 *
 * @author      Jamie Madden, WC Vendors
 * @category    Admin
 * @package     WCVendors/Admin
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WCV_Admin_Setup {

	public function __construct() {

		add_filter( 'set-screen-option', 									array( 'WCV_Admin_Setup', 'set_table_option' ), 10, 3 );
		add_action( 'admin_menu', 											array( 'WCV_Admin_Setup', 'menu' ), 10 );
		add_action( 'woocommerce_admin_order_data_after_shipping_address', 	array( $this, 'add_vendor_details' ), 10, 2 );
		add_action( 'woocommerce_admin_order_actions_end', 					array( $this, 'append_actions' ), 10, 1 );
		add_filter( 'woocommerce_debug_tools', 								array( $this, 'wcvendors_tools' ) );

		add_action( 'admin_head', 											array( $this, 'commission_table_header_styles' ) );
		add_action( 'admin_init', 											array( $this, 'export_commissions' ) );
		add_filter( 'woocommerce_screen_ids', 								array( $this, 'wcv_screen_ids' ) );

	}

	public function add_vendor_details( $order )
	{
		$actions = $this->append_actions( $order, true );

		if (empty( $actions['wc_pv_shipped']['name'] )) {
			return;
		}

		echo '<h4>' . __('Vendors shipped', 'wc-vendors') . '</h4><br/>';
		echo $actions['wc_pv_shipped']['name'];
	}

	public function append_actions( $order, $order_page = false )
	{
		global $woocommerce;

		$order_id = ( version_compare( WC_VERSION, '2.7', '<' ) ) ? $order->id : $order->get_id();

		$authors = WCV_Vendors::get_vendors_from_order( $order );
		$authors = $authors ? array_keys( $authors ) : array();
		if ( empty( $authors ) ) return false;

		$shipped = (array) get_post_meta( $order_id, 'wc_pv_shipped', true );
		$string = '</br></br>';

		foreach ($authors as $author ) {
			 $string .= in_array( $author, $shipped ) ? '&#10004; ' : '&#10005; ';
			 $string .= WCV_Vendors::get_vendor_shop_name( $author );
			 $string .= '</br>';
		}

		$response = array(
			'url'       => '#',
			'name'      => __('Vendors Shipped', 'wc-vendors') . $string,
			'action'    => 'wc_pv_shipped',
			'image_url' => wcv_assets_url . '/images/icons/truck.png',
		);

		if ( ! $order_page ) {
			printf( '<a class="button tips %s" href="%s" data-tip="%s"><img style="width:16px;height:16px;" src="%s"></a>', $response['action'], $response['url'], $response['name'], $response['image_url'] );
		} else {
			echo $response['name'];
		}

		return $response;
	}


	/**
	 * Add the commissions sub menu
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 */
	public static function menu()
	{
		$hook = add_submenu_page( 'wc-vendors', __( 'Commissions', 'wc-vendors' ), __( 'Commissions', 'wc-vendors' ), 'manage_woocommerce', 'pv_admin_commissions', array( 'WCV_Admin_Setup', 'commissions_page' ) );

		add_action( "load-$hook", array( 'WCV_Admin_Setup', 'add_options' ) );
		add_action( "admin_print_styles-$hook", 	array( 'WCV_Admin_Setup', 'commission_enqueue_style' ) );
		add_action( "admin_print_scripts-$hook", 	array( 'WCV_Admin_Setup', 'commission_my_enqueue_script' ) );

	} // menu()


	/**
	 * Add tools to the woocommerce status tools page
	 *
	 * @since 1.9.2
	 * @access public
	 */
	public function wcvendors_tools( $tools ){

		$tools[ 'reset_wcvendor_roles' ] = array(
				'name'    => __( 'Reset WC Vendors roles ', 'wc-vendors' ),
				'button'  => __( 'Reset WC Vendor Roles', 'wc-vendors' ),
				'desc'    => __( 'This will reset the wcvendors roles ( vendor & pending_vendor ), back to the default capabilities.', 'wc-vendors' ),
				'callback' => array( 'WCV_Admin_Setup', 'reset_vendor_roles' )
			);

		$tools[ 'reset_wcvendors' ] = array(
				'name'    => __( 'Reset WC Vendors ', 'wc-vendors' ),
				'button'  => __( 'Reset WC Vendors Settings', 'wc-vendors' ),
				'desc'    => __( 'This will reset wcvendors back to defaults. This DELETES ALL YOUR Settings.', 'wc-vendors' ),
				'callback' => array( 'WCV_Admin_Setup', 'reset_wcvendors' )
			);

		return $tools;

	} // wcvendors_tools()

	/**
	 * Reset the vendor roles
	 *
	 * @since 1.9.2
	 * @access public
	 */
	public static function reset_vendor_roles(){

		$can_add          = WC_Vendors::$pv_options->get_option( 'can_submit_products' );
		$can_edit         = WC_Vendors::$pv_options->get_option( 'can_edit_published_products' );
		$can_submit_live  = WC_Vendors::$pv_options->get_option( 'can_submit_live_products' );

		$args = array(
			'assign_product_terms'      => $can_add,
			'edit_products'             => $can_add || $can_edit,
			'edit_published_products'   => $can_edit,
			'delete_published_products' => $can_edit,
			'delete_products'           => $can_edit,
			'manage_product'            => $can_add,
			'publish_products'          => $can_submit_live,
			'delete_posts'				=> true,
			'read'                      => true,
			'read_products'             => $can_edit || $can_add,
			'upload_files'              => true,
			'import'                    => true,
			'view_woocommerce_reports'  => false,
		);

		remove_role( 'vendor' );
		add_role( 'vendor', __('Vendor', 'wc-vendors'), $args );

		remove_role( 'pending_vendor');
		add_role( 'pending_vendor', __( 'Pending Vendor', 'wc-vendors' ), array(
																							  'read'         => true,
																							  'edit_posts'   => false,
																							  'delete_posts' => false
																						 ) );

		echo '<div class="updated inline"><p>' . __( 'WC Vendor roles successfully reset.', 'wc-vendors' ) . '</p></div>';

	} // reset_vendor_roles()


	/**
	 * Reset wcvendors
	 *
	 * @since 1.9.2
	 * @access public
	 */
	public static function reset_wcvendors(){

		delete_option( WC_Vendors::$id . '_options' );
		echo '<div class="updated inline"><p>' . __( 'WC Vendors was successfully reset. All settings have been reset.', 'wc-vendors' ) . '</p></div>';

	} // reset_wcvendors()


	public static function commission_enqueue_style(){

		wp_enqueue_style( 'commissions_select2_css', wcv_assets_url . 'css/select2.min.css' );

	} //commission_enqueue_style()

	public static function commission_my_enqueue_script(){

		$select2_args = apply_filters( 'wcvendors_select2_commission_args', array(
			'placeholder' => __( 'Select a Vendor', 'wc-vendors' ),
			'allowclear' => true,
		) );

		wp_enqueue_script( 'commissions_select2_styles_js', wcv_assets_url. 'js/select2.min.js', array('jquery') );

		wp_register_script( 'commissions_select2_load_js', wcv_assets_url. 'js/wcv-commissions.js', array('jquery') );
		wp_localize_script( 'commissions_select2_load_js', 'wcv_commissions_select', $select2_args );
		wp_enqueue_script( 'commissions_select2_load_js' );

	}

	/**
	 * Load styles for the commissions table page
	 */
	public function commission_table_header_styles() {

	    $page = ( isset( $_GET[ 'page' ] ) ) ? esc_attr( $_GET[ 'page' ] ) : false;

	    // Only load the styles on the license table page

	    if ( 'pv_admin_commissions' !== $page ) return;

	    echo '<style type="text/css">';
	    echo '.wp-list-table .column-product_id { width: 20%; }';
	    echo '.wp-list-table .column-vendor_id { width: 15%; }';
	    echo '.wp-list-table .column-order_id { width: 8%; }';
	    echo '.wp-list-table .column-total_due { width: 10%;}';
	    echo '.wp-list-table .column-total_shipping { width: 10%;}';
	    echo '.wp-list-table .column-tax { width: 10%;}';
	    echo '.wp-list-table .column-totals { width: 10%;}';
	    echo '.wp-list-table .column-status { width: 5%;}';
	    echo '.wp-list-table .column-time { width: 10%;}';
	    echo '</style>';

	} //table_header_styles()

	/**
	 *
	 *
	 * @param unknown $status
	 * @param unknown $option
	 * @param unknown $value
	 *
	 * @return unknown
	 */
	public static function set_table_option( $status, $option, $value ){
		if ( $option == 'commission_per_page' ) {
			return $value;
		}
	}


	/**
	 *
	 */
	public static function add_options(){
		global $PV_Admin_Page;

		$args = array(
			'label'   => 'Rows',
			'default' => 10,
			'option'  => 'commission_per_page'
		);
		add_screen_option( 'per_page', $args );

		$PV_Admin_Page = new WCV_Commissions_Page();

	}


	/**
	 * HTML setup for the WC > Commission page
	 */
	public static function commissions_page() {
		global $woocommerce, $PV_Admin_Page;
		?>

		<div class="wrap">

			<div id="icon-woocommerce" class="icon32 icon32-woocommerce-reports"><br/></div>
			<h2><?php _e( 'Commission', 'wc-vendors' ); ?></h2>

			<form id="posts-filter" method="get">

			<?php
				$page  = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRIPPED );
				$paged = filter_input( INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT );

				printf( '<input type="hidden" name="page" value="%s" />', $page );
				printf( '<input type="hidden" name="paged" value="%d" />', $paged );
			?>

			<input type="hidden" name="page" value="pv_admin_commissions"/>

			<?php $PV_Admin_Page->prepare_items(); ?>
			<?php $PV_Admin_Page->views() ?>
			<?php $PV_Admin_Page->display() ?>

			</form>
			<div id="ajax-response"></div>
			<br class="clear"/>
		</div>
	<?php
	}

	/*
	*	Export commissions via csv
	*/
	public function export_commissions(){

		// prepare the items to export


		if ( isset( $_GET['action'], $_GET['nonce'] ) && wp_verify_nonce( wp_unslash( $_GET['nonce'] ), 'export_commissions' ) && 'export_commissions' === wp_unslash( $_GET['action'] ) ) {

			include_once( 'class-wcv-commissions-csv-exporter.php' );

			$exporter = new WCV_Commissions_CSV_Export();

			$date = date( 'Y-M-d' );

			if ( ! empty( $_GET['com_status'] ) ) { // WPCS: input var ok.
				$exporter->set_filename( 'wcv_commissions_'. wp_unslash( $_GET['com_status'] ) . '-' . $date . '.csv' ); // WPCS: input var ok, sanitization ok.
			} else {
				$exporter->set_filename( 'wcv_commissions-' . $date . '.csv' ); // WPCS: input var ok, sanitization ok.
			}

			$exporter->export();
		}

	}

	/**
	* Add wc vendors screens to woocommerce screen ids to utilise js and css assets from woocommerce.
	*
	* @since 2.0.0
	*/
	public function wcv_screen_ids( $screen_ids ){
		$screen_ids[] = 'wc-vendors_page_wcv-settings';
		return $screen_ids;
	}


}
