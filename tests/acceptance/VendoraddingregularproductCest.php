<?php

class VendoraddingregularproductCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->amOnPage('/');
		$I->click('My account');
		$I->fillField('#username', 'howdyvendor');
		$I->fillField('#password', 'k@sperskyPure3.0');
		$I->click('#customer_login > div.u-column1.col-1 > form > p:nth-child(3) > button');
    }

    // tests
    public function frontpageWorks(AcceptanceTester $I)
    {
		$I->amOnPage('/wp-admin');//Launching the WordPress Admin panel for vendor as adding products using the admin panel.
		$I->click('Products');
		$I->see('Search Products');
		$I->click('#wpbody-content > div.wrap > a:nth-child(2)');//Clicking on to add a new products
		$I->fillField('#title', 'Automated Product Two KOne');
		$I->click('#in-product_cat-15'); //Setting uncategorized category for automation.
		$I->fillField('#_regular_price', '200');//Setting the price for automated product.
		$I->scrollTo('#title');
		$I->click('#publish');
		$I->see('Product published. View Product');
		$I->click('View Product');
		$I->see('Automated Product Two');
    }
}