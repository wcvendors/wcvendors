<?php
/**
 * Class VendorHandler
 * Handle Vendor Table Action
 *
 * @package WCVendors
 */

namespace WCVendors;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_User;

/**
 * VendorHandler, Handle action for Vendors list
 */
class VendorHandler {

	/**
	 * Init all hook for vendor action
	 *
	 * @since 3.0.0
	 * @version 1.0.0
	 */
	public function init_hooks() {
		add_action( 'wcvendors_throw_message', array( $this, 'throw_message' ), 10, 2 );
		add_action( 'wp_ajax_enable_vendor', array( $this, 'toogle_enable' ) );
		add_action( 'wcvendors_approve', array( $this, 'approve' ), 10, 1 );
		add_action( 'wcvendors_deny', array( $this, 'deny' ), 10, 1 );
		add_action( 'wcvendors_delete', array( $this, 'delete' ), 10, 1 );
		add_action( 'wcvendors_bulkdeny_vendor', array( $this, 'bulk_deny' ), 10, 1 );
		add_action( 'wcvendors_bulkapprove_vendor', array( $this, 'bulk_approve' ), 10, 1 );
		add_action( 'wcvendors_bulkdisable_vendor', array( $this, 'bulk_disable' ), 10, 1 );
		add_action( 'wcvendors_bulkenable_vendor', array( $this, 'bulk_enable' ), 10, 1 );
		add_action( 'wcvendors_bulkdelete_vendor', array( $this, 'bulk_delete' ), 10, 1 );
	}

	/**
	 * Delete single vendor
	 *
	 * @param Vendor $vendor_id Vendor ids.
	 * @since 3.0.0
	 * @version 1.0.0
	 * @return bool
	 */
	public function delete( $vendor_id ) {
		if ( $this->user_id_exists( $vendor_id ) ) {
			wp_delete_user( $vendor_id, 1 );
		}
		return true;
	}

	/**
	 * Handle bulk delete vendor
	 *
	 * @param Vendors $vendor_ids Vendor ids.
	 * @since 3.0.0
	 * @version 1.0.0
	 * @return bool
	 */
	public function bulk_delete( $vendor_ids ) {
		foreach ( $vendor_ids as $vendor_id ) {
			$this->delete( $vendor_id );
		}
		$this->throw_message( __( 'Deleted all selected user', 'wc-vendors' ), 'success' );
		return true;
	}

	/**
	 * Deny single vendor
	 *
	 * @param Vendor $vendor_id Vendor ID.
	 * @since 3.0.0
	 * @version 1.0.0
	 * @return bool
	 */
	public function deny( $vendor_id ) {
		$user      = new WP_User( $vendor_id );
		$user_role = $user->roles;
		if ( in_array( 'pending_vendor', $user_role, true ) ) {
			$user->remove_role( 'pending_vendor' );
			$user->set_role( 'subscriber' );
			$this->throw_message( /* translators: Notice deny success. */ sprintf( __( 'Denied %s', 'wc-vendors' ), $user->user_nicename ), 'success' );
			return true;
		}

		$this->throw_message( /* translators: Notice deny error. */ sprintf( __( 'Cannot deny %s', 'wc-vendors' ), $user->user_nicename ), 'error' );
		return false;
	}

	/**
	 * Handle bulk delete vendor
	 *
	 * @param Vendors $vendor_ids Vendor IDs.
	 * @since 3.0.0
	 * @version 1.0.0
	 * @return bool
	 */
	public function bulk_deny( $vendor_ids ) {
		$users_args = array(
			'include' => $vendor_ids,
		);
		$users      = get_users( $users_args );

		foreach ( $users as $user ) {
			if ( in_array( 'pending_vendor', (array) $user->roles, true ) ) {
				$user->remove_role( 'pending_vendor' );
				$user->set_role( 'subscriber' );
			}
		}
		$this->throw_message( __( 'Denied all selected vendor', 'wc-vendors' ), 'success' );
		return true;
	}
	/**
	 * Approve single vendor
	 *
	 * @param Vendor $vendor_id Vendor ID.
	 * @since 3.0.0
	 * @version 1.0.0
	 * @return bool
	 */
	public function approve( $vendor_id ) {
		$user      = new WP_User( $vendor_id );
		$user_role = $user->roles;

		if ( in_array( 'pending_vendor', $user_role, true ) ) {
			$user->remove_role( 'pending_vendor' );
			$user->set_role( 'vendor' );
			$this->throw_message( /* translators: Notice approve success. */ sprintf( __( 'Approved %s', 'wc-vendors' ), $user->user_nicename ), 'success' );
			return true;
		}

		$this->throw_message( /* translators: Notice approve success. */ sprintf( __( 'Cannot approve %s', 'wc-vendors' ), $user->user_nicename ), 'error' );
		return false;
	}
	/**
	 * Hanlde bulk approve vendor
	 *
	 * @param Vendors $vendor_ids Vendor IDs.
	 * @since 3.0.0
	 * @version 1.0.0
	 * @return bool
	 */
	public function bulk_approve( $vendor_ids ) {
		$users_args = array(
			'include' => $vendor_ids,
		);
		$users      = get_users( $users_args );

		foreach ( $users as $user ) {
			if ( in_array( 'pending_vendor', (array) $user->roles, true ) ) {
				$user->remove_role( 'pending_vendor' );
				$user->set_role( 'vendor' );
			}
		}
		$this->throw_message( __( 'Approved all selected vendor', 'wc-vendors' ), 'success' );
		return true;
	}

