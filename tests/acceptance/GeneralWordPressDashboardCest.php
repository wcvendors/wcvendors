<?php

class GeneralWordPressDashboardCest
{
    public function _before(AcceptanceTester $I)
    {
		$I->amOnPage('/');
		$I->click('My account');
    }

    // Vendors should not be able to access WordPress dashboard if not checked at the settings.
    public function tryToTest(AcceptanceTester $I)
    {
		$I->fillField('#username', 'vendor1');
		$I->fillField('#password', '#*mr4Xk)R2l)W^XuI^P85jP');
		$I->click('Log in');
		$I->amOnPage('/wp-admin');
		$I->amOnPage('/my-account');
		$I->click('Log out');
		$I->fillField('#username', 'admin');
		$I->fillField('#password', '123456');
		$I->click('Log in');
		$I->amOnPage('/wp-admin/admin.php?page=wcv-settings&tab=general');
		$I->click('#mainform > table > tbody > tr:nth-child(6) > td > fieldset > label');
		$I->scrollTo('#select2-wcvendors_vendor_login_redirect-container');
		$I->click('Save changes');
		$I->amOnPage('/my-account');
		$I->click('Log out');
		$I->fillField('#username', 'vendor1');
		$I->fillField('#password', '#*mr4Xk)R2l)W^XuI^P85jP');
		$I->click('Log in');
		$I->amOnPage('/wp-admin');
		$I->dontSee('Personal Options');
		$I->amOnPage('/my-account');
		$I->click('Log out');
		$I->fillField('#username', 'admin');
		$I->fillField('#password', '123456');
		$I->click('Log in');
		$I->amOnPage('/wp-admin/admin.php?page=wcv-settings&tab=general');
		$I->click('#mainform > table > tbody > tr:nth-child(6) > td > fieldset > label');
		$I->scrollTo('#select2-wcvendors_vendor_login_redirect-container');
		$I->click('Save changes');
    }
}
