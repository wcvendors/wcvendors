<?php
/**
 * Admin View: Notice - Install
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="updated wcvendors-message wc-connect is-dismissible">
	<p><?php _e( '<strong>WC Vendors Update Required.</strong> &#8211; We need to upgrade your configuration to the latest version.', 'wc-vendors' ); ?></p>
	<p class="submit"><a class="wcv-update-now button-primary"
	                     href="<?php echo esc_url( add_query_arg( 'do_update_wcvendors', 'true', admin_url( 'admin.php?page=wcv-settings' ) ) ); ?>"
	                     class="button-primary"><?php _e( 'Run the update', 'wc-vendors' ); ?></a></p>
</div>
<script type="text/javascript">
	jQuery('.wcv-update-now').click('click', function () {
		return window.confirm('<?php echo esc_js( __( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?', 'wc-vendors' ) ); ?>'); // jshint ignore:line
	});
</script>
