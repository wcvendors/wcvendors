<?php
/**
 * Denied Template
 *
 * This template can be overridden by copying it to yourtheme/wc-vendors/dashboard/denied.php
 *
 * @author		Jamie Madden, WC Vendors
 * @package 	WCVendors/Templates/dashboard
 * @version 	2.0.0

 */

 if ( ! defined( 'ABSPATH' ) ) {
 	exit;
 }
?>

<?php wc_print_notices(); ?>

<?php if ( WCV_Vendors::is_pending( get_current_user_id() ) ) { ?>

	<p><?php _e( 'Your account has not yet been approved to become a vendor.  When it is, you will receive an email telling you that your account is approved!', 'wc-vendors' ); ?></p>

<?php } else { ?>

	<p><?php _e( 'Your account is not setup as a vendor.', 'wc-vendors' ); ?></p>

	<?php if ( 'yes' === get_option( 'wcvendors_vendor_allow_registration', 'no' ) ) { ?>
		<form method="POST" action="">
			<div class="clear"></div>
			<p class="form-row">
				<input class="input-checkbox"
					   id="apply_for_vendor" <?php checked( isset( $_POST[ 'apply_for_vendor' ] ), true ) ?>
					   type="checkbox" name="apply_for_vendor" value="1"/>
				<label for="apply_for_vendor"
					   class="checkbox"><?php echo apply_filters('wcvendors_vendor_registration_checkbox', __( 'Apply to become a vendor? ', 'wc-vendors' )); ?></label>
			</p>

			<div class="clear"></div>

			<?php 
			
			$terms_page = get_option( 'wcvendors_vendor_terms_page_id' );

			if ( $terms_page ) { ?>
				<p class="form-row agree-to-terms-container" style="display:none">
					<input class="input-checkbox"
						   id="agree_to_terms" <?php checked( isset( $_POST[ 'agree_to_terms' ] ), true ) ?>
						   type="checkbox" name="agree_to_terms" value="1"/>
					<label for="agree_to_terms"
						   class="checkbox"><?php printf( __( 'I have read and accepted the <a href="%s">terms and conditions</a>', 'wc-vendors' ), get_permalink( $terms_page ) ); ?></label>
				</p>

				<script type="text/javascript">
					jQuery(function () {
						if (jQuery('#apply_for_vendor').is(':checked')) {
							jQuery('.agree-to-terms-container').show();
						}

						jQuery('#apply_for_vendor').on('click', function () {
							jQuery('.agree-to-terms-container').slideToggle();
						});
					})
				</script>

				<div class="clear"></div>
			<?php } ?>

			<p class="form-row">
				<input type="submit" class="button" name="apply_for_vendor_submit"
					   value="<?php _e( 'Submit', 'wc-vendors' ); ?>"/>
			</p>
		</form>
	<?php } ?>

<?php } ?>

<br class="clear">
