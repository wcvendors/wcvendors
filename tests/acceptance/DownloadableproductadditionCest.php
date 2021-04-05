<?php

class DownloadableproductadditionCest
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
		$I->amOnPage('/wp-admin');
		$I->doubleClick('#menu-posts-product > a > div.wp-menu-name');//clicking on products
		$I->see('Search Products');
		$I->wait(5);
		$I->doubleClick('#wpbody-content > div.wrap > a:nth-child(2)');//Clicking on to add a new products
		$I->wait(3);
		$I->fillField('#title', 'Automated Downloadable Product 1');
		$I->click('#in-product_cat-15'); //Adding uncategorized category for automation.
		$I->fillField('#_regular_price', '239');//Setting the price for automated product.
		$I->scrollTo('#wp-word-count');//This function is not working
		$I->click("//*[@id='_downloadable']"); //Setting the product to be virtual
		$I->click('#general_product_data > div.options_group.show_if_downloadable.hidden > div > table > tfoot > tr > th > a');
		$I->wait(5);
		$I->fillField('#general_product_data > div.options_group.show_if_downloadable.hidden > div > table > tbody > tr > td.file_name > input.input_text', 'auto downloadable');
		$I->fillField('#general_product_data > div.options_group.show_if_downloadable.hidden > div > table > tbody > tr > td.file_url > input', 'http://localhost/wordpress/wp-content/uploads/woocommerce_uploads/2021/03/wcv_commissions_sum-2021-Feb-11-3n6sds.csv');
		$I->scrollTo('#title');
		$I->doubleClick('#publish');
		$I->scrollTo('#wpbody-content > div.wrap > h1');
		$I->see('Product published. View Product');
		$I->click('View Product');
		$I->see('Automated Downloadable Product 1');
    }
}