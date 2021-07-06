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
		$I->wait(60);
		//$I->executeJS('{window.alert()? window.alert().accept() : sleep(1)}');
		$I->wait(2);
		$I->acceptPopup();
		$I->waitForText('variations do not have prices. Variations (and their attributes) that do not have prices will not be shown in your store.', 300);
		$I->click('#variable_product_options_inner > div.woocommerce_variations.wc-metaboxes.ui-sortable > div:nth-child(2) > h3 > strong');
		$I->waitForText('Sale price (₹) Schedule', 20);
		$I->fillField('#variable_regular_price_0', 121);
		$I->click('#variable_product_options_inner > div.woocommerce_variations.wc-metaboxes.ui-sortable > div:nth-child(2) > h3 > strong');
		$I->click('#postimagediv > div.postbox-header > h2');
		$I->click('#variable_product_options_inner > div.woocommerce_variations.wc-metaboxes.ui-sortable > div:nth-child(3) > h3 > strong');
		$I->waitForText('Sale price (₹) Schedule', 20);
		$I->fillField('#variable_regular_price_1', 51);
		$I->click('#variable_product_options_inner > div.woocommerce_variations.wc-metaboxes.ui-sortable > div:nth-child(3) > h3 > strong');
		$I->click('#postimagediv > div.postbox-header > h2');
		$I->click('Save changes');
		$I->wait(3);
		//$I->scrollTo('#title');
		$I->executeJS('window.scrollTo(0, 0)');
		$I->doubleClick('#publish');
		$I->scrollTo('#show-settings-link');
		$I->waitForText('Product published. View Product', 300);
		$I->executeJS('window.scrollTo(0, 0)');
		$I->click('View Product');
		$I->see('Var Pro 1');
		$I->amOnPage('/my-account');
		$I->click('Log out');
		//Logging as customer to purchase the product.
		$I->fillField('#username', 'customer1');
		$I->fillField('#password', 'dM^gc87RPE&Osuj(EKPY)X8(');
		$I->click('Log in');
		$I->fillField('#woocommerce-product-search-field-0', 'Var Pro 1');//searching for the product added by vendor.
		$I->pressKey('#woocommerce-product-search-field-0', \Facebook\WebDriver\WebDriverKeys::ENTER);//Was difficult to find the exact syntax to pass enter key lol.
		$I->wait(5);
		$I->scrollTo('#main > div:nth-child(2) > form > select');//This will require 2 products with same name or simply run the script twice to add the product twice.
		$I->click('Select options');
		$I->waitForText('Var Pro 1', 300);
		$I->scrollTo('#product-547 > div.summary.entry-summary > h1');
		$I->click('#sizes');
		$I->wait(2);
		$I->click('#sizes > option:nth-child(2)');
		$I->click('Add to cart');
		$I->waitForText('has been added to your cart.', 300);
		$I->amOnPage('/cart');
		$I->click('Proceed to checkout');
		$I->scrollTo('#billing_first_name');
		$I->fillField('#billing_first_name', 'Customer');
		$I->fillField('#billing_last_name', 'Automated One');
		$I->scrollTo('#billing_address_1');
		$I->fillField('#billing_address_1', 'sample billing address line');
		$I->scrollTo('#billing_city');
		$I->fillField('#billing_city', 'Nadiad');
		$I->scrollTo('#billing_postcode');
		$I->fillField('#billing_postcode', '387002');
		$I->scrollTo('#billing_phone');
		$I->fillField('#billing_phone', '1234567890');
		$I->scrollTo('#billing_email');
		$I->fillField('#billing_email', 'automation.customer.one@yopmail.com');
		$I->wait(5);
		$I->scrollTo('#payment > ul > li.wc_payment_method.payment_method_paypal > label > img'); //Clicking the WC Vendors Test Gateway for payment.
		$I->executeJS('document.querySelector("#payment > ul > li.wc_payment_method.payment_method_wcvendors_test_gateway > label").click()');
		$I->waitForText('This is a test gateway — not to be used on live sites for live transactions. Click here to visit WCVendors.com.', 20);//Make sure that the test gateway is set correct.
		$I->executeJS('document.querySelector("#place_order").click()');
		$I->waitForText('Order received', 300);
		$I->see('Thank you. Your order has been received.');
		$I->scrollTo('#post-8 > div > div > div > ul > li.woocommerce-order-overview__email.email');
		$I->see('automation.customer.one@yopmail.com');
		$I->amOnPage('/my-account');//Navigation to accounts page to log out.
		$I->click('Log out');
		//Product purchased by the customer
		//Loggin in as Admin to complete the purchase.
		//$I->amOnPage('/my-account');//Navigation to accounts page to log out.
		$I->fillField('#username', 'admin');
		$I->fillField('#password', '123456');
		$I->click('Log in');
		$I->amOnPage('/wp-admin');
		$I->executeJS('document.querySelector("#toplevel_page_woocommerce > a > div.wp-menu-name").click()');
		$I->executeJS('document.querySelector("#toplevel_page_woocommerce > ul > li:nth-child(3) > a").click()');
		$I->fillField('#post-search-input', 'Var Pro 1');//searching for the product added by vendor.
		$I->pressKey('#post-search-input', \Facebook\WebDriver\WebDriverKeys::ENTER);
		$I->wait(3);
		$I->click('//*[@name="post[]"][1]');//Clicking the first order that is visible after searching for the product AVP1
		$I->click('#bulk-action-selector-top');
		$I->wait(2);
		$I->click('#bulk-action-selector-top > option:nth-child(6)');
		$I->wait(2);
		$I->executeJS('document.querySelector("#doaction").click()');
		$I->waitForText('order status changed.', 300);
		$I->amOnPage('/my-account');
		$I->click('Log out');//Admin login out
		//logging back in as customer to check the product is downloadable.
		$I->amOnPage('/my-account');
		$I->fillField('#username', 'customer1');
		$I->fillField('#password', 'dM^gc87RPE&Osuj(EKPY)X8(');
		$I->click('Log in');
		$I->amOnPage('/my-account/orders/');//navigating to the orders page.
		$I->click('#post-9 > div > div > div > table > tbody > tr:nth-child(1) > td.woocommerce-orders-table__cell.woocommerce-orders-table__cell-order-actions > a');//Clicking to view the order details
		$I->see('order details');
		$I->see('Var Pro 1');		
    }
}
