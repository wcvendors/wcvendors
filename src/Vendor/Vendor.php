<?php 

namespace WCVendors\Vendor; 
use WP_Query;

/**
 * The Vendor Object
 * 
 * @since 3.0.0
 */

 class Vendor {

    /**
     * The Vendor ID.
     *
     * @var integer
     */
    public $id = 0;

    public $meta_key = 'wcv_store_data'; 

    /**
     * The underlying WP_User user object. 
     *
     * @var null|WP_User
     */
    public $wp_user = null; 

    /**
	 * Stores if the vendor is enabled. 
	 *
	 * @var string
	 */
	protected $is_enabled;

    /**
     * The vendor store data.
     *
     * @var array
     */
    protected $store_data = array(

    ); 

    /**
     * Track the changes to the vendor data.
     *
     * @var array
     */
    private $changes = array();

    /**
     * Load the vendor data based on how Vendor is called. 
     * 
     * @since 3.0.0     
     * @param WP_User|int $vendor Vendor ID or data. 
     */
    public function __construct( $vendor = null ){

        if ( $vendor instanceof WP_User ){ 
            $this->set_id( absint( $vendor->ID ) ); 
            $this->set_wp_user( $vendor ); 
            $this->load_store_data(); 
        } elseif( is_numeric( $vendor ) ){
            // Load the user object and user meta 
            $wp_user = get_user_by( 'id', $vendor );

            if ( $wp_user ){
                $this->set_id( $wp_user->ID );
                $this->set_wp_user( $wp_user ); 
                $this->load_store_data(); 
            }
        }

        do_action( 'wcvendors_vendor_loaded', $this ); 
    }

    /** 
     * Provide a prefix for all hooks in the object 
     */
    public function get_hook_prefix(){ 
        return 'wcvendors_vendor_'; 
    }

    /**
     * Is this user a vendor.
     *
     * @return boolean
     */
    public function is_vendor(){ 
        return wcv_is_vendor( $this->id );
    }

    /**
     * Is the vendor enabled and able to sell.
     *
     * @return boolean
     */
    public function is_enabled(){
        return wcv_is_vendor_enabled( $this->id );
    }

    /**
     * Is the vendor verified 
     *
     * @return boolean
     */
    public function is_verified(){ 
        return wcv_is_vendor_verified( $this->id ); 
    }

    /**
     * Is the vendor trusted
     *
     * @return boolean
     */
    public function is_trusted(){ 
        return wcv_is_vendor_trusted( $this->id );
    }

    /**
     * Is the vendor untrusted.
     *
     * @return boolean
     */
    public function is_untrusted(){
        return wcv_is_vendor_untrusted( $this->id ); 
    }

    /**
     * Is the vendor trusted.
     *
     * @return boolean
     */
    public function is_featured(){
        return wcv_is_vendor_featured( $this->id ); 
    }

    /** 
     * Load the vendor store data from the database 
     */
    public function load_store_data(){ 

        // New Vendor, load defaults 
        if ( ! $this->id ){ 
            $this->store_info = wcv_vendor_store_info_defaults(); 
            return;
        } 

        $store_data = get_user_meta( $this->id, $this->meta_key, true ); 
        $store_data = is_array( $store_data ) ? $store_data : array(); 
        $store_data = wp_parse_args( $store_data, wcv_vendor_store_info_defaults() ); 

        $this->store_data = apply_filters( 'wcvendors_vendor_store_data', $store_data ); 

    }

    /**
     * Get a property from the object 
     *  
     * @since  3.0.0
	 * @param  string $prop Name of prop to get.
	 * @return mixed
	 */
    public function get_prop( $prop ){ 

        $value = null; 

        if ( array_key_exists( $prop, $this->store_data ) ) {
			$value = array_key_exists( $prop, $this->changes ) ? $this->changes[ $prop ] : $this->data[ $prop ];
		}

        return $value; 
    }

    /**
	 * Return data changes only.
	 *
	 * @since 3.0.0
	 * @return array
	 */
	public function get_changes() {
		return $this->changes;
	}

	/**
	 * Merge changes with data and clear.
	 *
	 * @since 3.0.0
	 */
	public function apply_changes() {
		$this->store_data  = array_replace_recursive( $this->store_data, $this->changes ); // @codingStandardsIgnoreLine
		$this->changes = array();
	}


    /**
	 * Save should create or update based on object existence.
	 *
	 * @since  3.0.0
	 * @return int
	 */
	public function save() {

		/**
		 * Trigger action before saving to the DB. Allows you to adjust object props before save.
		 *
		 * @param WC_Data          $this The object being saved.
		 * @param WC_Data_Store_WP $data_store THe data store persisting the data.
		 */
		do_action( $this->get_hook_prefix() . '_before_' . '_object_save', $this, $this->store_data );

        $this->apply_changes(); 


        

		/**
		 * Trigger action after saving to the DB.
		 *
		 * @param Vendor    $this The vendor object being saved.
		 * @param Array     $store_data THe store data 
		 */
		do_action( $this->get_hook_prefix() . '_after_' . '_object_save', $this, $this->store_data );

		return $this->get_id();
	}
    

    /*
    |--------------------------------------------------------------------------
    | Getters
    |--------------------------------------------------------------------------
    */

    /**
     * Get the vendor store data
     *
     * @return array $store_data Vendor store data array.
     */
    public function get_store_data(){
      
        if ( ! empty( $this->store_data ) ){ 
            return $this->store_data; 
        }

        $this->load_store_data(); 

        return $this->store_data; 

    }

    /**
     * Get the WP_User for the particular vendor
     */
    public function get_wp_user(){
        return $this->wp_user; 
    }

    /**
     * Get the vendors user email address from the WP_User object
     */
    public function get_user_email(){ 
        return $this->wp_user->email; 
    }

	/**
	 * Return this vendors's avatar.
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function get_avatar_url( ) {
		return get_avatar_url( $this->get_user_email() );
	}

    /**
     * Get the Vendors Name. 
     */
    public function get_name( ){
        return $this->get_prop( 'name', $context )
    }

 

    /**
	 * Return vendor store name. 
	 *
	 * @since  3.0.0
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
    public function get_store_name( ){
        return $this->get_prop( 'store_name' ); 
    }

    /**
     * Get payment part 
     */
    protected function get_payment_prop( $prop, $payment ){

        $value = null;

		if ( array_key_exists( $prop, $this->store_data[ $payment ] ) ) {
			$value = isset( $this->changes[ $payment ][ $prop ] ) ? $this->changes[ $payment ][ $prop ] : $this->data[ $payment ][ $prop ];

		}
		return $value;
    }
    
    /**
     * Get the vendor store URL permalink. 
     * 
     * @
     */
    public function get_store_url(){
        return wcv_get_storeurl( $this->id );
    }

    /**
     * Get the store info 
     */
    public function get_store_info(){
        return $this->get_prop( 'info' );
    }


    public function get_description( ){

    }

    /**
	 * Return vendors PayPal email address 
	 *
	 * @since  3.0.0
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
    public function get_paypal(){
        return $this->get_payment_prop( 'paypal' ); 
    }
    
    /**
	 * Return vendors bank details 
	 *
	 * @since  3.0.0
	 * @param  string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @return string
	 */
    public function get_bank_details(){
        return $this->get_payment_prop( $bank ); 
    }


    public function get_store_slug(){
        return $this->get_prop( 'slug' );

    }

     /**
     * Get vendor products.
     *
     * @return array $products Vendor Products. 
     */
    public function get_products(){ 
        $products = '';

        return $products;
    }

    /**
     * Get vendors orders. 
     * 
     * @since 3.0.0 
     * 
     * @return vendor_order objects 
     */
    public function get_orders( $args = [] ){
        $orders = '';

        return $orders;
    }


    /*
    |--------------------------------------------------------------------------
    | Setters
    |--------------------------------------------------------------------------
    */
    
    /**
     * Set the user ID
     *
     * @since 3.0.0 
     * @param int $id WP User ID.
     */
    public function set_id( $id ){ 
        $this->id = absint( $id ); 
    }

    /**
     * Set the WP_User object
     *
     * @since 3.0.0 
     * @param WP_User $wp_user The WP_User Object
     */
    public function set_wp_user( $wp_user ){ 
        $this->wp_user = $wp_user; 
    }

    /**
     * Set the store data.
     *
     * @param array $data The store data. 
     */
    public function set_store_data( $data ){ 
        if ( $this->wp_user->has_prop( $this->get_meta_key() ) ){
            $this->store_data = $this->wp_user->get( $this_>get_meta_key() ); 
        } else { 
            $this->store_data = wcv_vendor_store_defaults(); 
        }
    }
}

