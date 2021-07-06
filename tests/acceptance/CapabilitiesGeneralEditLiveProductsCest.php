<?php

class CapabilitiesGeneralEditLiveProductsCest
{
    public function _before(AcceptanceTester $I)
    {
		$I->amOnPage('/');
        $I->see('wcvendors');
		$I->click('My account');
		$I->fillField('#username', 'vendor2');
		$I->fillField('#password', '1IZ)h7%J9wQNG@AUqE43y2%c');
		$I->click('Log in');
    }

    // Validating while a product is live Vendor does have rights to edit or not, as per the settings.
    public function tryToTest(AcceptanceTester $I)
    {
		$I->click('Vendor Dashboard');
		$I->waitForText('Add New Product', 60);
		$I->click('Add New Product');
		$I->fillField('#title', 'Automated Edit Product');
		$I->click('#in-product_cat-15'); //Setting uncategorized category for automation.
		$I->fillField('#_regular_price', '1000');//Setting the price for automated product.
		$I->executeJS('window.scrollTo(0, 0)');
		$I->waitForElement('#publish', 30);
		$I->wait(5);//Multiple wait statement because of waiting for the same element.
		$I->doubleClick('#publish');
		$I->waitForText('Product published. View Product', 120);
		//Vendore modifying publishd product.
		$I->fillField('#title', 'Automated Product name modified');
		$I->click('Update');
		$I->waitForText('Product updated. View Product', 310);
		$I->amOnPage('/my-account');
		$I->click('Log out');
		$I->fillField('#username', 'admin');
		$I->fillField('#password', '123456');
		$I->click('Log in');
		$I->amOnPage('/wp-admin/admin.php?page=wcv-settings&tab=capabilities');
		$I->waitForText('Allow vendors to edit published (live) products', 300);
		$I->executeJS('document.querySelector("#mainform > table:nth-child(10) > tbody > tr:nth-child(2) > td > fieldset > label").click()');
		$I->scrollTo('#mainform > table:nth-child(15) > tbody > tr:nth-child(5) > td > fieldset > label');
		$I->click('Save changes');
		$I->amOnPage('/my-account');
		$I->click('Log out');
		$I->fillField('#username', 'vendor2');
		$I->fillField('#password', '1IZ)h7%J9wQNG@AUqE43y2%c');
		$I->click('Log in');
		$I->click('Vendor Dashboard');
		$I->waitForText('Add New Product', 60);
		$I->click('Add New Product');
		$I->fillField('#title', 'AEP 1');
		$I->click('#in-product_cat-15'); //Setting uncategorized category for automation.
		$I->fillField('#_regular_price', '1000');//Setting the price for automated product.
		$I->executeJS('window.scrollTo(0, 0)');
		$I->waitForElement('#publish', 30);
		$I->wait(5);//Multiple wait statement because of waiting for the same element.
		$I->doubleClick('#publish');
		$I->waitForText('Sorry, you are not allowed to access this page.', 30);
		$I->amOnPage('/my-account');
		$I->click('Log out');
		$I->fillField('#username', 'admin');
		$I->fillField('#password', '123456');
		$I->click('Log in');
		$I->amOnPage('/wp-admin/admin.php?page=wcv-settings&tab=capabilities');
		$I->waitForText('Allow vendors to edit published (live) products', 300);
		$I->executeJS('document.querySelector("#mainform > table:nth-child(10) > tbody > tr:nth-child(2) > td > fieldset > label").click()');
		$I->scrollTo('#mainform > table:nth-child(15) > tbody > tr:nth-child(5) > td > fieldset > label');
		$I->click('Save changes');
    }
}
