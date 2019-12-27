<?php
/**
 * Init classes and setup our plugin.
 */
namespace WCVendors;

class Plugin {
	public function __construct() {
		$this->load_textdomain();
		$this->init_classes();
	}

	private function load_textdomain() {
		load_plugin_textdomain( 'wc-vendors', false, WCV_PLUGIN_BASENAME . '/languages' );
	}

	private function init_classes() {
	}
}