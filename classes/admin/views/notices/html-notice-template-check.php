<?php
/**
 * Admin View: Notice - Template Check
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$theme = wp_get_theme();
?>
<div id="message" class="updated wcvendors-message">
	<a class="wcvendors-message-close notice-dismiss"
	   href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wcv-hide-notice', 'template_files' ), 'wcvendors_hide_notices_nonce', '_wcv_notice_nonce' ) ); ?>"><?php _e( 'Dismiss', 'wc-vendors' ); ?></a>

	<p><?php printf( __( '<strong>Your theme (%1$s) contains outdated copies of some WC Vendors template files.</strong> These files may need updating to ensure they are compatible with the current version of WC Vendors. You can see which files are affected from the <a href="%2$s">system status page</a>. If in doubt, check with the author of the theme.', 'wc-vendors' ), esc_html( $theme['Name'] ), esc_url( admin_url( 'admin.php?page=wc-status' ) ) ); ?></p>
	<p class="submit"><a class="button-primary"
	                     href="https://docs.wcvendors.com/knowledge-base/changing-the-vendor-templates/?utm_source=plugin"
	                     target="_blank"><?php _e( 'Learn more about templates', 'wc-vendors' ); ?></a></p>
</div>
