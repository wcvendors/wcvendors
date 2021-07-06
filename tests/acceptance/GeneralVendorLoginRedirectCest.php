<?php

class GeneralVendorLoginRedirectCest
{
    public function _before(AcceptanceTester $I)
    {
		$I->amOnPage('/');
		$I->click('My account');
		$I->fillField('#username', 'vendor1');
		$I->fillField('#password', '#*mr4Xk)R2l)W^XuI^P85jP');
		$I->click('Log in');
    }


    //Validating after vendor login the landing page set is open correct.
    public function tryToTest(AcceptanceTester $I)
    {
		$I->see('To add or edit products, view sales and orders for your vendor account, or to configure your store, visit your Vendor Dashboard.');
		$I->click('Log out');
		$I->fillField('#username', 'admin');
		$I->fillField('#password', '123456');
		$I->click('Log in');
		$I->amOnPage('/wp-admin/admin.php?page=wcv-settings&tab=general');
		$I->scrollTo('#mainform > table > tbody > tr:nth-child(4) > td > fieldset > label');
		$I->click('#select2-wcvendors_vendor_login_redirect-container');
		$I->wait(2);
		$I->click('//*[@class="select2-results__option"]');
		$I->scrollTo('#select2-wcvendors_vendor_login_redirect-container');
		$I->click('Save changes');
		$I->amOnPage('/my-account');
		$I->click('Log out');
		$I->fillField('#username', 'vendor1');
		$I->fillField('#password', '#*mr4Xk)R2l)W^XuI^P85jP');
		$I->click('Log in');
		$I->see('Commission paid');
		$I->click('My account');
		$I->click('Log out');
		$I->fillField('#username', 'admin');
		$I->fillField('#password', '123456');
		$I->click('Log in');
		$I->amOnPage('/wp-admin/admin.php?page=wcv-settings&tab=general');
		$I->scrollTo('#mainform > table > tbody > tr:nth-child(4) > td > fieldset > label');
		$I->click('#select2-wcvendors_vendor_login_redirect-container');
		$I->wait(2);
		$I->click('//*[@class="select2-results__option"]');
		$I->scrollTo('#select2-wcvendors_vendor_login_redirect-container');
		$I->click('Save changes');
    }
}
