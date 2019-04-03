<?php
/**
 * Admin View: Custom Notices
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="updated wcvendors-message">
	<a class="wcvendors-message-close notice-dismiss"
	   href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wcv-hide-notice', $notice ), 'wcvendors_hide_notices_nonce', '_wcv_notice_nonce' ) ); ?>"><?php _e( 'Dismiss', 'wc-vendors' ); ?></a>
	<?php echo wp_kses_post( wpautop( $notice_html ) ); ?>
</div>