	/**
	 * Disable single vendor
	 *
	 * @param Vendor $vendor_id Vendor ID.
	 * @since 3.0.0
	 * @version 1.0.0
	 * @return bool
	 */
	public function disable( $vendor_id ) {
		$is_vendor_enabled = get_user_meta( absint( $vendor_id ), 'is_vendor_enabled', true );

		if ( $is_vendor_enabled ) {
			$disabled = update_user_meta( absint( $vendor_id ), 'is_vendor_enabled', false );
			if ( $disabled || is_integer( $disabled ) ) {
				return false;
			}
		}
	}

	/**
	 * Handle bulk disable vendor
	 *
	 * @param Vendors $vendor_ids Vendor IDs.
	 * @since 3.0.0
	 * @version 1.0.0
	 * @return bool
	 */
	public function bulk_disable( $vendor_ids ) {
		foreach ( $vendor_ids as $id ) {
			$this->disable( $id );
		}
		$this->throw_message( __( 'Disable all selected vendor', 'wc-vendors' ), 'success' );
		return true;
	}

	/**
	 * Disable or enable vendor ajax
	 *
	 * @since 3.0.0
	 * @version 1.0.0
	 */
	public function toogle_enable() {
		if ( ! check_ajax_referer( 'wcv_toogle_vendor_nonce', 'security', false ) ) {
			wp_send_json_error( 'Invalid security token sent.' );
			wp_die();
		}

		$vendor_id         = isset( $_REQUEST['vendor_id'] ) ? absint( $_REQUEST['vendor_id'] ) : '';
		$is_vendor_enabled = get_user_meta( $vendor_id, 'is_vendor_enabled', true );

		$result = false;

		if ( ! $is_vendor_enabled ) {
			$result = $this->enable( $vendor_id );
		} else {
			$result = $this->disable( $vendor_id );
		}

		echo esc_html( $result );

		wp_die();
	}

	/**
	 * Enable single vendor
	 *
	 * @param Vendor $vendor_id Vendor ID.
	 * @since 3.0.0
	 * @version 1.0.0
	 * @return bool
	 */
	public function enable( $vendor_id ) {
		$is_vendor_enabled = get_user_meta( absint( $vendor_id ), 'is_vendor_enabled', true );

		if ( ! $is_vendor_enabled ) {
			$updated = update_user_meta( absint( $vendor_id ), 'is_vendor_enabled', true );
			if ( ! $updated || is_integer( $updated ) ) {
				return true;
			}
		}
		return false;
	}
	/**
	 * Handle bulk enable vendor
	 *
	 * @param Vendors $vendor_ids .
	 * @since 3.0.0
	 * @version 1.0.0
	 * @return bool
	 */
	public function bulk_enable( $vendor_ids ) {
		foreach ( $vendor_ids as $id ) {
			$this->enable( $id );
		}
		$this->throw_message( __( 'Enable all selected vendor', 'wc-vendors' ), 'success' );
		return true;
	}

	/**
	 * Display message
	 *
	 * @param String $message message to show.
	 * @param String $type type of WordPress notice.
	 * @since 3.0.0
	 * @version 1.0.0
	 */
	public function throw_message( $message = '', $type = 'success' ) {
		echo sprintf( '<div class="notice notice-%s is-dismissible"><p><strong>%s</strong></p></div>', esc_attr( $type ), esc_html( $message ) );
	}

	/**
	 * Check if user exists
	 *
	 * @param Interger $user_id id of user.
	 * @since 3.0.0
	 * @version 1.0.0
	 * @return bool
	 */
	public function user_id_exists( $user_id ) {
		global $wpdb;
		$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM $wpdb->users WHERE ID = %d", $user_id ) );
		return empty( $count ) || 1 > $count ? false : true;
	}
}
