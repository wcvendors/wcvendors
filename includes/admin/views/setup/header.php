<?php
/**
 * Admin View: Setup Header 
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta name="viewport" content="width=device-width" />
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<title><?php esc_html_e( 'WC Vendors &rsaquo; Setup Wizard', 'wcvendors' ); ?></title>
			<?php wp_print_scripts( 'wcv-setup' ); ?>
			<?php do_action( 'admin_print_styles' ); ?>
			<?php do_action( 'admin_head' ); ?>
		</head>
		<body class="wcv-setup wp-core-ui">
			<h1 id="wcv-logo"><a href="https://wcv-vendors.com/"><img src="<?php echo esc_url( WCVendors()->plugin_url() ); ?>/assets/images/wcvendors_logo.png" alt="WC Vendors" /></a></h1>