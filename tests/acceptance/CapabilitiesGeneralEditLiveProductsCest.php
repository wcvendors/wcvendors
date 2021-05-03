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
		$I->waitForText('Product published. View Product', 30);
    }
}
