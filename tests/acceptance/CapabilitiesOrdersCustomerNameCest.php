<?php

class CapabilitiesOrdersCustomerNameCest
{
    public function _before(AcceptanceTester $I)
    {
		$I->amOnPage('/my-account');
		$I->fillField('#username', 'admin');
		$I->fillField('#password', '123456');
		$I->click('Log in');
		$I->amOnPage('/wp-admin/admin.php?page=wcv-settings&tab=capabilities&section=order');
		$I->waitForText('Allow vendors to view customer name fields', 200);
		$I->click('#wcvendors_capability_order_customer_name');
		$I->scrollTo('#mainform > table:nth-child(10) > tbody > tr:nth-child(3) > th');
		$I->click('Save changes');
		$I->waitForText('Your settings have been saved.', 300);
    }

    // Vendors wont be shown Customer Names.
    public function tryToTest(AcceptanceTester $I)
    {
		//Logging in as vendor.
		$I->amOnPage('/my-account');//Navigation to accounts page to log out.
		$I->click('Log out');
		$I->fillField('#username', 'vendor1');
		$I->fillField('#password', '#*mr4Xk)R2l)W^XuI^P85jP');
		$I->click('Log in');
		$I->click('Vendor Dashboard');
		$I->scrollTo('#post-14 > div > h2:nth-child(3)');
		$I->click('Show Orders');
		$I->dontSee('Full name');
		
		//Restoring default settings.
		$I->amOnPage('/my-account');
		$I->click('Log out');
		$I->fillField('#username', 'admin');
		$I->fillField('#password', '123456');
		$I->click('Log in');
		$I->amOnPage('/wp-admin/admin.php?page=wcv-settings&tab=capabilities&section=order');
		$I->click('#wcvendors_capability_order_customer_name');
		$I->scrollTo('#mainform > table:nth-child(10) > tbody > tr:nth-child(3) > th');
		$I->click('Save changes');
		$I->waitForText('Your settings have been saved.', 300);
    }
}
