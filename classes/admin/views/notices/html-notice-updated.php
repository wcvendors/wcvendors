<?php
/**
 * Admin View: Notice - Updated
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="updated wcvendors-message wc-connect wcvendors-message-success">
	<a class="wcvendors-message-close notice-dismiss"
	   href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wcv-hide-notice', 'update', remove_query_arg( 'do_update_wcvendors' ) ), 'wcvendors_hide_notices_nonce', '_wcv_notice_nonce' ) ); ?>"><?php _e( 'Dismiss', 'wc-vendors' ); ?></a>
	<p><?php _e( 'WC Vendors data update complete. Thank you for updating to the latest version!', 'wc-vendors' ); ?></p>
</div>
