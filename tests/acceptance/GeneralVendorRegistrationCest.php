<?php

class GeneralVendorRegistrationCest
{
    public function _before(AcceptanceTester $I)
    {
		$I->amOnPage('/');
		$I->click('My account');
		$I->fillField('#username', 'admin');
		$I->fillField('#password', '123456');
		$I->click('Log in');
    }

    // Vendor Registration is disabled and enabled to check the effect at the front end and validated.
    public function tryToTest(AcceptanceTester $I)
    {
		$I->amOnPage('/wp-admin/admin.php?page=wcv-settings');//Directly navigating to the URL of WCVendors settings page.
		//Untick the Allow users to apply to become vendor
		$I->click('#mainform > table > tbody > tr:nth-child(1) > td > fieldset > label');
		$I->scrollTo('#select2-wcvendors_vendor_login_redirect-container');
		$I->click('Save changes');
		$I->amOnPage('/my-account');
		$I->click('Log out');
		$I->dontSee('Apply to become a vendor?');
		$I->fillField('#username', 'admin');
		$I->fillField('#password', '123456');
		$I->click('Log in');
		$I->amOnPage('/wp-admin/admin.php?page=wcv-settings');
		$I->click('#mainform > table > tbody > tr:nth-child(1) > td > fieldset > label');
		$I->scrollTo('#select2-wcvendors_vendor_login_redirect-container');
		$I->click('Save changes');
		$I->amOnPage('/my-account');
		$I->click('Log out');
		$I->see('Apply to become a vendor?');
    }
}
