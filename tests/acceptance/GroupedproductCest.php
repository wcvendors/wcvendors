<?php

class GroupedproductCest
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

    // Adding the grouped product.
    public function tryToTest(AcceptanceTester $I)
    {
		$I->amOnPage('/wp-admin/post-new.php?post_type=product');//Navigating direct to product addition form.
		$I->see('Publish immediately');
		$I->fillField('#title', 'Grouped 1');
		$I->scrollTo('#product_catdiv > div.postbox-header > h2');
		$I->click('#product_cat-15 > label');
		$I->click('#product-type');
		$I->wait(2);//Waiting for the drop down to load complete before clicking.
		$I->click('#product-type > optgroup > option:nth-child(2)');
		$I->dontSee('General');
		$I->click('Linked Products');
		$I->wait(1);
		$I->fillField('//*[@id="linked_product_data"]/div[1]/p/span[1]/span[1]/span/ul/li/input', 'Var');
		//$I->waitForElement('#select2-grouped_products-results > li:nth-child(1)', 30);
		$I->wait(30);
		$I->click('#select2-grouped_products-results > li:nth-child(1)');
		$I->scrollTo('#title');
		$I->doubleClick('#publish');
		$I->scrollTo('#show-settings-link');
		$I->waitForText('Product published. View Product', 300);
		$I->click('View Product');
		$I->see('Grouped 1');
    }
}
