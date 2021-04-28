<?php

class GeneralVendorRegistrationFormCest
{
    public function _before(AcceptanceTester $I)
    {
		$I->amOnPage('/my-account');
    }

    // Validating that a vendor is displayed vendor registration form before applying as Vendor.
    public function tryToTest(AcceptanceTester $I)
    {
		$I->executeJS('document.querySelector("#reg_username").value = (Math.random().toString(36).substring(7))');
		$I->executeJS('document.querySelector("#reg_email").value = (Math.random().toString(36).substring(7) + ".QA.test.mail@yopmail.com");');
		$I->fillField('#reg_password', 'p@ssw0rd@123P@$%');
		$I->click('#apply_for_vendor');
		$I->click('#agree_to_terms');
		$I->click('Register');
		$I->waitForText('Store description');
		$I->executeJS('document.querySelector("#store_save_button").click()');
		$I->waitForText('Your application has been received. You will be notified by email the results of your application. QA Test Local Setup', 300);
		$I->amOnPage('/my-account');
		$I->click('Log out');
		$I->fillField('#username', 'admin');
		$I->fillField('#password', '123456');
		$I->click('Log in');
		$I->amOnPage('/wp-admin/admin.php?page=wcv-settings&tab=general');
		$I->scrollTo('#mainform > table > tbody > tr:nth-child(5) > td > fieldset > label');
		$I->click('#mainform > table > tbody > tr:nth-child(8) > td > fieldset > label');
		$I->click('Save changes');
		$I->amOnPage('/my-account');
		$I->click('Log out');
		$I->executeJS('document.querySelector("#reg_username").value = (Math.random().toString(36).substring(7))');
		$I->executeJS('document.querySelector("#reg_email").value = (Math.random().toString(36).substring(7) + ".QA.test.mail@yopmail.com");');
		$I->fillField('#reg_password', 'p@ssw0rd@123P@$%');
		$I->click('#apply_for_vendor');
		$I->click('#agree_to_terms');
		$I->click('Register');
		$I->dontSee('Store description');
		$I->waitForText('Your application has been received. You will be notified by email the results of your application. QA Test Local Setup', 300);
		$I->amOnPage('/my-account');
		$I->click('Log out');
		$I->fillField('#username', 'admin');
		$I->fillField('#password', '123456');
		$I->click('Log in');
		$I->amOnPage('/wp-admin/admin.php?page=wcv-settings&tab=general');
		$I->scrollTo('#mainform > table > tbody > tr:nth-child(5) > td > fieldset > label');
		$I->click('#mainform > table > tbody > tr:nth-child(8) > td > fieldset > label');
		$I->click('Save changes');
    }
}
