<?php
/**
 * Backwards compat.
 *
 * @package WCVendors
 * @since 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$active_plugins = get_option( 'active_plugins', array() );
foreach ( $active_plugins as $key => $active_plugin ) {
	if ( strstr( $active_plugin, '/class-wc-vendors.php' ) ) {
		$active_plugins[ $key ] = str_replace( '/class-wc-vendors.php', '/wc-vendors.php', $active_plugin );
	}
}
update_option( 'active_plugins', $active_plugins );
