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
     * The WP_User user object. 
     *
     * @var null|WP_User
     */
    public $wp_user = null; 

    /**
     * The vendor store data.
     *
     * @var array
     */
    private $store_data = array(); 

    /**
     * Track the updates to the vendor data.
     *
     * @var array
     */
    private $updates = array();

    /**
     * Load the vendor data based on how Vendor is called. 
     * 
     * @since 3.0.0     
     * @param WP_User|int $vendor Vendor ID or data. 
     */
    public function __construct( $vendor = null ){

        if ( $vendor instanceof WP_User ){ 
            $this->set_id ( absint( $vendor->ID ) ); 
            $this->set_wp_user( $vendor );
        } elseif( is_numeric( $vendor ) ){
            // Load the user object and user meta 
            $wp_user = get_user_by( 'id', $vendor );

            if ( $user_data ){
                $this->set_id( $wp_user->ID );
                $this->set_wp_user( $wp_user ); 
            }
        }
        do_action( 'wcv_vendor_loaded', $this ); 
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
     * Is the vendor data populated?
     *
     * @return boolean
     */
    public function has_store_data(){ 

        if ( array_key_exists( $this->get_meta_key(), $this->get_wp_user_meta() ) && ! empty( $this->get_store_data() ) ){ 
            return true; 
        } else { 
            return false; 
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Getters
    |--------------------------------------------------------------------------
    */

    /**
     * Get the vendor store data.
     *
     * @return array $store_data Vendor store data array.
     */
    public function get_store_data(){

        if ( $this->wp_user->has_prop( $this->get_meta_key() ) ){ 
            return $this->wp_user->get( $this->get_meta_key() ); 
        }
    
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

    public function get_name(){}
    public function get_store_name(){}
    public function get_store_url(){}
    


    /*
    |--------------------------------------------------------------------------
    | Setters
    |--------------------------------------------------------------------------
    */
    
    /**
     * Set the ID.
     *
     * @since 3.0.0 
     * @param int $id ID.
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

