<?php
/**
 * The following snippets uses `PLUGIN` to prefix
 * the constants and class names. You should replace
 * it with something that matches your plugin name.
 *
 * @package WCVendors_Pro
 */

// Define test environment.
define( 'PLUGIN_PHPUNIT', true );

// Define fake ABSPATH.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', sys_get_temp_dir() );
}
// Define fake PLUGIN_ABSPATH.
if ( ! defined( 'PLUGIN_ABSPATH' ) ) {
	define( 'PLUGIN_ABSPATH', sys_get_temp_dir() . '/wp-content/plugins/wcvendors/' );
}

require_once __DIR__ . '/../../vendor/autoload.php';

// Include the class for WCVendorsTestCase.
require_once __DIR__ . '/includes/WCVendorsTestCase.php';
