<?php
/**
 * WCV Dependency Checker
 *
 * Checks if a required plugin is enabled
 */

class WCV_Dependencies {

	private static $active_plugins;


	/**
	 *
	 */
	public static function init() {

		self::$active_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() ) {
			self::$active_plugins = array_merge( self::$active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}
	}


	/**
	 *
	 *
	 * @return boolean
	 */
	public static function woocommerce_active_check() {

		if ( ! self::$active_plugins ) {
			self::init();
		}

		foreach ( self::$active_plugins as $plugin ) {
			if ( strpos( $plugin, '/woocommerce.php' ) ) {
				return true;
			}
		}

		return false;
	}
}


