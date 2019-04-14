<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Core helper functions 
 *
 * @author      Jamie Madden, WC Vendors
 * @category    Functions
 * @package     WCVendors/Functions
 * @version     2.0.0
 */

function wcv_get_permalink_structure(){ 
	$permalinks = wp_parse_args( (array) get_option( 'wcvendors_permalinks', array() ), array(
			'vendor_shop_base'        => ''
	) );
	
	// Ensure that the permalinks are set 
	$permalinks[ 'vendor_shop_base' ]   = untrailingslashit( empty( $permalinks[ 'vendor_shop_base' ] ) ? __( 'vendors', 'wcvendors' )  : $permalinks[ 'vendor_shop_base' ] );

	return $permalinks; 
}

/**
 * Formats the order status for localization
 *
 * @since 1.0.0
 * @param string $order_status
 */
function wcv_format_order_status( $order_status = '' ) {
	switch ( $order_status ) {
		case 'pending':
			$order_status = __( 'Pending', 'wcvendors' );
			break;
		case 'processing':
			$order_status = __( 'Processing', 'wcvendors' );
			break;
		case 'on-hold':
			$order_status = __( 'On-hold', 'wcvendors' );
			break;
		case 'completed':
			$order_status = __( 'Completed', 'wcvendors' );
			break;
		case 'cancelled':
			$order_status = __( 'Cancelled', 'wcvendors' );
			break;
		case 'refunded':
			$order_status = __( 'Refunded', 'wcvendors' );
			break;
		case 'failed':
			$order_status = __( 'Failed', 'wcvendors' );
			break;
		case 'pre-ordered':
			$order_status = __( 'Pre-ordered', 'wcvendors' );
			break;
		case 'trash':
			$order_status = __( 'Trash', 'wcvendors' );
			break;
		default:
			$order_status = __( 'Unknown', 'wcvendors' );
			break;
	}

	return $order_status;
}

/**
 * Converts a GMT date into the correct format for the blog.
 *
 * Requires and returns a date in the Y-m-d H:i:s format. If there is a
 * timezone_string available, the returned date is in that timezone, otherwise
 * it simply adds the value of gmt_offset. Return format can be overridden
 * using the $format parameter
 *
 * @since 1.0.0
 * @version 1.0.0
 * @param string $string The date to be converted.
 * @param string $format The format string for the returned date (default is Y-m-d H:i:s)
 * @return string Formatted date relative to the timezone / GMT offset.
 */
function wcv_get_date_from_gmt( $string, $format = 'Y-m-d H:i:s', $timezone_string ) {
	$tz = $timezone_string;

	if ( empty( $timezone_string ) ) {
		$tz = get_option( 'timezone_string' );
	}

	if ( $tz && ( ! preg_match( '/UTC-/', $tz ) && ! preg_match( '/UTC+/', $tz ) ) ) {
		$datetime = date_create( $string, new DateTimeZone( 'UTC' ) );

		if ( ! $datetime ) {
			return date( $format, 0 );
		}

		$datetime->setTimezone( new DateTimeZone( $tz ) );
		$string_localtime = $datetime->format( $format );
	} else {
		if ( ! preg_match( '#([0-9]{1,4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})#', $string, $matches ) ) {
			return date( $format, 0 );
		}

		$string_time = gmmktime( $matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1] );
		$string_localtime = gmdate( $format, $string_time + get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
	}

	return $string_localtime;
}

/**
 * Formats the order and payout dates to be consistent
 *
 * @since 1.0.0
 * @version 1.0.0
 * @param string $sql_date
 * @return string $date
 */
function wcv_format_date( $sql_date, $timezone = '' ) {
	$date = '0000-00-00 00:00:00';

	if ( '0000-00-00 00:00:00' !== $sql_date ) {
		$date = wcv_get_date_from_gmt( $sql_date, get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timezone );
	}

	return apply_filters( 'wcvendors_date_format', $date, $sql_date );
}