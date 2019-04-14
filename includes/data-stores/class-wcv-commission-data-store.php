<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Commission Item Data Store.
 *
 * @author      Jamie Madden, WC Vendors
 * @category    Data-Stores
 * @package     WCVendors/Data-Stores
 * @version     2.0.0
 * 
 * Schema
 * 
 * commission_id
 * order_id
 * vendor_id
 * vendor_name
 * vendor_order_id
 * product_id
 * variation_id
 * order_item_id
 * product_qty
 * total_shipping
 * shipping_tax
 * tax
 * fees
 * total_due
 * status
 * commission_date
 * commission_rate
 * commission_fee
 * paid_date 
 * paid_status
 * paid_via
 */
class WCVendors_Commission_Data_Store_CPT extends WC_Data_Store_WP implements WCVendors_Commission_Data_Store_Interface, WC_Object_Data_Store_Interface {	


	/**
	 * Method to create a new commission 
	 */
	public function create( &$commission ){ 
		global $wpdb; 

		$wpdb->insert( $wpdb->prefix . 'wcvendors_commissions', array( 
			'order_id'			=> $commission->get_order_id(), 
			'vendor_id'			=> $commission->get_vendor_id(), 
			'vendor_name'		=> $commission->get_vendor_name(), 
			'vendor_order_id'	=> $commission->get_vendor_order_id(), 
			'product_id'		=> $commission->get_product_id(), 
			'variation_id'		=> $commission->get_variation_id(), 
			'order_item_id'		=> $commission->get_order_item_id(), 
			'product_qty'		=> $commission->get_product_qty(), 
			'total_shipping'	=> $commission->get_total_shipping(), 
			'shipping_tax'		=> $commission->get_shipping_tax(), 
			'tax'				=> $commission->get_tax(), 
			'fees'				=> $commission->get_fees(), 
			'total_due'			=> $commission->get_total_due(), 
			'status'			=> $commission->get_status(), 
			'commission_date'	=> $commission->get_commission_date(), 
			'commission_rate'	=> $commission->get_commission_rate(), 
			'commission_fee'	=> $commission->get_commission_fee(), 
			'paid_date'			=> $commission->get_paid_date(), 
			'paid_status'		=> $commission->get_paid_status(), 
			'paid_via'			=> $commission->get_paid_via(), 
		)); 

		$commission->set_id( $wpdb->insert_id ); 
		$commission->apply_changes(); 

		do_action( 'wcvendors_commission_created', $commission ); 

	}

