<?php
/**
 * Our logger that integrated to WC.
 *
 * @package WCVendors
 */

namespace WCVendors;

use WC_Logger;

/**
 * Our logger that integrated to WC.
 */
class Logger {
	/**
	 * This hold the WC logger.
	 *
	 * @var WC_Logger $wc_logger WC_Logger instance.
	 */
	private $wc_logger;

	/**
	 * Whether to enable log.
	 *
	 * @var bool $enabled Enable/disable log.
	 */
	private $enabled;

	/**
	 * The filename of our log.
	 */
	const WC_LOG_FILENAME = 'wc-vendors';

	/**
	 * Initialize the logger.
	 */
	public function __construct() {
		$this->enabled = wc_string_to_bool( get_option( 'wcv_enable_logging', true ) );
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG || $this->enabled ) {
			$this->set_wc_logger();
		}
	}

	/**
	 * Grab an instance of WC_Logger and save it.
	 */
	private function set_wc_logger() {
		if ( ! class_exists( 'WC_Logger' ) ) {
			return;
		}
		$this->wc_logger = wc_get_logger();
	}

	/**
	 * Add an log entry.
	 *
	 * @param string $message Log message.
	 * @param string $type    Log type. Can be one of the following: emergency|
	 *                        alert|critical|error|warning|notice|info|debug.
	 */
	public function log( $message, $type = 'debug' ) {
		if ( ! $this->enabled ) {
			return;
		}
		if ( ! $this->wc_logger ) {
			return;
		}
		if ( ! apply_filters( 'wcv_log', true, $message ) ) {
			return;
		}

		if ( is_array( $message ) || is_object( $message ) ) {
			$message = print_r( $message, true ); // phpcs:ignore
		}

		$this->wc_logger->log( $type, $message, array( 'source' => self::WC_LOG_FILENAME ) );
	}
}
