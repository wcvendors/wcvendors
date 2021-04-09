<?php

class VirtualproductadditionCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->amOnPage('/');
		$I->click('My account');
		$I->fillField('#username', 'vendor1');
		$I->fillField('#password', '#*mr4Xk)R2l)W^XuI^P85jP');
		$I->click('#customer_login > div.u-column1.col-1 > form > p:nth-child(3) > button');
    }

    // Vendors adds a virtual product.
    public function frontpageWorks(AcceptanceTester $I)
    {
		$I->amOnPage('/wp-admin');
		$I->click('Products');//clicking on products
		$I->see('Search Products');
		$I->doubleClick('#wpbody-content > div.wrap > a:nth-child(2)');//Clicking on to add a new products
		$I->wait(10);
		$I->fillField('#title', 'Automated Virtual Product 1');
		$I->click('#in-product_cat-15'); //Adding uncategorized category for automation.
		$I->fillField('#_regular_price', '233');//Setting the price for automated product.
		$I->scrollTo('#wp-word-count');//This function is not working
		$I->click("//*[@id='_virtual']"); //Setting the product to be virtual
		$I->dontSee('Shipping');
		$I->scrollTo('#title');
		$I->doubleClick('#publish');
		$I->scrollTo('#wpbody-content > div.wrap > h1');
		$I->see('Product published. View Product');
		$I->click('View Product');
		$I->see('Automated Virtual Product 1');
    }
}