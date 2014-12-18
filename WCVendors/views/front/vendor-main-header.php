<?php
/**
 *  Vendor Main Header - Hooked into archive-product page 
 *
 * @author WCVendors
 * @package WCVendors
 * @version 1.3.0
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 

/*
*	Template Variables available 
*   $shop_name : pv_shop_name
*   $shop_description : pv_shop_description (completely sanitized)
*   $vendor_id  : current vendor id for customization 
*/ 

?>

<h1><?php echo $shop_name; ?></h1>
<div class="wcv_shop_description">
<?php echo $shop_description; ?>
</div>
