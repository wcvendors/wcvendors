<?php

/**
 * WP-Admin users page
 *
 * @author  Matt Gates <http://mgates.me>
 * @package WC_Vendors
 */


class WCV_Admin_Users {


	/**
	 * Constructor
	 */
	function __construct() {

		if ( ! is_admin() ) {
			return;
		}

		add_action( 'edit_user_profile'       , array( $this, 'show_extra_profile_fields' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_extra_profile_fields' ) );

		add_filter( 'add_menu_classes', array( $this, 'show_pending_number' ) );

		// Add vendor shop name to user page
		add_filter( 'manage_users_columns', 		array( $this,  'add_vendor_shop_column' ), 15, 1 );
		add_filter( 'manage_users_custom_column', 	array( $this,  'add_vendor_shop_column_data' ), 10, 3 );
		add_filter( 'bulk_actions-users',			array( $this,  'set_vendor_default_role' ) );
		add_filter( 'handle_bulk_actions-users', 	array( $this,  'handle_set_vendor_primary_role' ), 10, 3 );


		// Disabling non-vendor related items on the admin screens
		if ( WCV_Vendors::is_vendor( get_current_user_id() ) ) {
			add_filter( 'woocommerce_csv_product_role'       , array( $this, 'csv_import_suite_compatibility' ) );
			add_filter( 'woocommerce_csv_product_export_args', array( $this, 'csv_import_suite_compatibility_export' ) );

			// Admin page lockdown
			remove_action( 'admin_init', 'woocommerce_prevent_admin_access' );
			add_action( 'admin_init'   , array( $this, 'prevent_admin_access' ) );

			add_filter( 'woocommerce_prevent_admin_access', array( $this, 'deny_admin_access' ) );

			// WC > Product page fixes
			add_action( 'load-post-new.php' , array( $this, 'confirm_access_to_add' ) );
			add_action( 'load-edit.php'     , array( $this, 'edit_nonvendors' ) );
			add_filter( 'views_edit-product', array( $this, 'hide_nonvendor_links' ) );

			// Filter user attachments so they only see their own attachements
			add_action( 'ajax_query_attachments_args'  , array( $this, 'show_user_attachment_ajax' ) );
			add_filter( 'parse_query'                  , array( $this, 'show_user_attachment_page' ) );
			add_action( 'admin_menu'                   , array( $this, 'remove_menu_page' ), 99 );
			add_action( 'add_meta_boxes'               , array( $this, 'remove_meta_boxes' ), 99 );
			add_filter( 'product_type_selector'        , array( $this, 'filter_product_types' ), 99 );
			add_filter( 'product_type_options'         , array( $this, 'filter_product_type_options' ), 99 );
			add_filter( 'woocommerce_product_data_tabs', array( $this, 'filter_product_data_tabs' ), 99, 2 );

			// Vendor Capabilities
			// Duplicate product
			add_filter( 'woocommerce_duplicate_product_capability', array( $this, 'add_duplicate_capability' ) );

			// WC > Product featured
			add_filter( 'manage_product_posts_columns', array( $this, 'manage_product_columns' ), 99 );

			// Check allowed product types and hide controls
			add_filter( 'product_type_options', array( $this, 'check_allowed_product_type_options' ) );			
		}

	}

	public function confirm_access_to_add() {

		if ( empty( $_GET['post_type'] ) || $_GET['post_type'] != 'product' ) {
			return;
		}

		$can_submit = wc_string_to_bool( get_option( 'wcvendors_capability_products_enabled', 'no' ) );

		if ( ! $can_submit ) {
			wp_die( sprintf( __( 'You are not allowed to submit products. <a href="%s">Go Back</a>', 'wc-vendors' ), admin_url( 'edit.php?post_type=product' ) ) );
		}
	}

	public function csv_import_suite_compatibility( $capability ) {

		return 'manage_product';
	}

	public function csv_import_suite_compatibility_export( $args ) {

		$args['author'] = get_current_user_id();

		return $args;
	}

	/*
	* Enable/disable duplicate product
	*/
	public function add_duplicate_capability( $capability ) {

		if ( wc_string_to_bool( get_option( 'wcvendors_capability_product_duplicate', 'no' ) ) ) {
			return 'manage_product';
		}

		return $capability;
	}

	/**
	 *
	 *
	 * @param unknown $menu
	 *
	 * @return unknown
	 */
	public function show_pending_number( $menu ) {

		$args = array(
			'post_type'   => 'product',
			'author'      => get_current_user_id(),
			'post_status' => 'pending',
		);

		if ( ! WCV_Vendors::is_vendor( get_current_user_id() ) ) {
			unset( $args['author'] );
		}

		$pending_posts = get_posts( $args );

		$pending_count = is_array( $pending_posts ) ? count( $pending_posts ) : 0;

		$menu_str = 'edit.php?post_type=product';

		foreach ( $menu as $menu_key => $menu_data ) {

			if ( $menu_str != $menu_data[2] ) {
				continue;
			}

			if ( $pending_count > 0 ) {
				$menu[ $menu_key ][0] .= " <span class='update-plugins counting-$pending_count'><span class='plugin-count'>" . number_format_i18n( $pending_count ) . '</span></span>';
			}
		}

		return $menu;
	}

	/**
	 *
	 *
	 * @param unknown $types
	 * @param unknown $product_type
	 *
	 * @return unknown
	 */
	function filter_product_types( $types ) {

		$product_types = (array) get_option( 'wcvendors_capability_product_types', array() );
		$product_misc  = WCV_Product_Meta::get_product_capabilities();

		unset( $product_misc['taxes'] );

		// Filter product type drop down
		foreach ( $types as $key => $value ) {
			if ( in_array( $key, $product_types ) ) {
				unset( $types[ $key ] );
			}
		}

		return $types;
	}

	/**
	 * Filter the product meta tabs in wp-admin
	 *
	 * @since 1.9.0
	 */
	function filter_product_data_tabs( $tabs ) {

		$product_panel = get_option( 'wcvendors_capability_product_data_tabs', array() );

		if ( ! $product_panel ) {
			return $tabs;
		}

		foreach ( $tabs as $key => $value ) {
			if ( in_array( $key, $product_panel ) ) {
				unset( $tabs[ $key ] );
			}
		}

		return $tabs;

	} // filter_product_data_tabs()


	/**
	 *
	 *
	 * @param unknown $types
	 *
	 * @return unknown
	 */
	function filter_product_type_options( $types ) {

		$product_options = get_option( 'wcvendors_capability_product_type_options', array() );

		if ( ! $product_options ) {
			return $types;
		}

		foreach ( $types as $key => $value ) {
			if ( ! empty( $product_options[ $key ] ) ) {
				unset( $types[ $key ] );
			}
		}

		return $types;
	}


	/**
	 * Show attachments only belonging to vendor
	 *
	 * @param object $query
	 */
	function show_user_attachment_ajax( $query ) {

		$user_id = get_current_user_id();
		if ( $user_id ) {
			$query['author'] = $user_id;
		}

		return $query;
	}

	/**
	 * Show attachments only belonging to vendor
	 *
	 * @param object $query
	 */
	function show_user_attachment_page( $query ) {

		global $current_user, $pagenow;

		if ( ! is_a( $current_user, 'WP_User' ) ) {
			return;
		}

		if ( 'upload.php' != $pagenow && 'media-upload.php' != $pagenow ) {
			return;
		}

		if ( ! current_user_can( 'delete_pages' ) ) {
			$query->set( 'author', $current_user->ID );
		}

		return;
	}

	/**
	 * Allow vendors to access admin when disabled
	 */
	public function prevent_admin_access() {

		$permitted_user = ( current_user_can( 'edit_posts' ) || current_user_can( 'manage_woocommerce' ) || current_user_can( 'vendor' ) );

		if ( get_option( 'woocommerce_lock_down_admin' ) == 'yes' && ! is_ajax() && ! $permitted_user ) {
			wp_safe_redirect( get_permalink( woocommerce_get_page_id( 'myaccount' ) ) );
			exit;
		}
	}

	public function deny_admin_access() {

		return false;
	}


	/**
	 * Request when load-edit.php
	 */
	public function edit_nonvendors() {

		add_action( 'request', array( $this, 'hide_nonvendor_products' ) );
	}


	/**
	 * Hide links that don't matter anymore from vendors
	 *
	 * @param array $views
	 *
	 * @return array
	 */
	public function hide_nonvendor_links( $views ) {

		return array();
	}


	/**
	 * Hide products that don't belong to the vendor
	 *
	 * @param array $query_vars
	 *
	 * @return array
	 */
	public function hide_nonvendor_products( $query_vars ) {

		if ( array_key_exists( 'post_type', $query_vars ) && ( $query_vars['post_type'] == 'product' ) ) {
			$query_vars['author'] = get_current_user_id();
		}

		return $query_vars;
	}


	/**
	 * Remove the media library menu
	 */
	public function remove_menu_page() {

		global $pagenow;

		remove_menu_page( 'index.php' ); /* Hides Dashboard menu */
		remove_menu_page( 'separator1' ); /* Hides separator under Dashboard menu*/
		remove_all_actions( 'admin_notices' );

		$can_submit = 'yes' == get_option( 'wcvendors_capability_products_enabled' ) ? true : false;

		if ( ! $can_submit ) {
			global $submenu;
			unset( $submenu['edit.php?post_type=product'][10] );
		}

		if ( $pagenow == 'index.php' ) {
			wp_redirect( admin_url( 'profile.php' ) );
		}
	}


	/**
	 *
	 */
	public function remove_meta_boxes() {

		remove_meta_box( 'postcustom', 'product', 'normal' );
		remove_meta_box( 'wpseo_meta', 'product', 'normal' );
		remove_meta_box( 'expirationdatediv', 'product', 'side' );
	}


	/**
	 * Update the vendor PayPal email
	 *
	 * @param int $vendor_id
	 *
	 * @return bool
	 */
	public function save_extra_profile_fields( $vendor_id ) {

		if ( ! current_user_can( 'edit_user', $vendor_id ) ) {
			return false;
		}

		if ( ! WCV_Vendors::is_pending( $vendor_id ) && ! WCV_Vendors::is_vendor( $vendor_id ) ) {
			return;
		}

		$users = get_users(
			array(
				'meta_key'   => 'pv_shop_slug',
				'meta_value' => sanitize_title( $_POST['pv_shop_name'] ),
			)
		);
		if ( empty( $users ) || $users[0]->ID == $vendor_id ) {
			update_user_meta( $vendor_id, 'pv_shop_name', $_POST['pv_shop_name'] );
			update_user_meta( $vendor_id, 'pv_shop_slug', sanitize_title( $_POST['pv_shop_name'] ) );
		}

		update_user_meta( $vendor_id, 'pv_paypal', $_POST['pv_paypal'] );
		update_user_meta( $vendor_id, 'pv_shop_html_enabled', isset( $_POST['pv_shop_html_enabled'] ) );

		if ( apply_filters( 'wcvendors_admin_user_meta_commission_rate_enable', true ) ){
			update_user_meta( $vendor_id, 'pv_custom_commission_rate', $_POST['pv_custom_commission_rate'] );
		}

		update_user_meta( $vendor_id, 'pv_shop_description', $_POST['pv_shop_description'] );
		update_user_meta( $vendor_id, 'pv_seller_info', $_POST['pv_seller_info'] );
		update_user_meta( $vendor_id, 'wcv_give_vendor_tax', isset( $_POST['wcv_give_vendor_tax'] ) );
		update_user_meta( $vendor_id, 'wcv_give_vendor_shipping', isset( $_POST['wcv_give_vendor_shipping'] ) );

		// Bank details
		update_user_meta( $vendor_id, 'wcv_bank_account_name', $_POST['wcv_bank_account_name'] );
		update_user_meta( $vendor_id, 'wcv_bank_account_number', $_POST['wcv_bank_account_number'] );
		update_user_meta( $vendor_id, 'wcv_bank_name', $_POST['wcv_bank_name'] );
		update_user_meta( $vendor_id, 'wcv_bank_routing_number', $_POST['wcv_bank_routing_number'] );
		update_user_meta( $vendor_id, 'wcv_bank_iban', $_POST['wcv_bank_iban'] );
		update_user_meta( $vendor_id, 'wcv_bank_bic_swift', $_POST['wcv_bank_bic_swift'] );

		do_action( 'wcvendors_update_admin_user', $vendor_id );
	}


	/**
	 * Show the PayPal field and commision due table
	 *
	 * @param unknown $user
	 */
	public function show_extra_profile_fields( $user ) {

		if ( ! WCV_Vendors::is_vendor( $user->ID ) && ! WCV_Vendors::is_pending( $user->ID ) ) {
			return;
		}

		include apply_filters( 'wcvendors_vendor_meta_partial', WCV_ABSPATH_ADMIN . 'views/html-vendor-meta.php' );
	}

	/*
		Manage product columns on product page
	*/
	public function manage_product_columns( $columns ) {

		// Featured Product
		if ( 'yes' !== get_option( 'wcvendors_capability_product_featured', 'no' ) ) {
			unset( $columns['featured'] );
		}

		// SKU
		if ( wc_string_to_bool( get_option( 'wcvendors_capability_product_sku', 'no' ) ) ) {
			unset( $columns['sku'] );
		}

		return $columns;
	}

	/**
	 * Hide the virtual or downloadable product types if hidden in settings
	 *
	 * @param array $type_options - the product types
	 *
	 * @return void
	 *
	 * @since 2.1.1
	 */
	public static function check_allowed_product_type_options( $type_options ) {

		$product_types = get_option( 'wcvendors_capability_product_type_options', array() );

		foreach ( $product_types as $type ) {
			unset( $type_options[ $type ] );
		}

		return $type_options;
	}

	/**
	 * Add vendor shop column to users screen
	 *
	 * @since 2.1.10
	 * @version 2.1.10
	 */
	public function add_vendor_shop_column( $columns ){


		if ( array_key_exists( 'role', $_GET) && 'vendor' === $_GET['role'] ){
			$new_columns = array();
			foreach ( $columns as $key => $label ) {
				if ( $key === 'email' ){
					$new_columns['vendor'] = sprintf( __( '%s Store', 'wc-vendors' ), wcv_get_vendor_name() );
				}
				$new_columns[ $key ] = $label;
			}
			return $new_columns;
		}

		return $columns;
	}

	/**
	 * Add vendor shop column data to users screen
	 *
	 * @since 2.1.10
	 * @version 2.1.12
	 */
	public function add_vendor_shop_column_data( $custom_column, $column, $user_id ){

		if ( array_key_exists( 'role', $_GET) && 'vendor' === $_GET['role'] ){

			switch ( $column ) {
				case 'vendor':
					$shop_name 		= WCV_Vendors::get_vendor_sold_by( $user_id  );
					$display_name 	= empty( $shop_name ) ? get_the_author() : $shop_name;
					$store_url 		= WCV_Vendors::get_vendor_shop_page( $user_id );
					$target 		= apply_filters( 'wcv_users_view_store_url_target', 'target="_blank"' );
					$class 			= apply_filters( 'wcv_users_view_store_url_class', 'class=""' );
					return sprintf(
						'<a href="%s"%s%s>%s</a>',
						$store_url,
						$class,
						$target,
						$display_name );
					break;
				default:
					return $custom_column;
					break;
			}
		}

		return $custom_column;
	}

	/**
	 * Add new bulk action to users screen to set default role to vendor
	 *
	 * @since 2.1.10
	 * @version 2.1.10
	 */
	public function set_vendor_default_role( $actions ){
		$actions[ 'set_vendor_default_role' ] = sprintf( __( 'Set primary role to %s ', 'wc-vendors' ), wcv_get_vendor_name() );
		return $actions;
	}

	/**
	 * Process the bulk action for setting vendor default role
	 *
	 * @since 2.1.10
	 * @version 2.1.10
	 */
	public function handle_set_vendor_primary_role( $sendback, $action, $userids ){

		if ( 'set_vendor_default_role' == $action ){
			foreach ( $userids as $user_id ) {
				if ( WCV_Vendors::is_vendor( $user_id ) ){
					$user = new WP_User( $user_id );
					wcv_set_primary_vendor_role( $user );
				}
			}
		}

		return $sendback;
	}

}
