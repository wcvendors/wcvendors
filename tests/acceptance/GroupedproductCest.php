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
		$I->amOnPage('/my-account');
		$I->click('Log out');
		//Customer to make the purchase.
		$I->fillField('#username', 'customer1');
		$I->fillField('#password', 'dM^gc87RPE&Osuj(EKPY)X8(');
		$I->click('Log in');
		$I->fillField('#woocommerce-product-search-field-0', 'Grouped 1');//searching for the product added by vendor.
		$I->pressKey('#woocommerce-product-search-field-0', \Facebook\WebDriver\WebDriverKeys::ENTER);//Was difficult to find the exact syntax to pass enter key lol.
		$I->waitForText('Search results', 300);
		$I->scrollTo('#main > div:nth-child(2) > form > select');//This will require 2 products with same name or simply run the script twice to add the product twice.
		$I->click('View products');
		$I->waitForText('Var Pro 1', 300);
		$I->scrollTo('#page > div.storefront-breadcrumb > div > nav > a:nth-child(3)');
		$I->click('Select options');
		$I->waitForText('Var Pro 1', 300);
		$I->scrollTo('#page > div.storefront-breadcrumb > div > nav > a:nth-child(3)');
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
    }
}
