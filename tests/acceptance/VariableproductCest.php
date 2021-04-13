<?php

class VariableproductCest
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

    // Adding a variable product and will be bought by customer and made sure is delivered after the purchase is marked completed by Admin.
    public function tryToTest(AcceptanceTester $I)
    {
		$I->amOnPage('/wp-admin/post-new.php?post_type=product');//Navigating direct to product addition form.
		$I->see('Publish immediately');
		$I->fillField('#title', 'Var Pro 1');
		$I->click('#product_cat-15 > label');
		$I->click('#product-type');
		$I->wait(2);//Waiting for the drop down to load correct for click
		$I->click('#product-type > optgroup > option:nth-child(4)');
		$I->dontSee('General');
		$I->click('#_manage_stock');
		$I->fillField('#_stock', '100');
		$I->click('Variations');
		$I->see('Before you can add a variation you need to add some variation attributes on the Attributes tab.');
		$I->click('Attributes');
		$I->click('//*[@id="product_attributes"]/div[1]/button');
		$I->waitForText('Used for variations', 300);//Will also make sure that we have the product type set as variable.
		$I->click('//*[@id="product_attributes"]/div[2]/div/div/table/tbody/tr[3]/td/div/label');
		$I->fillField('#product_attributes > div.product_attributes.wc-metaboxes.ui-sortable > div > div > table > tbody > tr:nth-child(1) > td.attribute_name > input.attribute_name', 'Sizes');
		$I->fillField('//*[@id="product_attributes"]/div[2]/div/div/table/tbody/tr[1]/td[2]/textarea', 'S|M|L|XL|XXL');
		$I->click('Save attributes');
		$I->wait(2);
    }
}
