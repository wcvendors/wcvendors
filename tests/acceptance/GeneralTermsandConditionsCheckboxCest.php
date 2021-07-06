<?php

class GeneralTermsandConditionsCheckboxCest
{
    public function _before(AcceptanceTester $I)
    {
		$I->amOnPage('/');
		$I->click('My account');
		
    }

    // Validation for the terms and conditions checkbox to be present at my accounts page.
    public function tryToTest(AcceptanceTester $I)
    {
		$I->see('I have read and accepted the terms and conditions.');//Assumed that initially with your local setup the terms and conditions checkbox is set to be true.
		$I->fillField('#username', 'admin');
		$I->fillField('#password', '123456');
		$I->click('Log in');
		$I->amOnPage('/wp-admin/admin.php?page=wcv-settings');//Directly navigating to the URL of WCVendors settings page.
		//Untick the Allow users to apply to become vendor
		$I->click('#mainform > table > tbody > tr:nth-child(2) > td > fieldset > label');
		$I->scrollTo('#select2-wcvendors_vendor_login_redirect-container');
		$I->click('Save changes');
		$I->amOnPage('/my-account');
		$I->click('Log out');
		$I->dontSee('I have read and accepted the terms and conditions.');
		$I->fillField('#username', 'admin');
		$I->fillField('#password', '123456');
		$I->click('Log in');
		$I->amOnPage('/wp-admin/admin.php?page=wcv-settings');
		$I->click('#mainform > table > tbody > tr:nth-child(2) > td > fieldset > label');
		$I->scrollTo('#select2-wcvendors_vendor_login_redirect-container');
		$I->click('Save changes');
    }
}
