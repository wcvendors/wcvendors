<?php

class CapabilitiesSubmitProductsCest
{
    public function _before(AcceptanceTester $I)
    {
		$I->amOnPage('/');
        $I->see('wcvendors');
		$I->click('My account');
    }

    // Validate allowing vendors to add/edit products successfully. At the capabilities/Submit product "Allow vendors to add/edit products" is set by default.
    public function tryToTest(AcceptanceTester $I)
    {
		$I->fillField('#username', 'vendor2');
		$I->fillField('#password', '1IZ)h7%J9wQNG@AUqE43y2%c');
		$I->click('Log in');
		$I->click('Vendor Dashboard');
		$I->waitForText('Add New Product', 60);
		$I->click('My account');
		$I->click('Log out');
		$I->fillField('#username', 'admin');
		$I->fillField('#password', '123456');
		$I->click('Log in');
		$I->amOnPage('/wp-admin/admin.php?page=wcv-settings&tab=capabilities');
		$I->click('#mainform > table:nth-child(10) > tbody > tr:nth-child(1) > td > fieldset > label');
		$I->scrollTo('#mainform > table:nth-child(15) > tbody > tr:nth-child(5) > td > fieldset > label');
		$I->click('Save changes');
		$I->amOnPage('/my-account');
		$I->click('Log out');
		$I->fillField('#username', 'vendor2');
		$I->fillField('#password', '1IZ)h7%J9wQNG@AUqE43y2%c');
		$I->click('Log in');
		$I->click('Vendor Dashboard');
		$I->dontSee('Add New Product');
		$I->click('My account');
		$I->click('Log out');
		$I->fillField('#username', 'admin');
		$I->fillField('#password', '123456');
		$I->click('Log in');
		$I->amOnPage('/wp-admin/admin.php?page=wcv-settings&tab=capabilities');
		$I->click('#mainform > table:nth-child(10) > tbody > tr:nth-child(1) > td > fieldset > label');
		$I->scrollTo('#mainform > table:nth-child(15) > tbody > tr:nth-child(5) > td > fieldset > label');
		$I->click('Save changes');
    }
}
