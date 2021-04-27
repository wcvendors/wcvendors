<?php

class GeneralVendorApprovalCest
{
    public function _before(AcceptanceTester $I)
    {
		$I->amOnPage('/');
		$I->click('My account');
    }

    //Vendor approval process manually, validation will includes vendor login success and failiour messages.
    public function tryToTest(AcceptanceTester $I)
    {
		$I->executeJS('document.querySelector("#reg_username").value = (Math.random().toString(36).substring(7)');
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
		$I->click('#mainform > table > tbody > tr:nth-child(3) > td > fieldset > label');
		$I->scrollTo('#select2-wcvendors_vendor_login_redirect-container');
		$I->click('Save changes');
		$I->amOnPage('/my-account');
		$I->click('Log out');
		$I->executeJS('document.querySelector("#reg_username").value = (Math.random().toString(36).substring(7)');
		$I->executeJS('document.querySelector("#reg_email").value = (Math.random().toString(36).substring(7) + ".QA.test.mail@yopmail.com");');
		$I->fillField('#reg_password', 'p@ssw0rd@123P@$%1');
		$I->click('#apply_for_vendor');
		$I->click('#agree_to_terms');
		$I->click('Register');
		$I->waitForText('Store description');
		$I->executeJS('document.querySelector("#store_save_button").click()');
		$I->dontSee('Your application has been received. You will be notified by email the results of your application. QA Test Local Setup');
		$I->amOnPage('/my-account');
		$I->click('Log out');
		$I->fillField('#username', 'admin');
		$I->fillField('#password', '123456');
		$I->click('Log in');
		$I->amOnPage('/wp-admin/admin.php?page=wcv-settings&tab=general');
		$I->click('#mainform > table > tbody > tr:nth-child(3) > td > fieldset > label');
		$I->scrollTo('#select2-wcvendors_vendor_login_redirect-container');
		$I->click('Save changes');
    }
}