	/**
	 * Method to read a commission from the database 
	 */
	public function read( &$commission ){ 
		global $wpdb; 

		if ( $commission_data = $wbdp->get_row( $wpdb->prepare( 
			"SELECT 'order_id ', 'vendor_id ', 'vendor_name', 'vendor_order_id', 'product_id', 'variation_id', 'order_item_id', 'product_qty', 'total_shipping', 'shipping_tax', 'tax', 'fees', 'total_due', 'status', 'commission_date', 'commission_rate', 'commission_fee', 'paid_date ', 'paid_status', 'paid_via ',
			FROM {$wpdb->prefix}wcvendors_commissions WHERE commission_id = %d LIMIT 1", $commission->get_id() ) ) ){ 
 			
			$commission->set_order_id( $commission_data->order_id ); 
			$commission->set_vendor_id( $commission_data->vendor_id ); 
			$commission->set_vendor_name( $commission_data->vendor_name ); 
			$commission->set_vendor_order_id( $commission_data->vendor_order_id ); 
			$commission->set_product_id( $commission_data->product_id ); 
			$commission->set_variation_id( $commission_data->variation_id ); 
			$commission->set_order_item_id( $commission_data->order_item_id ); 
			$commission->set_product_qty( $commission_data->product_qty ); 
			$commission->set_total_shipping( $commission_data->total_shipping ); 
			$commission->set_shipping_tax( $commission_data->shipping_tax ); 
			$commission->set_tax( $commission_data->tax ); 
			$commission->set_fees( $commission_data->fees ); 
			$commission->set_total_due( $commission_data->total_due ); 
			$commission->set_status( $commission_data->status ); 
			$commission->set_commission_date( $commission_data->commission_date ); 
			$commission->set_commission_rate( $commission_data->commission_rate ); 
			$commission->set_commission_fee( $commission_data->commission_fee ); 
			$commission->set_paid_date( $commission_data->paid_date ); 
			$commission->set_paid_status( $commission_data->paid_status ); 
			$commission->set_paid_via( $commission_data->paid_via ); 

			$commission->read_meta_data(); 
 			$commission->set_object_read( true ); 

 			do_action( 'wcvendors_commission_loaded', $commission ); 

		} else { 
			throw new Exception( __( 'Invalid data store.', 'wcvendors' ) ); 
		}

	}

	public function update( &$commission ){ 

		global $wpdb; 

		if ( $commission->get_id() ){ 
			$wpdb->update( $wpdb->prefix . 'wcvendors_commissions', array( 
				'order_id'			=> $commission->get_order_id(), 
				'vendor_id'			=> $commission->get_vendor_id(), 
				'vendor_name'		=> $commission->get_vendor_name(), 
				'vendor_order_id'	=> $commission->get_vendor_order_id(), 
				'product_id'		=> $commission->get_product_id(), 
				'variation_id'		=> $commission->get_variation_id(), 
				'order_item_id'		=> $commission->get_order_item_id(), 
				'product_qty'		=> $commission->get_product_qty(), 
				'total_shipping'	=> $commission->get_total_shipping(), 
				'shipping_tax'		=> $commission->get_shipping_tax(), 
				'tax'				=> $commission->get_tax(), 
				'fees'				=> $commission->get_fees(), 
				'total_due'			=> $commission->get_total_due(), 
				'status'			=> $commission->get_status(), 
				'commission_date'	=> $commission->get_commission_date(), 
				'commission_rate'	=> $commission->get_commission_rate(), 
				'commission_fee'	=> $commission->get_commission_fee(), 
				'paid_date'			=> $commission->get_paid_date(), 
				'paid_status'		=> $commission->get_paid_status(), 
				'paid_via'			=> $commission->get_paid_via(), 
			), 
				array( 'id' 	=> $commission->get_id() ) 
			); 

			$commission->apply_changes(); 

			do_action( 'wcvendors_commission_updated', $commission ); 

		}
		
	}

	/**
	 * Deletes a commission from the database including the commission items 
	 */
	public function delete( &$commission, $args = array() ){ 

		if ( $commission->get_id() ){ 
			global $wpdb; 
			$wpdb->delete( $wpdb->prefix . 'wcvendors_commissions', array( 'id' => $commission->get_id() ) ); 

			$id = $commission->get_id(); 
			$commission->set_id( null ); 

			do_action( 'wcvendors_commission_delete', $id ); 

		}

	}

	/**
	 * Get all commission data based on status 
	 */
	public function get_commissions( $status = '', $limit = '', $offset = '' ){ 
		global $wpdb; 

		$commissions_data 		= array(); 
		$commissions 			= array(); 

		$commission_sql = "SELECT `id`, `order_date`, `order_item_ids`, `vendor_order_id`, `vendor_id`, `vendor_name`, `commission`, `tax`, `fees`, `shipping`, `total_due`, `status`, `paid_date` FROM {$wpdb->prefix}wcvendors_commissions "; 

		if ( $status != '' ){ 
			$commission_sql .= "WHERE status = %s"; 
			$commissions_data = $wpdb->get_results( $wpdb->prepare( $commission_sql, $status ) ); 	
		} else { 
			$commissions_data = $wpdb->get_results( $commission_sql ); 
		}

		foreach ( $commissions_data as $commission_data ) {
				
			$commission = new WCVendors_Commission(); 
			$commission->set_id( $commission_data->id ); 
			$commission->set_order_date( $commission_data->order_date ); 
			$commission->set_order_item_ids( maybe_unserialize( $commission_data->order_item_ids ) );
			$commission->set_vendor_order_id( $commission_data->vendor_order_id ); 
			$commission->set_vendor_id( $commission_data->vendor_id ); 
			$commission->set_vendor_name( $commission_data->vendor_name ); 
			$commission->set_commission( $commission_data->commission ); 
			$commission->set_tax( $commission_data->tax ); 
			$commission->set_fees( $commission_data->fees ); 
			$commission->set_shipping( $commission_data->shipping ); 
			$commission->set_total_due( $commission_data->total_due );  
			$commission->set_paid_date( $commission_data->paid_date ); 
			$commission->set_status( $commission_data->status ); 
			$commission->apply_changes(); 
			$commission->read_meta_data(); 
 			$commission->set_object_read( true ); 

			$commissions[ $commission_data->id ] = $commission; 
		}

		return $commissions; 
	}

	/**
	 * Get all commission data based on status 
	 */
	public function get_vendor_commissions( $vendor_id, $status = '', $limit = '', $offset = '' ){ 
		global $wpdb; 

		$commissions_data 		= array(); 
		$vendor_commissions 	= array(); 

		$commission_sql = "SELECT `id`, `order_date`, `order_item_ids`, `vendor_order_id`, `vendor_id`, `vendor_name`, `commission`, `tax`, `fees`, `shipping`, `total_due`, `status`, `paid_date` FROM {$wpdb->prefix}wcvendors_commissions WHERE vendor_id = %d "; 

		if ( $status != '' ){ 
			$commission_sql .= "AND status = %s"; 
			$commissions_data = $wpdb->get_results( $wpdb->prepare( $commission_sql, $vendor_id, $status ) ); 	
		} else { 
			$commissions_data = $wpdb->get_results( $wpdb->prepare( $commission_sql, $vendor_id ) ); 
		}

		foreach ( $commissions_data as $commission_data ) {
				
			$commission = new WCVendors_Commission(); 
			$commission->set_id( $commission_data->id ); 
			$commission->set_order_date( $commission_data->order_date ); 
			$commission->set_order_item_ids( maybe_unserialize( $commission_data->order_item_ids ) );
			$commission->set_vendor_order_id( $commission_data->vendor_order_id ); 
			$commission->set_vendor_id( $commission_data->vendor_id ); 
			$commission->set_vendor_name( $commission_data->vendor_name ); 
			$commission->set_commission( $commission_data->commission ); 
			$commission->set_tax( $commission_data->tax ); 
			$commission->set_fees( $commission_data->fees ); 
			$commission->set_shipping( $commission_data->shipping ); 
			$commission->set_total_due( $commission_data->total_due );  
			$commission->set_paid_date( $commission_data->paid_date ); 
			$commission->set_status( $commission_data->status ); 
			$commission->apply_changes(); 
			$commission->read_meta_data(); 
 			$commission->set_object_read( true ); 

			$vendor_commissions[ $commission_data->id ] = $commission; 
		}

		return $vendor_commissions; 
	}

	/**	
	*	Get all due commissions 
	*/ 
	public function get_due_commissions( $limit = '', $offset = '' ) { 
		$due_commissions = $this->get_commissions( 'due', $limit, $offset ); 
		return $due_commissions; 
	}

	/**	
	* Get all paid commissions 
	*/ 
	public function get_paid_commissions( $limit = '', $offset = '' ){ 
		$paid_commissions = $this->get_commissions( 'paid', $limit, $offset ); 
		return $paid_commissions; 
	}

	/**
	*	Get all void commissions 
	*/ 
	public function get_void_commissions( $limit = '', $offset = '' ){ 
		$void_commissions = $this->get_commissions( 'paid', $limit, $offset ); 
		return $void_commissions; 
	}
}