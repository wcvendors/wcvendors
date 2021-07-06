<?php

class SettingsdisplaybecomeavendorCest
{
    public function _before(AcceptanceTester $I)
    {
		$I->amOnPage('/my-account');//Navigation to accounts page to log out.
		$I->fillField('#username', 'admin');
		$I->fillField('#password', '123456');
		$I->click('Log in');
		$I->amOnPage('/wp-admin/admin.php?page=wcv-settings&tab=display&section=labels');
		$I->waitForText('This enables the sold by labels used to show which vendor shop the product belongs to', 200);
		$I->scrollTo('#wcvendors_label_sold_by_separator');
		$I->click('#mainform > table > tbody > tr:nth-child(6) > td > fieldset > label');
		$I->scrollTo('#wcvendors_store_total_sales_label');
		$I->click('Save changes');
		$I->waitForText('Your settings have been saved.', 300);
    }

    // Validating option to Become a Vendor being displayed at front end at the login page.
    public function tryToTest(AcceptanceTester $I)
    {
		$I->amOnPage('/my-account');//Navigation to accounts page to log out.
		$I->click('Log out');
		$I->scrollTo('#password');
		$I->dontSee('Apply to become a vendor?');
		
		//Restoring default
		$I->fillField('#username', 'admin');
		$I->fillField('#password', '123456');
		$I->click('Log in');
		$I->amOnPage('/wp-admin/admin.php?page=wcv-settings&tab=display&section=labels');
		$I->waitForText('This enables the sold by labels used to show which vendor shop the product belongs to', 200);
		$I->scrollTo('#wcvendors_label_sold_by_separator');
		$I->click('#mainform > table > tbody > tr:nth-child(6) > td > fieldset > label');
		$I->scrollTo('#wcvendors_store_total_sales_label');
		$I->click('Save changes');
		$I->waitForText('Your settings have been saved.', 300);
    }
}
