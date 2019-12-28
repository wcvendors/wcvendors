<?php
/**
 * Plugin Name:         WC Vendors
 * Plugin URI:          https://www.wcvendors.com
 * Description:         Create your own marketplace and allow users to sign up and sell products on your store. All while taking a commission!
 * Author:              WC Vendors
 * Author URI:          https://www.wcvendors.com
 * GitHub Plugin URI:   https://github.com/wcvendors/wcvendors
 *
 * Version:              3.0.0
 * Requires at least:    4.4.0
 * Tested up to:         4.9.0
 * WC requires at least: 3.0.0
 * WC tested up to:      3.2.0
 *
 * Text Domain:         wc-vendors
 * Domain Path:         /languages/
 *
 * @package             WCVendors

WC Vendors is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

WC Vendors is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with WC Vendors. If not, see http://www.gnu.org/licenses/gpl-2.0.txt.
 */

namespace WCVendors;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'WCV_VERSION', '3.0.0' );
define( 'WCV_MIN_PHP_VER', '5.6.0' );
define( 'WCV_MIN_WC_VER', '3.0.0' );
define( 'WCV_PLUGIN_PATH', dirname( __FILE__ ) . '/' );
define( 'WCV_PLUGIN_FILE', __FILE__ );
define( 'WCV_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'WCV_TEMPLATE_DEBUG_MODE', false );

// Use Jetpack autoloader instead of default Composer one.
require __DIR__ . '/vendor/autoload.php';

add_action(
	'woocommerce_loaded',
	function() {
		new Plugin();
		do_action( 'wcvendors_loaded' );
	}
);

/**
 * Activation hook.
 */
register_activation_hook(
	__FILE__,
	function () {
		update_option( 'wcvendors_activated', true );
		do_action( 'wcvendors_activate' );
	}
);

/**
 * Deactivation hook.
 */
register_deactivation_hook(
	__FILE__,
	function () {
		update_option( 'wcvendors_activated', false );
		do_action( 'wcvendors_activate' );
	}
);
