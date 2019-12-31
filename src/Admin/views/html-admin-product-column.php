<?php
/**
 * Output the hidden fields required for quick edit
 *
 * @author   Jamie Madden, WC Vendors
 * @version  2.0.0
 * @category Class
 */
?>
<div class="hidden" id="wcvendors_inline_<?php echo absint( $post_id ); ?>">
	<div class="vendor_id"><?php echo $vendor_id; ?></div>
	<div class="commission_rate"><?php echo $product->get_meta( '_wcv_commission_rate', true ); ?></div>
</div>
