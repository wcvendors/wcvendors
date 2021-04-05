<?php

class Vendoraddingregularproduct1Cest
{
    public function _before(FunctionalTester $I)
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
		$I->amOnPage('/wp-admin');
		$I->click('Products');//clicking on products
		$I->see('Search Products');
		$I->click('#wpbody-content > div.wrap > a:nth-child(2)');//Clicking on to add a new products
		$I->fillField('#title', 'Automated Product Two');
		$I->click('#in-product_cat-15'); //Adding uncategorized category for automation.
		$I->fillField('#_regular_price', '200');//Setting the price for automated product.
		//Unable to set inventory for the product.
		//$I->click('#woocommerce-product-data > div.inside > div > ul > li.inventory_options.inventory_tab.show_if_simple.show_if_variable.show_if_grouped.show_if_external > a');
		//$I->click('#_manage_stock');//setting checkbox to set inventory count
		//$I->fillField('#_stock','200');
		//$I->fillField('#_low_stock_amount', '10');
		$I->scrollTo(['css' => '#publish'], 0, 5);
		//$I->executeJS('window.scrollTo(0,0);');//Want to scroll back to top of the page, in order for publish to be visible
		$I->click('#publish');
		$I->see('Product published. View Product');
		$I->click('View Product');
		$I->see('Automated Product Two');
    }
}