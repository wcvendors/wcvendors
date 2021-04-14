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
		$I->scrollTo('#product_catdiv > div.postbox-header > h2');
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
		$I->fillField('//*[@id="product_attributes"]/div[2]/div/div/table/tbody/tr[1]/td[2]/textarea', 'Small|Medium');
		$I->click('Save attributes');
		$I->wait(20);
		$I->scrollTo('#wp-word-count');
		$I->click('#product_tag > div > div.ajaxtag.hide-if-no-js > input.button.tagadd');
		$I->click('Variations');//XPath because required consistent click.
		$I->wait(20);//You can remove the wait if required.
		$I->dontSee('Before you can add a variation you need to add some variation attributes on the Attributes tab.');
		$I->wait(2);
		$I->click('#field_to_edit');
		$I->wait(2);
		$I->click('#field_to_edit > option:nth-child(2)');
		$I->click('Go');
		$I->acceptPopup();
		$I->wait(30);
		//$I->executeJS('if(window.alert()) window.alert().accept(); sleep(1)');
		$I->wait(2);
		$I->acceptPopup();
		$I->waitForText('variations do not have prices. Variations (and their attributes) that do not have prices will not be shown in your store.', 300);
		$I->click('#variable_product_options_inner > div.woocommerce_variations.wc-metaboxes.ui-sortable > div:nth-child(2) > h3 > strong');
		$I->waitForText('Sale price (₹) Schedule', 10);
		$I->fillField('#variable_regular_price_0', 121);
		$I->click('#variable_product_options_inner > div.woocommerce_variations.wc-metaboxes.ui-sortable > div:nth-child(2) > h3 > strong');
		$I->click('#postimagediv > div.postbox-header > h2');
		$I->click('#variable_product_options_inner > div.woocommerce_variations.wc-metaboxes.ui-sortable > div:nth-child(3) > h3 > strong');
		$I->waitForText('Sale price (₹) Schedule', 10);
		$I->fillField('#variable_regular_price_1', 51);
		$I->click('#variable_product_options_inner > div.woocommerce_variations.wc-metaboxes.ui-sortable > div:nth-child(3) > h3 > strong');
		$I->click('#postimagediv > div.postbox-header > h2');
		$I->click('Save changes');
		$I->wait(3);
		$I->scrollTo('#title');
		$I->doubleClick('#publish');
		$I->scrollTo('#wpbody-content > div.wrap > h1');
		$I->see('Product published. View Product');
		$I->click('View Product');
		$I->see('Var Pro 1');
    }
}
