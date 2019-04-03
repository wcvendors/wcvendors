<?php
/**
 * Admin View: Setup Footer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<?php if ( 'store_setup' === $this->step ) : ?>
	<a class="wcv-return-to-dashboard"
	   href="<?php echo esc_url( admin_url() ); ?>"><?php esc_html_e( 'Exit Wizard', 'wc-vendors' ); ?></a>
<?php elseif ( 'ready' === $this->step ) : ?>
	<a class="wcv-return-to-dashboard"
	   href="<?php echo esc_url( admin_url() . 'admin.php?page=wcv-settings' ); ?>"><?php esc_html_e( 'Return to your dashboard', 'wc-vendors' ); ?></a>
<?php elseif ( 'activate' === $this->step ) : ?>
	<a class="wcv-return-to-dashboard"
	   href="<?php echo esc_url( $this->get_next_step_link() ); ?>"><?php esc_html_e( 'Skip this step', 'wc-vendors' ); ?></a>
<?php endif; ?>
</body>
</html>
