<?php

/**
 *
 */
class WCV_Vendor_Applicants {

	function __construct() {

		add_filter( 'user_row_actions', array( $this, 'user_row_actions' ), 10, 2 );
		add_filter( 'load-users.php', array( $this, 'user_row_actions_commit' ) );
	}

	/**
	 *
	 *
	 * @param unknown $actions
	 * @param unknown $user_object
	 *
	 * @return unknown
	 */
	function user_row_actions( $actions, $user_object ) {

		if ( in_array( 'pending_vendor', $user_object->roles ) ) {
			$actions['approve_vendor'] = "<a href='?role=pending_vendor&action=approve_vendor&user_id=" . $user_object->ID . "'>" . __( 'Approve', 'cgc_ub' ) . '</a>';
			$actions['deny_vendor']    = "<a href='?role=pending_vendor&action=deny_vendor&user_id=" . $user_object->ID . "'>" . __( 'Deny', 'cgc_ub' ) . '</a>';
		}

		return $actions;
	}


	/**
	 * Process the approve and deny actions for the user screen
	 *
	 * @since 1.0.1
	 * @version 2.1.10
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
	 *
	 */
	public function denied() {

		echo '<div class="updated">';
		echo '<p>' . sprintf( __( '%s has been <b>denied</b>.', 'wc-vendors' ), wcv_get_vendor_name() ) . '</p>';
		echo '</div>';
	}


	/**
	 *
	 */
	public function approved() {

		echo '<div class="updated">';
		echo '<p>' . sprintf( __( '%s has been <b>approved</b>.', 'wc-vendors' ), wcv_get_vendor_name() ) . '</p>';
		echo '</div>';
	}


	/**
	 *
	 *
	 * @param unknown $values
	 *
	 * @return unknown
	 */
	public function show_pending_vendors_link( $values ) {

		$values['pending_vendors'] = '<a href="?role=asd">' . __( 'Pending Vendors', 'wc-vendors' ) . ' <span class="count">(3)</span></a>';

		return $values;
	}
}
