<?php
/**
 * Add some content to the help tab
 *
 * @package     WC Vendors/Admin
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'WCVendors_Admin_Help', false ) ) {
	return new WCVendors_Admin_Help();
}

/**
 * WCVendors_Admin_Help Class.
 */
class WCVendors_Admin_Help {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {

		add_action( 'current_screen', array( $this, 'add_tabs' ), 99 );
	}

	/**
	 * Add help tabs.
	 */
	public function add_tabs() {

		$screen = get_current_screen();

		if ( ! $screen || ! in_array( $screen->id, wcv_get_screen_ids() ) ) {
			return;
		}

		// Rquired to unhook woocommerce tabs on these pages due to adding wc vendors pages into the wc_get_screen_ids to load woocommerce assets
		$screen->remove_help_tab( 'woocommerce_support_tab' );
		$screen->remove_help_tab( 'woocommerce_bugs_tab' );
		$screen->remove_help_tab( 'woocommerce_education_tab' );
		$screen->remove_help_tab( 'woocommerce_onboard_tab' );

		$screen->add_help_tab(
			array(
				'id'      => 'wcv_support_tab',
				'title'   => __( 'Help &amp; Support', 'wc-vendors' ),
				'content' =>
					'<h2>' . __( 'Welcome to WC Vendors Help &amp; Support', 'wc-vendors' ) . '</h2>' .
					'<p>' . sprintf(
					/* translators: %s: Documentation URL */
						__( 'Should you need any help with using or extending WC Vendors, <a href="%s">please read our documentation</a>. You will find all kinds of resources including code snippets, guides and much more.', 'wc-vendors' ),
						'https://docs.wcvendors.com/?utm_source=plugin&utm_medium=help&utm_campaign=settings'
					) . '</p>' .
					'<p>' . sprintf(
					/* translators: %s: Forum URL */
						__( 'Do you have a question about WC Vendors? For assistance please see our <a href="%1$s">community forum</a>. If you need help with premium extensions sold by WC Vendors, please <a href="%2$s">submit a ticket</a>.', 'wc-vendors' ),
						'https://wordpress.org/support/plugin/wc-vendors',
						'https://www.wcvendors.com/submit-ticket/?utm_source=plugin&utm_medium=help&utm_campaign=pluginsettings'
					) . '</p>' .
					'<p>' . __( 'Before asking for help we recommend checking the system status page to identify any problems with your configuration. <strong>Anything showing red should be fixed before contacting us.</strong>', 'wc-vendors' ) . '</p>' .
					'<p>' . sprintf( __( 'Please follow our <a href="%s">debuggin guide</a> to ensure that you have narrowed down the issue. ', 'wc-vendors' ), 'https://docs.wcvendors.com/knowledge-base/the-debugging-guide/?utm_source=plugin&utm_medium=help&utm_campaign=settings' ) .
					'<p><a href="' . admin_url( 'admin.php?page=wc-status' ) . '" class="button button-primary">' . __( 'System status', 'wc-vendors' ) . '</a> <a href="https://wordpress.org/support/plugin/wc-vendors" class="button">' . __( 'Community forum', 'wc-vendors' ) . '</a> <a href="https://www.wcvendors.com/submit-ticket/?utm_source=plugin&utm_medium=help&utm_campaign=pluginsettings" class="button">' . __( 'WC Vendors Premium Support', 'wc-vendors' ) . '</a></p>',
			)
		);

		$screen->add_help_tab(
			array(
				'id'      => 'wcvendors_getting_started_tab',
				'title'   => __( 'Getting Started', 'wc-vendors' ),
				'content' =>
					'<h2>' . __( 'Getting Started', 'wc-vendors' ) . '</h2>' .
					'<p>' . sprintf( __( 'If you are new to WC Vendors then we highly recommend that you go through our <a href="%s">getting started guides</a>.', 'wc-vendors' ), 'https://docs.wcvendors.com/article-categories/getting-started/' ) . '</p>',
			)
		);

		$screen->add_help_tab(
			array(
				'id'      => 'wcvendors_bugs_tab',
				'title'   => __( 'Found a bug?', 'wc-vendors' ),
				'content' =>
					'<h2>' . __( 'Found a bug?', 'wc-vendors' ) . '</h2>' .
					/* translators: 1: GitHub issues URL 2: System status report URL */
					'<p>' . sprintf( __( 'If you think you have found a bug in WC Vendors you can create a ticket via <a href="%1$s">Github issues</a>. To help us solve your issue, please be as descriptive as possible and include your <a href="%2$s">system status report</a>.', 'wc-vendors' ), 'https://github.com/wcvendors/wcvendors/issues?state=open', admin_url( 'admin.php?page=wc-status' ) ) . '</p>' .
					'<p><a href="https://github.com/wcvendors/wcvendors/issues?state=open" class="button button-primary">' . __( 'Report a bug', 'wc-vendors' ) . '</a> <a href="' . admin_url( 'admin.php?page=wc-status' ) . '" class="button">' . __( 'System status', 'wc-vendors' ) . '</a></p>',

			)
		);

		$screen->add_help_tab(
			array(
				'id'      => 'wcvendors_onboard_tab',
				'title'   => __( 'Setup wizard', 'wc-vendors' ),
				'content' =>
					'<h2>' . __( 'Setup wizard', 'wc-vendors' ) . '</h2>' .
					'<p>' . __( 'If you need to access the setup wizard again, please click on the button below.', 'wc-vendors' ) . '</p>' .
					'<p><a href="' . admin_url( 'index.php?page=wcv-setup' ) . '" class="button button-primary">' . __( 'Setup wizard', 'wc-vendors' ) . '</a></p>',

			)
		);

		$screen->add_help_tab(
			array(
				'id'      => 'wcvendors_upgrade_tab',
				'title'   => __( 'Update', 'wc-vendors' ),
				'content' =>
					'<h2>' . __( 'Upgrade', 'wc-vendors' ) . '</h2>' .
					'<p>' . __( 'If you need to manually run the updates, please click on the button below.', 'wc-vendors' ) . '</p>' .
					'<p><a href="' . esc_url( add_query_arg( 'do_update_wcvendors', 'true', admin_url( 'admin.php?page=wcv-settings' ) ) ) . '" class="button button-primary">' . __( 'Run the updater', 'wc-vendors' ) . '</a></p>',

			)
		);

		$screen->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'wc-vendors' ) . '</strong></p>' .
			'<p><a href="https://www.wcvendors.com/product/wc-vendors-pro/?utm_source=plugin&utm_medium=help&utm_campaign=settings" target="_blank">' . __( 'Upgrade to Pro', 'wc-vendors' ) . '</a></p>' .
			'<p><a href="https://www.wcvendors.com/home/compatible-plugins/?utm_source=plugin&utm_medium=help&utm_campaign=settings" target="_blank">' . __( 'Buy extensions', 'wc-vendors' ) . '</a></p>' .
			'<p><a href="https://woocommerce.com/?utm_source=helptab&utm_medium=product&utm_content=about&utm_campaign=woocommerceplugin" target="_blank">' . __( 'About WC Vendors', 'wc-vendors' ) . '</a></p>' .
			'<p><a href="https://wordpress.org/plugins/wc-vendors/" target="_blank">' . __( 'WC Vendors on WordPress.org', 'wc-vendors' ) . '</a></p>' .
			'<p><a href="https://github.com/wcvendors/wcvendors" target="_blank">' . __( 'WC Vendors Github', 'wc-vendors' ) . '</a></p>'
		);
	}
}

return new WCVendors_Admin_Help();
