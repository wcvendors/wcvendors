<?php

class VendorResetPasswordCest
{
    public function _before(AcceptanceTester $I)
    {
		$I->amOnPage('/');
        $I->see('wcvendors');
		$I->click('My account');
    }

    // Restting password for Vendor {You will need WP Mail Logging plugin installed and running before running these script}
    public function tryToTest(AcceptanceTester $I)
    {
		$I->scrollTo('#username');
		$I->click('Lost your password');
		$I->waitForText('Lost password');
		$I->scrollTo('#post-9 > header > h1');
		$I->fillField('#user_login', 'vendor.seller.two@yopmail.com');
		$I->click('Reset password');
		$I->waitForText('Password reset email has been sent', 20);
		$I->amOnPage('/my-account');
		$I->fillField('#username', 'admin');
		$I->fillField('#password', '123456');
		$I->click('Log in');
		$I->amOnPage('/wp-admin/tools.php?page=wpml_plugin_log');//You will need WP Mail Logging Plugin active and running.
		$I->waitForText('WP Mail Logging Log', 20);
		$I->fillField('#s-search-input', 'vendor.seller.two@yopmail.com');
		$I->pressKey('#s-search-input', \Facebook\WebDriver\WebDriverKeys::ENTER);//After searching, you would notice mails only for that searched email address.
		$I->waitForText('Password Reset Request for wcvendors', 20);
		$I->executeJS('document.querySelector("#the-list > tr:nth-child(1) > td.message.column-message > a").click()');
		$I->waitForText('Someone has requested a new password for the following account on wcvendors:', 20);
		$I->waitForElement('#body_content_inner > p:nth-child(5) > a');
		$I->executeJS('window.open("http://localhost/wordpress/my-account/")');
		$I->switchToNextTab();
		$I->waitForText('Log out', 300);
		$I->click('Log out');
		$I->switchToNextTab();
		$I->executeJS('document.querySelector("#body_content_inner > p:nth-child(5) > a").click()');
		$I->scrollTo('#post-9 > header > h1');
		$I->fillField('#password_1', 'p@12323@P2323#$%R');
		$I->fillField('#password_2', 'p@12323@P2323#$%R');
		$I->click('Save');
		$I->waitForText('Your password has been reset successfully.');
		$I->fillField('#username', 'vendor.seller.two@yopmail.com');
		$I->fillField('#password', 'p@12323@P2323#$%R');
		$I->click('Log in');
		$I->waitForText('Hello Vendor Two');
		//Resetting the password
		$I->click('Log out');
		$I->scrollTo('#username');
		$I->click('Lost your password');
		$I->waitForText('Lost password');
		$I->scrollTo('#post-9 > header > h1');
		$I->fillField('#user_login', 'vendor.seller.two@yopmail.com');
		$I->click('Reset password');
		$I->waitForText('Password reset email has been sent', 20);
		$I->amOnPage('/my-account');
		$I->fillField('#username', 'admin');
		$I->fillField('#password', '123456');
		$I->click('Log in');
		$I->amOnPage('/wp-admin/tools.php?page=wpml_plugin_log');//You will need WP Mail Logging Plugin active and running.
		$I->waitForText('WP Mail Logging Log', 20);
		$I->fillField('#s-search-input', 'vendor.seller.two@yopmail.com');
		$I->pressKey('#s-search-input', \Facebook\WebDriver\WebDriverKeys::ENTER);//After searching, you would notice mails only for that searched email address.
		$I->waitForText('Password Reset Request for wcvendors', 20);
		$I->executeJS('document.querySelector("#the-list > tr:nth-child(1) > td.message.column-message > a").click()');
		$I->waitForText('Someone has requested a new password for the following account on wcvendors:', 20);
		$I->waitForElement('#body_content_inner > p:nth-child(5) > a');
		$I->executeJS('window.open("http://localhost/wordpress/my-account/")');
		$I->switchToNextTab();
		$I->switchToNextTab();
		$I->waitForText('Log out', 300);
		$I->click('Log out');
		$I->switchToNextTab();
		$I->executeJS('document.querySelector("#body_content_inner > p:nth-child(5) > a").click()');
		$I->scrollTo('#post-9 > header > h1');
		$I->fillField('#password_1', '1IZ)h7%J9wQNG@AUqE43y2%c');
		$I->fillField('#password_2', '1IZ)h7%J9wQNG@AUqE43y2%c');
		$I->click('Save');
		$I->waitForText('Your password has been reset successfully.');
		$I->fillField('#username', 'vendor.seller.two@yopmail.com');
		$I->fillField('#password', '1IZ)h7%J9wQNG@AUqE43y2%c');
		$I->click('Log in');
		$I->waitForText('Hello Vendor Two');
    }
}
