<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The vendor order class handles storage of a vendor order data. The data is stored in a custom post type 
 *
 * @author      Jamie Madden, WC Vendors
 * @category    Classes
 * @package     WCVendors/Classes
 * @version     2.0.0
 */
class WCVendors_Vendor_Order extends WC_Order {

	protected $order = null;

	/**
	 * Which data store to load. WC 3.0+ property.
	 *
	 * @var string
	 */
	protected $data_store_name = 'shop-order-vendor';

	/**
	 * This is the name of this object type. WC 3.0+ property.
	 *
	 * @var string
	 */
	protected $object_type = 'shop_order_vendor';

	/**
	 * Stores the $this->is_editable() returned value in memory
	 *
	 * @var bool
	 */
	private $editable;

	private $parent_order; 

	/**
	 * Extra data for this object. Name value pairs (name + default value).
	 *
	 * WC 3.0+ property.
	 *
	 * @var array
	 */
	protected $extra_data = array(

		// Extra data with getters/setters
		'vendor_id'				=> 0, 
		'commission'       		=> 0,
		'order_item_ids'		=> array(), 
	);

	/**
	 * Initialize the vendor order object.
	 *
	 * @param int|WCVendors_Vendor_Order $order
	 */
	public function __construct( $vendor_order = 0 ) {

		parent::__construct( $vendor_order );
		
		$this->order_type = 'shop_order_vendor';

		if ( is_numeric( $vendor_order ) && $vendor_order > 0 ) {
			$this->set_id( $vendor_order );
		} elseif ( $vendor_order instanceof self ) {
			$this->set_id( $vendor_order->get_id() );
		} elseif ( ! empty( $vendor_order->ID ) ) {
			$this->set_id( $vendor_order->ID );
		} else {
			$this->set_object_read( true );
		}

		$this->data_store = WC_Data_Store::load( $this->data_store_name );

		if ( $this->get_id() > 0 ) {
			$this->data_store->read( $this );
		}
	}

	/**
	 * @todo finish these to make the market place functionality complete. 
	 */
	// Shipping 
	// $this->set_shipping_total( WC()->cart->shipping_total );
	// $this->set_shipping_tax( WC()->cart->shipping_tax_total );

	// Coupons 
	// $this->set_discount_total( WC()->cart->get_cart_discount_total() );
	// $this->set_discount_tax( WC()->cart->get_cart_discount_tax_total() );

	/**
	 * @todo need to make these work for the sub orders where required. 
	 */
	// $this->create_order_fee_lines( $parent_order, WC()->cart );
	// $this->create_order_shipping_lines( $parent_order, WC()->session->get( 'chosen_shipping_methods' ), WC()->shipping->get_packages() );
	// $this->create_order_coupon_lines( $parent_order, WC()->cart );


	/**
	 * Getters 
	 */

	/**
	 * Get internal type.
	 *
	 * @return string
	 */
	public function get_type() {
		return 'shop_order_vendor';
	}
	

	/**
	 * Get vendor id 
	 */
	public function get_vendor_id( $context = 'view' ){ 
		return $this->get_prop( 'vendor_id', $context );
	}

	/**
	 * Get commission total 
	 */
	public function get_commission( $context = 'view' ){ 
		// Need to get the relevant commission object from the database for this. 
		return $this->get_prop( 'commission', $context );
	}

	public function get_parent_order( ){ 
		if ( !is_object( $this->parent_order ) ){ 
			$this->parent_order = new WC_Order( $this->get_parent_id() ); 
		}

		return $this->parent_order; 
	}

	/**
	 * Get items 
	 */
	public function get_items( $types = 'line_item' ){ 	

		$parent_items = $this->get_parent_order()->get_items(); 
		$items 			= array(); 

		foreach ( $parent_items as $key => $item ) {
			foreach ( $this->get_order_item_ids() as $order_item_id ) {
				if ( $item->get_id() == $order_item_id ){ 
					$items[ $key ]  = $item; 
				}
			}
		}

		return apply_filters( 'wcvendors_vendor_order_get_items', $items, $this ); 
	}
	
