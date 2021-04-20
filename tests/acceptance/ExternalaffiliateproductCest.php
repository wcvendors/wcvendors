<?php

class ExternalaffiliateproductCest
{
    public function _before(AcceptanceTester $I)
    {
		$I->amOnPage('/');
        $I->see('wcvendors');
		$I->click('My account');
		$I->fillField('#username', 'vendor1');
		$I->fillField('#password', '#*mr4Xk)R2l)W^XuI^P*85jP');
		$I->click('Log in');		
    }

    // Adding an external or affiliate product.
    public function tryToTest(AcceptanceTester $I)
    {
		$I->amOnPage('/wp-admin/post-new.php?post_type=product');//Navigating direct to product addition form.
		$I->see('Publish immediately');
		$I->fillField('#title', 'XP1');
		$I->scrollTo('#product_catdiv > div.postbox-header > h2');
		$I->click('#product_cat-15 > label');
		$I->click('#product-type');
		$I->wait(2);//Waiting for the drop down to load complete before clicking.
		$I->click('#product-type > optgroup > option:nth-child(3)');
		$I->dontSee('Shipping');
		$I->fillField('#_product_url', 'http://localhost/wordpress/product/external-affiliate-product-for-automation/'); //ATM it is redirected to product added to locally.
		$I->fillField('#_button_text', 'External Product');
		$I->fillField('#_regular_price', '410');
		//$I->scrollTo('#show-settings-link');
		$I->executeJS('window.scrollTo(0, 0)');
		$I->doubleClick('#publish');
		$I->waitForText('Product published. View Product', 300);
		$I->executeJS('window.scrollTo(0, 0)');
		$I->click('View Product');
		$I->see('XP1');
		$I->amOnPage('/my-account');
		$I->click('Log out');
    }
}
