<?php
/**
 * Admin View: Setup Footer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<?php if ( 'store_setup' === $this->step ) : ?>
		<a class="wcv-return-to-dashboard" href="<?php echo esc_url( admin_url() ); ?>"><?php esc_html_e( 'Exit Wizard', 'wcvendors' ); ?></a>
	<?php elseif ( 'ready' === $this->step ) : ?>
		<a class="wcv-return-to-dashboard" href="<?php echo esc_url( admin_url() .'admin.php?page=wc_prd_vendor' ); ?>"><?php esc_html_e( 'Return to your dashboard', 'wcvendors' ); ?></a>
	<?php elseif ( 'activate' === $this->step ) : ?>
		<a class="wcv-return-to-dashboard" href="<?php echo esc_url( $this->get_next_step_link() ); ?>"><?php esc_html_e( 'Skip this step', 'wcvendors' ); ?></a>
	<?php endif; ?>
	</body>
</html>