	/**
	 * Get the order item ids for this order 
	 */
	public function get_order_item_ids( $context = 'view' ){ 
		return $this->get_prop( 'order_item_ids', $context ); 
	}

	/**
	 * Setters 
	 */

	/**
	 * Set the parent order 
	 */
	public function set_parent_order( $order ){ 
		$this->parent_order = $order; 
		if ( $order instanceof WC_Order ){ 
			$this->create_parent_order_details(); 
		}
	}

	/**
	 * Anything that relys on the parent order to get data, set it here 
	 */
	private function create_parent_order_details(){ 

		// Get details from parent order, don't store details in child order? .. 
		$this->set_parent_id( $this->parent_order->get_id() );
		$this->set_created_via( $this->parent_order->get_created_via() );
		$this->set_cart_hash( $this->parent_order->get_cart_hash() );
		$this->set_customer_id( $this->parent_order->get_customer_id() );
		$this->set_currency( $this->parent_order->get_currency() );
		$this->set_prices_include_tax( $this->parent_order->get_prices_include_tax() );
		$this->set_customer_ip_address( $this->parent_order->get_customer_ip_address( ) );
		$this->set_customer_user_agent( $this->parent_order->get_customer_user_agent() );
		$this->set_customer_note( $this->parent_order->get_customer_note() );
		$this->set_payment_method( $this->parent_order->get_payment_method() );
	}

	/**
	 * Set extra order data information, same as parent order for now 
	 */
	public function set_data( $data ){ 
		foreach ( $data as $key => $value ) {
			if ( is_callable( array( __CLASS__, "set_{$key}" ) ) ) {
				$this->{"set_{$key}"}( $value );
			}
		}
	}

	/**
	 * Set parent order id 
	 */
	public function set_parent_id( $value ){ 
		$this->set_prop( 'parent_id', $value ); 
	}

	/**
	 * Set vendor id 
	 */
	public function set_vendor_id( $value ){ 
		$this->set_prop( 'vendor_id', $value );
	}

	/**
	 * Set commission 
	 */
	public function set_commission( $value ){ 
		$this->set_prop( 'commission', $value );
	}

	/**
	 * Set the order item ids for the order 
	 */
	public function set_order_item_ids( $value ){ 
		$this->set_prop( 'order_item_ids', $value ); 
	}
	
	/**
	 * Utils 
	 */
	
	/**
	 * Add the filtered order items to the order 
	 */
	public function add_items( $items ){ 
		$this->set_order_item_ids( array_keys( $items ) ); 
		
		foreach ( $items as $key => $item ) {
			$this->add_item( $item ); 
		}
	}


	/**
	 * Calculate the totals for the vendor order based on only the order items relevant to this vendor 
	 */
	public function calculate_totals( $and_taxes = true ){ 

		$total 		= 0; 
		$total_tax 	= 0; 	

		// Don't calculate anything if there are no items.
		if ( is_null( $this->get_items() ) ) return; 

		foreach ( $this->get_items() as $item )  {
			$total += $item->get_total(); 
			$total_tax += $item->get_total_tax(); 
		}

		$this->set_total( (float) $total ); 
		$this->set_total_tax( (float) $total_tax ); 

		$this->create_order_tax_line_item(); 
	}

	/**
	 * Create the tax lines for the vendor order 
	 */
	public function create_order_tax_line_item(){ 

		// short circuit the function if the parent id hasn't been set. 
		if ( ! $this->get_parent_id() ) return; 

		$parent_tax_items = $this->parent_order->get_items( 'tax' ); 

		foreach ( $parent_tax_items as $tax_item_id => $tax_item ) {
			$item = new WC_Order_Item_Tax();
			$item->set_rate( $tax_item->get_rate_id() ); 
			$item->set_rate_id( $tax_item->get_rate_id() ); 
			$item->set_tax_total( $this->get_total_tax() ); 
			// $item->set_shipping_tax_total( ); 
			$item->apply_changes(); 
			$this->add_item( $item ); 
		}
		
	}

	/**
	 * Conditionals 
	 */

	/**
	 * Get if the commission is paid
	 *
	 * @return bool
	 */
	public function is_commission_paid(){ 
		return $this->get_commission_paid(); 
	}
	
}