<?php 
namespace WCVendors\DataStores;

use Vendor; 
use WC_Data_Store_WP;
use WC_Object_Data_Store_Interface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Vendor extends WC_Data_Store_WP implements WC_Object_Data_Store_Interface {


    /**
	 * Method to create a new vendor in the database.
	 *
	 * @since 3.0.0
	 *
	 * @param Vendor $vendor Vendor object.
	 *
	 * @throws WC_Data_Exception If unable to create new vendor.
	 */
	public function create( &$vendor ) {

        $id = wcv_create_new_vendor( $vendor->get_email(), $vendor->get_username(), $vendor->get_password() );

		if ( is_wp_error( $id ) ) {
			throw new WC_Data_Exception( $id->get_error_code(), $id->get_error_message() );
		}

		$vendor->set_id( $id );
		$this->update_user_meta( $vendor );

    } 


    /**
	 * Method to read a vendor object.
	 *
	 * @since 3.0.0
	 * @param Vendor $vendor Vendor object.
	 * @throws Exception If invalid vendor.
	 */
	public function read( &$vendor ) {
		$user_object = $vendor->get_id() ? get_user_by( 'id', $vendor->get_id() ) : false;

		// User object is required.
		if ( ! $user_object || empty( $user_object->ID ) ) {
			throw new Exception( __( 'Invalid vendor.', 'wcvendors' ) );
		}

		$vendor_id = $vendor->get_id();

		$vendor->set_props( get_user_meta( $vendor_id ) );
		$vendor->set_props(
			array(
				'is_paying_vendor' => get_user_meta( $vendor_id, 'paying_vendor', true ),
				'email'              => $user_object->user_email,
				'username'           => $user_object->user_login,
				'display_name'       => $user_object->display_name,
				'date_created'       => $user_object->user_registered, // Mysql string in local format.
				'date_modified'      => get_user_meta( $vendor_id, 'wcv_last_update', true ),
				'role'               => ! empty( $user_object->roles[0] ) ? $user_object->roles[0] : 'vendor',
			)
		);
        $vendor->set_wp_user_object( $user_object ); 
		$vendor->read_meta_data();
		$vendor->set_object_read( true );
		do_action( 'wcvendors_vendor_loaded', $vendor );
	}


} 