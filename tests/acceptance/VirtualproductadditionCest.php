<?php

class VirtualproductadditionCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->amOnPage('/');
		$I->click('My account');
		$I->fillField('#username', 'vendor1');
		$I->fillField('#password', '#*mr4Xk)R2l)W^XuI^P*85jP');
		$I->click('Log in');
    }

    // Vendors adds a virtual product and customer purchase the virtual product successfully and checks after the purchase made.
    public function frontpageWorks(AcceptanceTester $I)
    {
		$I->amOnPage('/wp-admin/edit.php?post_type=product');//Navigating to the product listing page.
		$I->see('Import');
		$I->doubleClick('#wpbody-content > div.wrap > a:nth-child(2)');//Clicking on to add a new products
		$I->fillField('#title', 'AVP1');
		$I->click('#in-product_cat-15'); //Adding uncategorized category for automation.
		$I->fillField('#_regular_price', '233');//Setting the price for automated product.
		$I->scrollTo('#product_catdiv > div.postbox-header > h2');
		$I->click("//*[@id='_virtual']"); //Setting the product to be virtual
		$I->wait(4);
		$I->dontSee('Shipping');
		$I->scrollTo('#title');
		$I->doubleClick('#publish');
		$I->scrollTo('#wpbody-content > div.wrap > h1');
		$I->see('Product published. View Product');
		//$I->executeJS('document.querySelector("#show-settings-link").scrollIntoView()');
		$I->executeJS('window.scrollTo(0, 0)');
		$I->click('View Product');
		$I->see('AVP1');
		//Logging out as Vendor and logging in as Customer to place order.
		$I->click('My account');
		$I->click('Log out');
		$I->fillField('#username', 'customer1');
		$I->fillField('#password', 'dM^gc87RPE&Osuj(EKPY)X8(');
		$I->click('Log in');
		$I->fillField('#woocommerce-product-search-field-0', 'AVP1');//searching for the product added by vendor.
		$I->pressKey('#woocommerce-product-search-field-0', \Facebook\WebDriver\WebDriverKeys::ENTER);//Was difficult to find the exact syntax to pass enter key lol.
		$I->wait(5);
		$I->scrollTo('#main > div:nth-child(2) > form > select');//This will require 2 products with same name or simply run the script twice to add the product twice.
		$I->click('Add to cart');
		$I->wait(10);
		$I->amOnPage('/cart');//Change in URL was the only option left
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
		$I->fillField('#post-search-input', 'AVP1');//searching for the product added by vendor.
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
		$I->see('AVP1');
    }
}