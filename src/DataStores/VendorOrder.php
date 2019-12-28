<?php
namespace WCVendors\DataStores;

use WC_Order_Data_Store_CPT;
use WC_Order_Data_Store_Interface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vendor Order Data Store.
 *
 * @author      Jamie Madden, WC Vendors
 * @category    Data-Stores
 * @package     WCVendors/Data-Stores
 * @version     2.0.0
 */
class VendorOrder extends WC_Order_Data_Store_CPT implements Interfaces\VendorOrder, WC_Order_Data_Store_Interface {

	public function get_vendor_total( &$vendor_order ) {
		// TODO: Implement get_vendor_total() method.
	}

	/**
	 * Define internal meta keys related to the vendor order
	 *
	 * The meta keys here determine the prop data that needs to be manually set. We can't use
	 * the $internal_meta_keys property from WC_Order_Data_Store_CPT because we want its value
	 * too, so instead we create our own and merge it into $internal_meta_keys in __construct.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $vendor_order_internal_meta_keys = array(
		'_parent_id',
		'_vendor_id',
		'_order_item_ids',
	);

	/**
	 * Array of vendor specific data which augments the meta of an order in the form meta_key => prop_key
	 *
	 * Used to read/update props on the vendor order.
	 *
	 * @since 2.2.0
	 * @var array
	 */
	protected $vendor_order_meta_keys_to_props = array(
		'_parent_id'      => 'parent_id',
		'_vendor_id'      => 'vendor_id',
		'_order_item_ids' => 'order_item_ids',
	);

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->internal_meta_keys = array_merge( $this->internal_meta_keys, $this->vendor_order_internal_meta_keys );
	}

	/**
	 * Create the vendor order in the database
	 */
	public function create( &$vendor_order ) {

		$vendor_order->set_version( WCV_VERSION );
		$vendor_order->set_date_created( time() );
		$vendor_order->set_currency( $vendor_order->get_currency() ? $vendor_order->get_currency() : get_woocommerce_currency() );

		$id = wp_insert_post(
			apply_filters(
				'wcvendors_new_vendor_order_data',
				array(
					'post_date'     => gmdate( 'Y-m-d H:i:s', $vendor_order->get_date_created( 'edit' )->getOffsetTimestamp() ),
					'post_date_gmt' => gmdate( 'Y-m-d H:i:s', $vendor_order->get_date_created( 'edit' )->getTimestamp() ),
					'post_type'     => $vendor_order->get_type( 'edit' ),
					'post_status'   => 'wc-' . ( $vendor_order->get_parent_order()->get_status( 'edit' ) ? $vendor_order->get_parent_order()->get_status( 'edit' ) : apply_filters( 'woocommerce_default_order_status', 'pending' ) ),
					'ping_status'   => 'closed',
					'post_author'   => $vendor_order->get_vendor_id(),
					'post_title'    => $this->get_post_title(),
					'post_password' => uniqid( 'wcvendors_order_' ),
					'post_parent'   => $vendor_order->get_parent_id( 'edit' ),
					'post_excerpt'  => $this->get_post_excerpt( $vendor_order ),
				)
			),
			true
		);

		if ( $id && ! is_wp_error( $id ) ) {
			$vendor_order->set_id( $id );
			$this->update_post_meta( $vendor_order );
			$vendor_order->save_meta_data();
			$vendor_order->apply_changes();
			$this->clear_caches( $vendor_order );
		}

		do_action( 'wcvendors_new_vendor_order', $vendor_order->get_id() );
	}

	/**
	 * Method to read a vendor order from the data base
	 */
	public function read( &$vendor_order ) {

		$vendor_order->set_defaults();

		if ( ! $vendor_order->get_id() || ! ( $post_object = get_post( $vendor_order->get_id() ) ) || $post_object->post_type !== 'shop_order_vendor' ) {
			throw new Exception( __( 'Invalid vendor order.', 'wcvendors' ) );
		}

		$id = $vendor_order->get_id();
		$vendor_order->set_props(
			array(
				'vendor_id'     => $post_object->post_author,
				'parent_id'     => $post_object->post_parent,
				'date_created'  => 0 < $post_object->post_date_gmt ? wc_string_to_timestamp( $post_object->post_date_gmt ) : null,
				'date_modified' => 0 < $post_object->post_modified_gmt ? wc_string_to_timestamp( $post_object->post_modified_gmt ) : null,
				'status'        => $post_object->post_status,
			)
		);

		$this->read_order_data( $vendor_order, $post_object );
		$vendor_order->read_meta_data();
		$vendor_order->set_object_read( true );

	}

	/**
	 * Update the order
	 */
	public function update( &$vendor_order ) {
		parent::update( $vendor_order );
	}

	/**
	 * Delete the object this should only be called if the parent is deleted.
	 *
	 * @todo add check if parent is deleted then child orders are also deleted (if neeeded)
	 */
	public function delete( &$vendor_order, $args = array() ) {
		parent::delete( $vendor_order, $args );
	}

	/**
	 * Read order data. Can be overridden by child classes to load other props.
	 *
	 * @param WC_Order
	 * @param object   $post_object
	 * @since 1.0.0
	 */
	protected function read_order_data( &$vendor_order, $post_object ) {

		parent::read_order_data( $vendor_order, $post_object );

		$props_to_set = array();

		// Gets extra data associated with the vendor order if needed.
		foreach ( $vendor_order->get_extra_data_keys() as $key ) {
			$function = 'set_' . $key;
			if ( is_callable( array( $vendor_order, $function ) ) ) {
				$vendor_order->{$function}( get_post_meta( $vendor_order->get_id(), '_' . $key, true ) );
			}
		}

		foreach ( $this->vendor_order_meta_keys_to_props as $meta_key => $prop_key ) {

				$meta_value                = get_post_meta( $vendor_order->get_id(), $meta_key, true );
				$props_to_set[ $prop_key ] = $meta_value;
		}

		$vendor_order->set_props( $props_to_set );
	}

	/**
	 * Update post meta for a vendor order based on it's settings in the WCVendors_Order class.
	 *
	 * @param WCVendors_Vendor_order $vendor_order
	 * @since 1.0.0
	 */
	protected function update_post_meta( &$vendor_order ) {

		$updated_props = array();

		foreach ( $this->get_props_to_update( $vendor_order, $this->vendor_order_meta_keys_to_props ) as $meta_key => $prop ) {
			$meta_value = $vendor_order->{"get_$prop"}( 'edit' );

			update_post_meta( $vendor_order->get_id(), $meta_key, $meta_value );
			$updated_props[] = $prop;
		}

		do_action( 'wcvendors_vendor_order_object_updated_props', $vendor_order, $updated_props );

		parent::update_post_meta( $vendor_order );
	}


}
