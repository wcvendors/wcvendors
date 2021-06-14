<?php

class SettingsDisplayGeneralDashboardCest
{
    public function _before(AcceptanceTester $I)
    {
		$I->amOnPage('/my-account');//Navigation to accounts page to log out.
		$I->fillField('#username', 'admin');
		$I->fillField('#password', '123456');
		$I->click('Log in');
		$I->amOnPage('/wp-admin/admin.php?page=wcv-settings&tab=display');
		$I->waitForText('This sets the page used to display the front end vendor dashboard. This page should contain the following shortcode. [wcv_vendor_dashboard]', 200);
		$I->click('#select2-wcvendors_vendor_dashboard_page_id-container');
		$I->fillField('body > span > span > span.select2-search.select2-search--dropdown > input', "my account");
		$I->wait(1);
		//$I->click('.select2-results__option select2-results__option--highlighted');//select2-results__option select2-results__option--highlighted
		$I->pressKey('body > span > span > span.select2-search.select2-search--dropdown > input', \Facebook\WebDriver\WebDriverKeys::ENTER);
		$I->scrollTo('#mainform > table:nth-child(11) > tbody > tr:nth-child(5) > th > label');
		$I->click('Save changes');
		$I->waitForText('Your settings have been saved.', 300);
    }

    // Validating display of dashboard by default for vendor login
    public function tryToTest(AcceptanceTester $I)
    {
		//Logging in as vendor.
		$I->amOnPage('/my-account');//Navigation to accounts page to log out.
		$I->click('Log out');
		$I->fillField('#username', 'vendor1');
		$I->fillField('#password', '#*mr4Xk)R2l)W^XuI^P85jP');
		$I->click('Log in');
		$I->see('My account');
		$I->dontSee('Sales Report');
		
		//Restoring default settings.
		$I->amOnPage('/my-account');//Navigation to accounts page to log out.
		$I->fillField('#username', 'admin');
		$I->fillField('#password', '123456');
		$I->click('Log in');
		$I->amOnPage('/wp-admin/admin.php?page=wcv-settings&tab=display');
		$I->waitForText('This sets the page used to display the front end vendor dashboard. This page should contain the following shortcode. [wcv_vendor_dashboard]', 200);
		$I->click('#select2-wcvendors_vendor_dashboard_page_id-container');
		$I->fillField('body > span > span > span.select2-search.select2-search--dropdown > input', "Vendor Dashboard");
		$I->wait(1);
		//$I->click('.select2-results__option select2-results__option--highlighted');
		$I->pressKey('body > span > span > span.select2-search.select2-search--dropdown > input', \Facebook\WebDriver\WebDriverKeys::ENTER);
		$I->scrollTo('#mainform > table:nth-child(11) > tbody > tr:nth-child(5) > th > label');
		$I->click('Save changes');
		$I->waitForText('Your settings have been saved.', 300);
    }
}
