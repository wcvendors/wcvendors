<?php 

namespace WCVendors\Abstracts; 

use WP_Query;


abstract class VendorFactory { 
     /**
     * The Vendor ID.
     *
     * @var integer
     */
    public $id = 0;

    /**
     * This is the meta key that all vendor store information is stored in the wp_users table. 
     */
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
	 * Default constructor.
	 *
	 * @param int|object|array $read ID to load from the DB (optional) or already queried data.
	 */
	public function __construct( $read = 0 ) {
		$this->data         = array_merge( $this->data, $this->extra_data );
		$this->default_data = $this->data;
	}

    

}