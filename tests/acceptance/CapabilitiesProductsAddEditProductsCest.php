<?php

class CapabilitiesProductsAddEditProductsCest
{
    public function _before(AcceptanceTester $I)
    {
		$I->amOnPage('/');
        $I->see('wcvendors');
		$I->click('My account');
		$I->fillField('#username', 'admin');
		$I->fillField('#password', '123456');
		$I->click('Log in');
		$I->amOnPage('/wp-admin/admin.php?page=wcv-settings&tab=capabilities&section=product');
    }

    // Validating vendors view only the permitted settings via admin panel. We will be checking for one value of each labeled input field.
    public function tryToTest(AcceptanceTester $I)
    {
		$I->click('#mainform > table > tbody > tr:nth-child(1) > td > span > span.selection > span > ul > li > input');
		$I->waitForElement('.select2-results__option.select2-results__option--highlighted', 10);
		$I->click('.select2-results__option.select2-results__option--highlighted');
		$I->click('#mainform > table > tbody > tr:nth-child(2) > td > span > span.selection > span > ul > li > input');
		$I->waitForElement('.select2-results__option.select2-results__option--highlighted', 10);
		$I->click('.select2-results__option.select2-results__option--highlighted');
		$I->scrollTo('#mainform > table > tbody > tr:nth-child(3) > td > span > span.selection > span > ul > li > input');
		$I->click('#mainform > table > tbody > tr:nth-child(3) > td > span > span.selection > span > ul > li > input');
		$I->waitForElement('.select2-results__option.select2-results__option--highlighted', 10);
		$I->click('.select2-results__option.select2-results__option--highlighted');
		$I->click('#mainform > table > tbody > tr:nth-child(4) > td > fieldset > label');
		$I->click('#mainform > table > tbody > tr:nth-child(5) > td > fieldset > label');
		$I->click('#mainform > table > tbody > tr:nth-child(6) > td > fieldset > label');
		$I->click('#mainform > table > tbody > tr:nth-child(7) > td > fieldset > label');
		$I->click('#mainform > table > tbody > tr:nth-child(8) > td > fieldset > label');
		$I->click('#mainform > p.submit > button');
		$I->waitForText('Your settings have been saved.', 300);
		
		//Logging in as vendor.
		$I->amOnPage('/my-account');//Navigation to accounts page to log out.
		$I->click('Log out');
		$I->fillField('#username', 'vendor1');
		$I->fillField('#password', '#*mr4Xk)R2l)W^XuI^P85jP');
		$I->click('Log in');
		//Vendor adding a product with all the restrictions set.
		$I->amOnPage('/wp-admin/post-new.php?post_type=product');
		$I->scrollTo('#product_catdiv > div.postbox-header > h2');
		$I->click('#product-type');
		$I->dontSeeElement('//*[@value="simple"]/optgroup/option');
		$I->dontSeeElement('#_virtual');
		$I->dontSee('General');
		$I->executeJS('window.scrollTo(0, 0)');
		$I->fillField('#title', 'Restricted product');
		$I->doubleClick('#publish');
		$I->waitForText('Product published. View Product', 300);
		
		//Removing the settings.
		$I->amOnPage('/my-account');//Navigation to accounts page to log out.
		$I->click('Log out');
		$I->fillField('#username', 'admin');
		$I->fillField('#password', '123456');
		$I->click('Log in');
		$I->amOnPage('/wp-admin/admin.php?page=wcv-settings&tab=capabilities&section=product');
		$I->click('#mainform > table > tbody > tr:nth-child(1) > td > span > span.selection > span > ul > li.select2-selection__choice > span');
		$I->click('#mainform > table > tbody > tr:nth-child(1) > th > label');
		$I->click('#mainform > table > tbody > tr:nth-child(2) > td > span > span.selection > span > ul > li.select2-selection__choice > span');
		$I->click('#mainform > table > tbody > tr:nth-child(1) > th > label');
		$I->click('#mainform > table > tbody > tr:nth-child(3) > td > span > span.selection > span > ul > li.select2-selection__choice > span');
		$I->click('#mainform > table > tbody > tr:nth-child(1) > th > label');
		$I->click('#mainform > table > tbody > tr:nth-child(4) > td > fieldset > label');
		$I->click('#mainform > table > tbody > tr:nth-child(5) > td > fieldset > label');
		$I->click('#mainform > table > tbody > tr:nth-child(6) > td > fieldset > label');
		$I->click('#mainform > table > tbody > tr:nth-child(7) > td > fieldset > label');
		$I->click('#mainform > table > tbody > tr:nth-child(8) > td > fieldset > label');
		$I->click('#mainform > p.submit > button');
		$I->waitForText('Your settings have been saved.', 300);
    }
}
