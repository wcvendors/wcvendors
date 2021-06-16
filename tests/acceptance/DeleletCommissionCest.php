<?php

/**
 * DeleletComissionCest
 */
class DeleletComissionCest {

	/**
	 * Make sure user login
	 *
	 * @param  AcceptanceTester $I .
	 */
	public function _before( AcceptanceTester $I ) {
		$I->amOnPage( '/wp-admin' );
		$I->fillField( '#user_login', 'admin' );
		$I->fillField( '#user_pass', 'admin' );
		$I->click( 'Log In' );
	}


	/**
	 * Test for delete single commission row
	 *
	 * @param  AcceptanceTester $I .
	 */
	public function delete_commission( AcceptanceTester $I ) {

		$I->click( 'WC Vendors' );
                $I->moveMouseOver( '#the-list > tr' );
                $I->click( '#the-list > tr > td.order_id.column-order_id.has-row-actions.column-primary > div > span > a' );
                $I->seeInPopup( 'Are you sure delete this commission?' );
                $I->acceptPopup();
                $I->see( 'Commission(s) deleted.' );

	}

	/**
	 * Test for delete multiple commssion row
	 *
	 * @param  AcceptanceTester $I .
	 */
	public function delete_commissions( AcceptanceTester $I ) {

		$I->click( 'WC Vendors' );
                $I->selectOption( '#bulk-action-selector-top', 'delete' );
                $I->click( '#cb-select-all-1' );
                $I->click( '#doaction' );
                $I->seeInPopup( 'Are you sure delete these commissions?' );
                $I->acceptPopup();
                $I->see( 'Commission(s) deleted.' );

	}
}
