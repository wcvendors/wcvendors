<?php

class CapabilitiesOrdersCustomerShippingAddressEmailandPhoneCest
{
    public function _before(AcceptanceTester $I)
    {
		$I->amOnPage('/my-account');//Navigation to accounts page to log out.
		$I->fillField('#username', 'admin');
		$I->fillField('#password', '123456');
		$I->click('Log in');
		$I->amOnPage('/wp-admin/admin.php?page=wcv-settings&tab=capabilities&section=order');
		$I->waitForText('Allow vendors to view the customer shipping fields', 200);
		$I->scrollTo('#wcvendors_capability_order_customer_billing');
		$I->click('#wcvendors_capability_order_customer_shipping');
		$I->click('#wcvendors_capability_order_customer_email');
		$I->click('#wcvendors_capability_order_customer_phone');
		$I->scrollTo('#mainform > table:nth-child(10) > tbody > tr:nth-child(3) > th');
		$I->click('Save changes');
		$I->waitForText('Your settings have been saved.', 300);
    }

    // Validating that customer's shipping address, email and phone are not displayed in order table.
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
		$I->dontSee('Email address');
		$I->dontSee('Address');
		$I->dontSee('City');
		$I->dontSee('State');
		$I->dontSee('Zip');
		$I->dontSee('Phone');
		
		//Restoring default settings.
		$I->amOnPage('/my-account');
		$I->click('Log out');
		$I->fillField('#username', 'admin');
		$I->fillField('#password', '123456');
		$I->click('Log in');
		$I->amOnPage('/wp-admin/admin.php?page=wcv-settings&tab=capabilities&section=order');
		$I->waitForText('Allow vendors to view the customer shipping fields', 200);
		$I->scrollTo('#wcvendors_capability_order_customer_billing');
		$I->click('#wcvendors_capability_order_customer_shipping');
		$I->click('#wcvendors_capability_order_customer_email');
		$I->click('#wcvendors_capability_order_customer_phone');
		$I->scrollTo('#mainform > table:nth-child(10) > tbody > tr:nth-child(3) > th');
		$I->click('Save changes');
		$I->waitForText('Your settings have been saved.', 300);
    }
}
