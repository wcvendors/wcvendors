<?php

class ProductwithattributesCest
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

    // Adding a new simple product with attributes, will purchase the same and will check the purchase is displayed as expected at the orders for the customer.
    public function tryToTest(AcceptanceTester $I)
    {
		$I->amOnPage('/wp-admin/post-new.php?post_type=product');//Navigating direct to product addition form.
		$I->see('Publish immediately');
		$I->fillField('#title', 'AttributesP1');
		$I->click('#in-product_cat-15'); //Adding uncategorized category for automation.
		$I->fillField('#_regular_price', '243');//Setting the price for automated product.
		$I->click('Attributes');
		$I->scrollTo('#wp-word-count');
		$I->click('//*[@id="product_attributes"]/div[1]/button');
		$I->waitForText('Visible on the product page', 300);
		$I->fillField('//*[@id="product_attributes"]/div[2]/div/div/table/tbody/tr[1]/td[1]/input[1]','Color');
		$I->fillField('//*[@id="product_attributes"]/div[2]/div/div/table/tbody/tr[1]/td[2]/textarea', 'S|M|L|XL|XXL');
		$I->wait(2);
		$I->click('Save attributes');
		$I->waitForElement('#product_attributes > div.product_attributes.wc-metaboxes.ui-sortable > div > h3 > strong', 300);
		$I->click('Linked Products');
		$I->waitForText('Upsells');
		$I->click('//*[@id="linked_product_data"]/div[2]/p[1]/span[1]/span[1]/span/ul/li/input');
		$I->fillField('//*[@id="linked_product_data"]/div[2]/p[1]/span[1]/span[1]/span/ul/li/input', 'AVP');//Setting upsells value for the product.
		$I->wait(5);
		$I->click('#select2-upsell_ids-results > li:nth-child(1)');
		$I->scrollTo('#title');
		$I->doubleClick('#publish');
		$I->scrollTo('#title');
		$I->doubleClick('#publish');
		$I->scrollTo('#wpbody-content > div.wrap > h1');
		$I->see('Product published. View Product');
		$I->click('View Product');
		$I->see('AttributesP1');
    }
}
