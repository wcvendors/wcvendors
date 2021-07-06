<?php

class CapabilitiesGeneralEditApprovedProductsCest
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

    // validating -  Publish edits to approved products. ( New products will still have to be approved )
    public function tryToTest(AcceptanceTester $I)
    {
		$I->click('Vendor Dashboard');
		$I->waitForText('Add New Product', 60);
		$I->click('Add New Product');
		$I->waitForElement('#publish', 300);
		$I->fillField('#title', 'A New product');
		$I->doubleClick('#publish');
		$I->waitForText('Product published. View Product', 120);
		$I->fillField('#title', 'Modified product name');
		$I->doubleClick('#publish');
		$I->waitForText('Product updated. View Product', 120);
		
		$I->amOnPage('/my-account');
		$I->click('Log out');
		$I->fillField('#username', 'admin');
		$I->fillField('#password', '123456');
		$I->click('Log in');
		$I->amOnPage('/wp-admin/admin.php?page=wcv-settings&tab=capabilities');
		$I->waitForText('Allow vendors to publish products directly to the marketplace without requiring approval.', 300);
		$I->executeJS('document.querySelector("#mainform > table:nth-child(10) > tbody > tr:nth-child(4) > td > fieldset > label").click()');
		$I->scrollTo('#mainform > table:nth-child(15) > tbody > tr:nth-child(5) > td > fieldset > label');
		$I->click('Save changes');
		$I->waitForText('Your settings have been saved.', 300);
		
		$I->amOnPage('/my-account');
		$I->click('Log out');
		$I->fillField('#username', 'vendor2');
		$I->fillField('#password', '1IZ)h7%J9wQNG@AUqE43y2%c');
		$I->click('Log in');
		$I->click('Vendor Dashboard');
		$I->waitForText('Add New Product', 60);
		$I->click('Add New Product');
		$I->waitForElement('#publish', 300);
		$I->fillField('#title', 'A new product added');
		$I->doubleClick('#publish');
		$I->waitForText('Product published. View Product', 120);
		$I->fillField('#title', 'Modified product name');
		$I->doubleClick('#publish');
		$I->waitForText('Product updated. View Product', 300);
		
		$I->amOnPage('/my-account');
		$I->click('Log out');
		$I->fillField('#username', 'admin');
		$I->fillField('#password', '123456');
		$I->click('Log in');
		$I->amOnPage('/wp-admin/admin.php?page=wcv-settings&tab=capabilities');
		$I->waitForText('Allow vendors to publish products directly to the marketplace without requiring approval.', 300);
		$I->executeJS('document.querySelector("#mainform > table:nth-child(10) > tbody > tr:nth-child(4) > td > fieldset > label").click()');
		$I->scrollTo('#mainform > table:nth-child(15) > tbody > tr:nth-child(5) > td > fieldset > label');
		$I->click('Save changes');
		$I->waitForText('Your settings have been saved.', 300);
    }
}
