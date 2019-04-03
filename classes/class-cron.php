<?php
/**
 * Cron class
 *
 * @package WC_Vendors
 */


class WCV_Cron {


	/**
	 * Constructor
	 */
	function __construct() {

		add_filter( 'cron_schedules'                  , array( 'WCV_Cron', 'custom_cron_intervals' ) );
		add_action( 'wcvendors_settings_save_payments', array( 'WCV_Cron', 'check_schedule'        ) );

		add_filter( 'wcvendors_admin_settings_sanitize_option_wcvendors_payments_paypal_schedule', array( 'WCV_Cron', 'check_schedule_now' ) );
		// add_filter( WC_Vendors::$id . '_options_on_update', array( 'WCV_Cron', 'check_schedule_now' ) );
	}


	/**
	 * Re-add cron schedule when the settings have been updated
	 *
	 * @param         array
	 * @param unknown $options
	 */
	public static function check_schedule() {

		$old_interval = wp_get_schedule( 'pv_schedule_mass_payments' );
		$new_interval = wc_string_to_bool( get_option( 'wcvendors_payments_paypal_schedule', '' ) );
		$instapay     = wc_string_to_bool( get_option( 'wcvendors_payments_paypal_instantpay_enable', 'no' ) );

		/**
		 * 1. The user actually changed the schedule
		 * 2. Instapay is turned off
		 * 3. Manual was not selected
		 */
		if ( ( $old_interval != $new_interval ) && ! $instapay && 'manual' != $new_interval ) {
			WCV_Cron::remove_cron_schedule();
			WCV_Cron::schedule_cron( $new_interval );
		}

		if ( 'manual' == $new_interval || $instapay ) {
			WCV_Cron::remove_cron_schedule();
		}

	}


	/**
	 * Check if the user chose "Now" on the Schedule settings
	 *
	 * @param array $options
	 *
	 * @return array
	 */
	public static function check_schedule_now( $new_schedule ) {

		$old_schedule = get_option( 'wcvendors_payments_paypal_schedule' );

		if ( 'now' == $new_schedule ) {
			$return              = WCV_Cron::pay_now();
			$options['schedule'] = $old_schedule;
			WCV_Cron::schedule_cron( $old_schedule );
			WCVendors_Admin_Settings::add_message( wp_strip_all_tags( $return['message'] ) );
		}

		return $new_schedule;
	}


	/**
	 * Pay all outstanding commission using Paypal Mass Pay
	 *
	 * @return array
	 */
	public static function pay_now() {

		$mass_pay = new WCV_Mass_Pay();
		$mass_pay = $mass_pay->do_payments();

		$message = ! empty( $mass_pay['total'] )
			? $mass_pay['msg'] . '<br/>' . sprintf( __( 'Payment total: %s', 'wc-vendors' ), wc_price( $mass_pay['total'] ) )
			: $mass_pay['msg'];

		return array(
			'message' => $message,
			'status'  => $mass_pay['status'],
		);
	}


	/**
	 * Remove the mass payments schedule
	 *
	 * @return bool
	 */
	public static function remove_cron_schedule() {

		$timestamp = wp_next_scheduled( 'pv_schedule_mass_payments' );

		return wp_unschedule_event( $timestamp, 'pv_schedule_mass_payments' );
	}


	/**
	 * Schedule a cron event on a specified interval
	 *
	 * @param string $interval
	 *
	 * @return bool
	 */
	public static function schedule_cron( $interval ) {

		// Scheduled event
		add_action( 'pv_schedule_mass_payments', array( 'WCV_Cron', 'pay_now' ) );

		// Schedule the event
		if ( ! wp_next_scheduled( 'pv_schedule_mass_payments' ) ) {
			wp_schedule_event( time(), $interval, 'pv_schedule_mass_payments' );

			return true;
		}

		return false;
	}


	/**
	 * Add new schedule intervals to WP
	 *
	 * Weekly
	 * Biweekly
	 * Monthly
	 *
	 * @param array $schedules
	 *
	 * @return array
	 */
	public static function custom_cron_intervals( $schedules ) {

		$schedules['daily'] = array(
			'interval' => 86400,
			'display'  => __( 'Once Daily' ),
		);

		$schedules['weekly'] = array(
			'interval' => 604800,
			'display'  => __( 'Once Weekly' ),
		);

		$schedules['biweekly'] = array(
			'interval' => 1209600,
			'display'  => __( 'Once every two weeks' ),
		);

		$schedules['monthly'] = array(
			'interval' => 2635200,
			'display'  => __( 'Once a month' ),
		);

		return $schedules;
	}


}
