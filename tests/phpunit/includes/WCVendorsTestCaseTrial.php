<?php
/**
 * Ready to use test case which set up Brain Monkey.
 *
 * @package WCVendors_Pro
 */

use Brain\Monkey;
use Brain\Monkey\Functions;
use Mockery;

/**
 * Base test case for WC Vendors Component.
 */
class WCVendorsTestCaseTrail extends WCVendorsTestCase {


	public function test_if_user_exists() {
		global $wpdb;
		$wpdb = Mockery::mock( 'wpdb' );
		$wpdb->shouldReceive( 'prepare' )
		->once()
		->with( 'SELECT COUNT(ID) FROM wp_users WHERE ID = %d', 1 )
		->andReturn( 'SELECT COUNT(ID) FROM wp_users WHERE ID = 1' );

		$prepare = $wpdb->prepare( 'SELECT COUNT(ID) FROM wp_users WHERE ID = %d', 1 );

		$wpdb->shouldReceive( 'get_var' )
		->once()
		->with( $prepare )
		->andReturn( 1 );

		$result = $wpdb->get_var( $prepare );

		$test_mock = Mockery::mock( '\WCVendors\VendorHandler' )->makePartial();

		$test_mock->shouldReceive( 'user_id_exists' )
		->once()
		->with( 1 )
		->andReturn( true );

		$is_user_exists = $test_mock->user_id_exists( 1 );

		$this->assertEquals( 1, $result );

		$this->assertTrue( $is_user_exists );
	}

	public function test_enable_vendor() {
		$test_mock = Mockery::mock( '\WCVendors\VendorHandler' )->makePartial();

		$test_mock->shouldReceive( 'enable' )
		->once()
		->with( 1 )
		->andReturn( true );

		$is_update_susscess = $test_mock->enable( 1 );

		$this->assertTrue( $is_update_susscess );
	}

	public function test_disable_vendor() {
		$test_mock = Mockery::mock( '\WCVendors\VendorHandler' )->makePartial();

		$test_mock->shouldReceive( 'disable' )
		->once()
		->with( 1 )
		->andReturn( true );

		$is_disable_susscess = $test_mock->disable( 1 );

		$this->assertTrue( $is_disable_susscess );
	}
}

