<?php 
/*
*	Template Variables available 
*   $shop_name : pv_shop_name
*   $shop_description : pv_shop_description (completely sanitized)
*   $shop_link : the vendor shop link 
*   $vendor_id  : current vendor id for customization 
*/
?>

<li>
	<a href="<?php echo $shop_link; ?>" alt="<?php echo $shop_name; ?>" >
	    <figure>
	    	<?php echo get_avatar($vendor_id, 50); ?>
	    	<span><?php echo $shop_name; ?></span>
	    </figure>
	</a>
</li>