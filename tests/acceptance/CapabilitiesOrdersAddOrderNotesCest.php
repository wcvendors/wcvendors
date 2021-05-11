<?php

class CapabilitiesOrdersAddOrderNotesCest
{
    public function _before(AcceptanceTester $I)
    {
		$I->amOnPage('/my-account');
		$I->fillField('#username', 'admin');
		$I->fillField('#password', '123456');
		$I->click('Log in');
		$I->amOnPage('/wp-admin/admin.php?page=wcv-settings&tab=capabilities&section=order');
		$I->click('#mainform > table:nth-child(7) > tbody > tr:nth-child(2) > td > fieldset > label');
		$I->scrollTo('#mainform > table:nth-child(10) > tbody > tr:nth-child(3) > th');
		$I->click('Save changes');
		$I->waitForText('Your settings have been saved.', 300);
    }

    // Vendors will not be able to add order notes.
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
		$I->click('Comments');
		$I->dontSeeElement('.btn.btn-large.btn-block');
		
		//Reverting to default settings.
		$I->amOnPage('/my-account');
		$I->click('Log out');
		$I->fillField('#username', 'admin');
		$I->fillField('#password', '123456');
		$I->click('Log in');
		$I->amOnPage('/wp-admin/admin.php?page=wcv-settings&tab=capabilities&section=order');
		$I->click('#mainform > table:nth-child(7) > tbody > tr:nth-child(2) > td > fieldset > label');
		$I->scrollTo('#mainform > table:nth-child(10) > tbody > tr:nth-child(3) > th');
		$I->click('Save changes');
		$I->waitForText('Your settings have been saved.', 300);
    }
}
