<?php
/**
 * Vendors Management page
 *
 * @package WCVendors/Admin
 */

namespace WCVendors\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class VendorsManagementTable extends \WP_List_Table {
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'vendor', 'wc-vendors' ),
				'plural'   => __( 'vendors', 'wc-vendors' ),
				'ajax'     => false,

			)
		);
	}

	/**
	 * Get all vendor role
	 *
	 * @since 3.0.0
	 * @version 1.0.0
	 * @return array
	 */
	private function get_vendor_roles() {
		return apply_filters( 'vendor_roles', array( 'vendor', 'pending_vendor' ) );
	}

	/**
	 *
	 * Get all vendor
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 * @param mixed
	 * @return array
	 */

	private function get_vendors( $page_number = 1, $per_page = 1 ) {
		global $wpdb;

		$offset        = ( $page_number - 1 ) * $per_page;
		$order         = isset( $_REQUEST['order'] ) ? trim( $_REQUEST['order'] ) : '';
		$orderby       = isset( $_REQUEST['orderby'] ) ? trim( $_REQUEST['orderby'] ) : '';
		$search_term   = isset( $_REQUEST['s'] ) ? trim( $_REQUEST['s'] ) : '';
		$vendor_status = isset( $_REQUEST['vendor_status'] ) ? ( $_REQUEST['vendor_status'] ) : '';

		$roles = $this::get_vendor_roles();

		if ( ! empty( $vendor_status ) ) {
			switch ( $vendor_status ) {
				case 'pending':
					$roles = array( 'pending_vendor' );
					break;

				case 'approved':
					$roles = array( 'vendor' );
					break;

				default:
					return $roles;
			}
		}

		$users_args = array(
			'role__in'    => $roles,
			'orderby'     => $orderby,
			'order'       => $order,
			'number'      => $per_page,
			'offset'      => $offset,
			'count_total' => true,

		);
		if ( ! empty( $search_term ) ) {
			$search_term_args = array(
				'search'         => $search_term,
				'search_columns' => array(
					'user_nicename',
					'user_email',
				),
				'meta_query'     => array(
					'relation' => 'OR',
					array(
						'meta_key'     => 'pv_shop_name',
						'meta_value'   => $search_term,
						'meta_compare' => 'LIKE',
					),
				),
			);

			$users_args = array_merge( $search_term_args, $users_args );

		}
		$vendors_arr = array();
		$vendors     = get_users( $users_args );
		foreach ( $vendors as $vendor ) {
			$vendors_arr[] = json_decode( json_encode( $vendor->data ), true );
		}
		return $vendors_arr;
	}

	/**
	 * Count all vendor
	 *
	 * @since 3.0.0
	 * @version 1.0.0
	 * @return number
	 */
	private function records_count() {
		$roles = $this::get_vendor_roles();
		$users = get_users(
			array(
				'role__in'    => $roles,
				'count_total' => true,
			)
		);
		return count( $users );
	}

	/**
	 * Prepare items for table
	 *
	 * @access public
	 * @since 3.0.0
	 * @version  1.0.0
	 * @return bool
	 */
	public function prepare_items() {

		$columns  = $this->get_columns();
		$hidden   = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();
		$this->process_bulk_action();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$per_page     = $this->get_items_per_page( 'vendor_per_page' );
		$current_page = $this->get_pagenum();
		$vendors      = $this->get_vendors( $current_page, $per_page );
		$total_items  = count( $vendors );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			)
		);
		$this->items = apply_filters( 'wcvendors_items', $vendors );
		return true;
	}

	/**
	 * Define html for bulk action checkbox
	 *
	 * @since 3.0.0
	 * @version 1.0.0
	 * @param  $item
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%s[]" value="%s" />',
			$this->_args['singular'],
			$item['ID']
		);
	}

	/**
	 * Define table columns header
	 *
	 * @since 3.0.0
	 * @version 1.0.0
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'              => '<input type="checkbox" />',
			'user_nicename'   => __( 'Name', 'wc-vendors' ),
			'user_email'      => __( 'Email', 'wc-vendors' ),
			'user_registered' => __( 'Registered Date', 'wc-vendors' ),
			'user_shop_name'  => __( 'Shop Name', 'wc-vendors' ),
			'vendor_enable'   => __( 'Vendor Enable', 'wc-vendors' ),
		);

		return $columns;
	}

	/**
	 * Assign data for each column
	 *
	 * @since 3.0.0
	 * @version 1.0.0
	 * @param  array  $item
	 * @param  string $columns_name
	 * @return string
	 */
	public function column_default( $item, $columns_name ) {
		$shop_name         = get_user_meta( $item['ID'], 'pv_shop_name', true );
		$is_vendor_enabled = get_user_meta( $item['ID'], 'is_vendor_enabled', true );
		switch ( $columns_name ) {
			case 'user_nicename':
			case 'user_email':
			case 'user_registered':
				return $item[ $columns_name ];
			case 'user_shop_name':
				return $shop_name;
			case 'vendor_enable':
				return '<label class="switch" for="vendor-id-' . $item['ID'] . '">
      <input value="' . $item['ID'] . '" id="vendor-id-' . $item['ID'] . '" class="vendor_enable_disable" type="checkbox" ' . checked( true, $is_vendor_enabled, false ) . '>
      <span class="slider round"></span>
      </label>';
			default:
				return apply_filters( 'wcvendors_no_item', 'No value' );
		}

	}

	/**
	 * Handle bulk action
	 *
	 * @since 3.0.0
	 * @version 1.0.0
	 * @return bool
	 */
	public function process_bulk_action() {

		if ( isset( $_REQUEST['_wpnonce'] ) && ! empty( $_REQUEST['_wpnonce'] ) ) {

			if ( isset( $_REQUEST['action2'] ) && isset( $_REQUEST['action'] ) && $_REQUEST['action'] !== '-1' ) {

				$nonce = filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING );

				if ( ! wp_verify_nonce( $nonce, 'bulk-' . $this->_args['plural'] ) ) {
					wp_die( 'Nope! Security check failed!' );
				}
				$vendor_ids = isset( $_REQUEST[ $this->_args['singular'] ] ) ? $_REQUEST[ $this->_args['singular'] ] : '';
				$action     = $this->current_action();

				if ( ! is_array( $vendor_ids ) ) {
					do_action( 'wcvendors_throw_message', 'Please select vendor for this action', 'warning' );
					return false;
				}

				switch ( $action ) {
					case 'disable':
						do_action( 'wcvendors_bulkdisable_vendor', $vendor_ids );
						break;
					case 'enable':
						do_action( 'wcvendors_bulkenable_vendor', $vendor_ids );
						break;
					case 'approve':
						do_action( 'wcvendors_bulkapprove_vendor', $vendor_ids );
						break;
					case 'deny':
						do_action( 'wcvendors_bulkdeny_vendor', $vendor_ids );
						break;
					case 'delete':
						do_action( 'wcvendors_bulkdelete_vendor', $vendor_ids );
						break;
				}
			}

			if ( ! isset( $_REQUEST['action2'] ) && isset( $_REQUEST['action'] ) ) {

				$nonce       = filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING );
				$action_name = 'vendor_action_nonce';

				if ( ! wp_verify_nonce( $nonce, $action_name ) ) {
					wp_die( 'Nope! Security check failed!' );
				}
				$action    = $this->current_action();
				$vendor_id = isset( $_REQUEST['vendor_id'] ) ? $_REQUEST['vendor_id'] : '';

				switch ( $action ) {

					case 'delete':
						do_action( 'wcvendors_delete', $vendor_id );

						break;

					case 'deny_vendor':
						do_action( 'wcvendors_deny', $vendor_id );
						break;

					case 'approve_vendor':
						do_action( 'wcvendors_approve', $vendor_id );
						break;
				}
			}
		}
		return true;

	}

	/**
	 * Define sorable columns
	 *
	 * @since 3.0.0
	 * @version 1.0.0
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'user_email'      => array( 'user_email', true ),
			'user_registered' => array( 'user_registered', true ),
			'user_shop_name'  => array( 'user_shop_name', true ),
		);

		return $sortable_columns;
	}

	/**
	 * Message if no vendor found
	 *
	 * @since 3.0.0
	 * @version 1.0.0
	 * @return string
	 */
	public function no_items() {
		_e( 'No vendor avaliable.', 'wc-vendors' );
	}

	/**
	 * Method for name column
	 *
	 * @since 3.0.0
	 * @version 1.0.0
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_user_nicename( $item ) {

		$action_nonce = wp_create_nonce( 'vendor_action_nonce' );
		$actions      = array(
			'delete'         => sprintf( '<a href="?page=%s&action=%s&vendor_id=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['ID'] ), $action_nonce ),
			'edit'           => sprintf( '<a href="%s?user_id=%s&">Edit</a>', admin_url() . 'user-edit.php', absint( $item['ID'] ) ),
			'approve_vendor' => sprintf( '<a href="?page=%s&action=%s&vendor_id=%s&_wpnonce=%s">Approve</a>', esc_attr( $_REQUEST['page'] ), 'approve_vendor', absint( $item['ID'] ), $action_nonce ),
			'deny_vendor'    => sprintf( '<a href="?page=%s&action=%s&vendor_id=%s&_wpnonce=%s">Deny</a>', esc_attr( $_REQUEST['page'] ), 'deny_vendor', absint( $item['ID'] ), $action_nonce ),
		);

		return $item['user_nicename'] . $this->row_actions( $actions );
	}

	/**
	 * Defines the hidden columns
	 *
	 * @access public
	 * @since 1.0.0
	 * @version 1.0.0
	 * @return array $columns
	 */
	public function get_hidden_columns() {
		// get user hidden columns
		$hidden = get_hidden_columns( $this->screen );

		$new_hidden = array();

		foreach ( $hidden as $k => $v ) {
			if ( ! empty( $v ) ) {
				$new_hidden[] = $v;
			}
		}

		return array_merge( array(), $new_hidden );
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @since 3.0.0 [<description>]
	 * @version 1.0.0 [<description>]
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'delete'  => 'Delete',
			'disable' => 'Disable',
			'enable'  => 'Enable',
			'approve' => 'Approve',
			'deny'    => 'Deny',
		);

		return $actions;
	}
	/**
	 * Gets the list of views available on this table.
	 *
	 * @since  3.0.0 [<description>]
	 * @version 1.0.0 [<description>]
	 * @return array of views
	 */
	public function get_views() {
		$page  = esc_attr( $_REQUEST['page'] );
		$views = array(
			'all'      => sprintf( '<li class="all"><a href="' . admin_url( 'admin.php?page=%s' ) . '">' . __( 'All', 'wc-vendors' ) . '</a></li>', $page ),
			'accepted' => sprintf( '<li class="all"><a href="' . admin_url( 'admin.php?page=%s&vendor_status=approved' ) . '">' . __( 'Approved Vendors', 'wc-vendors' ) . '</a></li>', $page ),
			'pendding' => sprintf( '<li class="all"><a href="' . admin_url( 'admin.php?page=%s&vendor_status=pending' ) . '">' . __( 'Pending Vendors', 'wc-vendors' ) . '</a></li>', $page ),
		);

		return $views;
	}
}
