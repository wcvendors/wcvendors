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
	<meta name="viewport" content="width=device-width"/>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title><?php esc_html_e( 'WC Vendors &rsaquo; Setup Wizard', 'wc-vendors' ); ?></title>
	<?php wp_print_scripts( 'wcv-setup' ); ?>
	<?php do_action( 'admin_print_styles' ); ?>
</head>
<body class="wcv-setup wp-core-ui">
<h1 id="wcv-logo"><a href="https://www.wcvendors.com/"><img
				src="<?php echo esc_url( wcv_assets_url ); ?>images/wcvendors_logo.png" alt="WC Vendors"/></a></h1>
