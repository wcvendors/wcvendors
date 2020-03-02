<?php
/**
 * Admin Users class file.
 *
 * @package     WCVendors/Admin
 * @version     3.0.0
 */

namespace WCVendors\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_User; 

/**
 * The admin class handles all user screen related functions for admin view.
 *
 * @version     3.0.0
 */
class Users {

	/**
	 * Init hooks
	 */
	public function init_hooks() {
		add_filter( 'user_row_actions', 			array( $this, 'user_row_actions' ), 10, 2 );
		add_filter( 'load-users.php', 				array( $this, 'user_row_actions_commit' ) );
		add_filter( 'bulk_actions-users',			array( $this, 'set_vendor_default_role' ) );
		add_filter( 'handle_bulk_actions-users', 	array( $this, 'handle_set_vendor_primary_role' ), 10, 3 );

	}

	

	/**
	 * Add actions to user row 
	 *
	 * @param array $actions user row actions 
	 * @param WP_User $user the user object
	 *
	 * @return unknown
	 */
	function user_row_actions( $actions, $user ) {

		if ( in_array( 'pending_vendor', $user->roles ) ) {
			$actions['approve_vendor'] = "<a href='?role=pending_vendor&action=approve_vendor&user_id=" . $user->ID . "'>" . __( 'Approve', 'wc-vendors' ) . '</a>';
			$actions['deny_vendor']    = "<a href='?role=pending_vendor&action=deny_vendor&user_id=" . $user->ID . "'>" . __( 'Deny', 'wc-vendors' ) . '</a>';
		}

		return $actions;
	}


	/**
	 * Process the approve and deny actions for the user screen
	 *
	 * @since 1.0.1
	 * @version 3.0.0 
	 */
	public function user_row_actions_commit() {

		if ( ! empty( $_GET['action'] ) && ! empty( $_GET['user_id'] ) ) {

			$wp_user_object = new WP_User( (int) $_GET['user_id'] );

			switch ( $_GET['action'] ) {
				case 'approve_vendor':
					// Remove the pending vendor role.
					$wp_user_object->remove_role( 'pending_vendor' );
					wcv_set_primary_vendor_role( $wp_user_object );
					add_action( 'admin_notices', array( $this, 'approved' ) );
					do_action( 'wcvendors_approve_vendor', $wp_user_object );
					break;

				case 'deny_vendor':
					$role = apply_filters( 'wcvendors_denied_vendor_role', get_option( 'default_role', 'subscriber' ) );
					$wp_user_object->remove_role( 'pending_vendor' );
					// Only add the default role if the user uas no other roles
					if ( empty( $wp_user_object->roles ) ){
						$wp_user_object->add_role( $role );
					}
					add_action( 'admin_notices', array( $this, 'denied' ) );
					do_action( 'wcvendors_deny_vendor', $wp_user_object );
					break;

				default:
					// code...
					break;
			}

		}
	}


	/**
	 * Output the denied vendor notice 
	 */
	public function denied() {
		echo '<div class="updated">';
		echo '<p>' . sprintf( __( '%s has been <b>denied</b>.', 'wc-vendors' ), wcv_get_vendor_name() ) . '</p>';
		echo '</div>';
	}


	/**
	 * Output the approved admin notice 
	 */
	public function approved() {
		echo '<div class="updated">';
		echo '<p>' . sprintf( __( '%s has been <b>approved</b>.', 'wc-vendors' ), wcv_get_vendor_name() ) . '</p>';
		echo '</div>';
	}

	/**
	 * Add vendor shop column to users screen
	 *
	 * @since 2.1.10
	 * @version 2.1.10
	 * @param array 
	 */
	public function add_vendor_shop_column( $columns ){


		if ( array_key_exists( 'role', $_GET ) && 'vendor' === $_GET['role'] ){
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
					$shop_name 		= wcv_get_vendor_sold_by( $user_id  );
					$display_name 	= wcv_get_vendor_display_name( $user_id ); 
					$store_url 		= wcv_get_vendor_shop_page( $user_id );
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
	 * 
	 * @param string $redirect_url The redirect URL.
	 * @param string $doaction     The action being taken.
	 * @param array  $items        The items to take the action on.
	 */
	public function handle_set_vendor_primary_role( $redirect_to, $action, $userids ){

		if ( 'set_vendor_default_role' == $action ){
			foreach ( $userids as $user_id ) {
				if ( wcv_is_vendor( $user_id ) ){
					$user = new WP_User( $user_id );
					wcv_set_primary_vendor_role( $user );
				}
			}
		}

		return $redirect_to;
	}

}