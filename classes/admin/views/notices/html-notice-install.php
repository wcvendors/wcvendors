<?php
/**
 * Admin View: Notice - Install
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="updated wcvendors-message wc-connect">
	<p><?php _e( '<strong>Welcome to WC Vendors</strong> &#8211; You&lsquo;re almost ready to start your marketplace', 'wc-vendors' ); ?></p>
	<p class="submit"><a href="<?php echo esc_url( admin_url( 'admin.php?page=wcv-setup' ) ); ?>"
	                     class="button-primary"><?php _e( 'Run the Setup Wizard', 'wc-vendors' ); ?></a> <a
				class="button-secondary skip"
				href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wcv-hide-notice', 'install' ), 'wcvendors_hide_notices_nonce', '_wcv_notice_nonce' ) ); ?>"><?php _e( 'Skip setup', 'wc-vendors' ); ?></a>
	</p>
</div>
