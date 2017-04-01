<?php
/**
 *  Vendor Main Header - Hooked into archive-product page 
*
 *  THIS FILE WILL LOAD ON VENDORS STORE URLs (such as yourdomain.com/vendors/bobs-store/)
 *
 * @author WCVendors
 * @package WCVendors
 * @version 1.3.0
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 

/*
*	Template Variables available 
*   $vendor : 			For pulling additional user details from vendor account.  This is an array.
*   $vendor_id  : 		current vendor user id number
*   $shop_name : 		Store/Shop Name (From Vendor Dashboard Shop Settings)
*   $shop_location : 	shop locaiton map lat,long - modified by kas5986
*   $shop_description : Shop Description (completely sanitized) (From Vendor Dashboard Shop Settings)
*   $seller_info : 		Seller Info(From Vendor Dashboard Shop Settings)
*	$vendor_email :		Vendors email address
*	$vendor_login : 	Vendors user_login name
*	$vendor_shop_link : URL to the vendors store
*/ 
	if (!empty($shop_location)) {
		$location = explode(',', $shop_location);
		$shop_lat = $location[0];
		$shop_long = $location[1];
	}
	
/**
 * kas5986 - shop location modify...
 * Google map api - not getting any idea where to put this to show on both dashboard 
 * and shop page you can move this to  right place for now just to test is it working 
 */
wp_enqueue_script( 'google_map', 'https://maps.googleapis.com/maps/api/js?key='.WC_Vendors::$pv_options->get_option('vendor_shop_google_api').'&libraries=places&callback=initMap', array('jquery') );
	
?>


<h1><?php echo $shop_name; ?></h1>
<div class="wcv_shop_description">
    <div id="map" style="height: 120px;"></div>
    <script>

      function initMap() {
        var myLatLng = {lat: <?php echo $shop_lat;?>, lng: <?php echo $shop_long;?>};

        var map = new google.maps.Map(document.getElementById('map'), {
          zoom: 15,
          center: myLatLng
        });

        var marker = new google.maps.Marker({
          position: myLatLng,
          map: map,
          title: '<?php echo $shop_name; ?>'
        });
      }
    </script>
<?php echo $shop_description; ?>
</div>
