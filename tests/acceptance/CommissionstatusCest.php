<?php

class CommissionstatusCest
{
    public function _before(AcceptanceTester $I)
    {
		//Customer login to make an order
		$I->amOnPage('/');
		$I->click('My account');
		$I->fillField('#username', 'customer1');
		$I->fillField('#password', 'dM^gc87RPE&Osuj(EKPY)X8(');
		$I->click('Log in');
    }

    // Validate commission status while order made using different payment methods. (that is Test Gateway, Cash on delivery, stripe, direct bank transfer, paypal and check.)
    public function tryToTest(AcceptanceTester $I)
    {
		//Order made using Test Payment Gateway
		$I->fillField('#woocommerce-product-search-field-0', 'AVP1');//searching for the product added by vendor.
		$I->pressKey('#woocommerce-product-search-field-0', \Facebook\WebDriver\WebDriverKeys::ENTER);//Was difficult to find the exact syntax to pass enter key lol.
		$I->waitForElement('#main > div:nth-child(4) > p', 500);
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
		//Second order made using Direct bank transfer
		$I->executeJS('window.scrollTo(0, 0)');
		$I->fillField('#woocommerce-product-search-field-0', 'AVP1');//searching for the product added by vendor.
		$I->pressKey('#woocommerce-product-search-field-0', \Facebook\WebDriver\WebDriverKeys::ENTER);//Was difficult to find the exact syntax to pass enter key lol.
		//$I->wait(5);
		$I->waitForElement('#main > div:nth-child(4) > p', 500);
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
		//$I->scrollTo('#order_review > table > tfoot > tr.woocommerce-shipping-totals.shipping > th');
		$I->executeJS('document.querySelector("#payment > ul > li.wc_payment_method.payment_method_bacs > label").click()');//Direct bank transfer
		$I->waitForText('Make your payment directly into our bank account. Please use your Order ID as the payment reference. Your order will not be shipped until the funds have cleared in our account.', 20);//Make sure that the test gateway is set correct.
		$I->executeJS('document.querySelector("#place_order").click()');
		$I->waitForText('Order received', 300);
		$I->see('Thank you. Your order has been received.');
		$I->scrollTo('#post-8 > div > div > div > ul > li.woocommerce-order-overview__email.email');
		$I->see('automation.customer.one@yopmail.com');
		//Third order made using Check payments.
		$I->executeJS('window.scrollTo(0, 0)');
		$I->fillField('#woocommerce-product-search-field-0', 'AVP1');//searching for the product added by vendor.
		$I->pressKey('#woocommerce-product-search-field-0', \Facebook\WebDriver\WebDriverKeys::ENTER);//Was difficult to find the exact syntax to pass enter key lol.
		$I->waitForElement('#main > div:nth-child(4) > p', 500);
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
		//$I->scrollTo('#order_review > table > tfoot > tr.woocommerce-shipping-totals.shipping > th');
		$I->executeJS('document.querySelector("#payment > ul > li.wc_payment_method.payment_method_cheque > label").click()');//Check payments
		$I->waitForText('Please send a check to Store Name, Store Street, Store Town, Store State / County, Store Postcode.', 20);//Make sure that the test gateway is set correct.
		$I->executeJS('document.querySelector("#place_order").click()');
		$I->waitForText('Order received', 300);
		$I->see('Thank you. Your order has been received.');
		$I->scrollTo('#post-8 > div > div > div > ul > li.woocommerce-order-overview__email.email');
		$I->see('automation.customer.one@yopmail.com');
		//Order made using Cash on delivery
		$I->executeJS('window.scrollTo(0, 0)');
		$I->fillField('#woocommerce-product-search-field-0', 'AVP1');//searching for the product added by vendor.
		$I->pressKey('#woocommerce-product-search-field-0', \Facebook\WebDriver\WebDriverKeys::ENTER);//Was difficult to find the exact syntax to pass enter key lol.
		$I->waitForElement('#main > div:nth-child(4) > p', 500);
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
		$I->scrollTo('#payment > ul > li.wc_payment_method.payment_method_bacs > label');
		$I->executeJS('document.querySelector("#payment > ul > li.wc_payment_method.payment_method_cod > label").click()');//Cash On Delivery
		$I->waitForText('Pay with cash upon delivery.', 20);//Make sure that the test gateway is set correct.
		$I->executeJS('document.querySelector("#place_order").click()');
		$I->waitForText('Order received', 300);
		$I->see('Thank you. Your order has been received.');
		$I->scrollTo('#post-8 > div > div > div > ul > li.woocommerce-order-overview__email.email');
		$I->see('automation.customer.one@yopmail.com');
		$I->amOnPage('/my-account');
		$I->waitForText('Hello Automation Customer', 300);
		$I->click('Log out');
		$I->fillField('#username', 'admin');
		$I->fillField('#password', '123456');
		$I->click('Log in');
		$I->amOnPage('/wp-admin/edit.php?post_type=shop_order');
		$I->waitForElement('#search-submit');
		$I->fillField('#post-search-input', 'AVP1');//searching for the product added by vendor.
		$I->pressKey('#post-search-input', \Facebook\WebDriver\WebDriverKeys::ENTER);
		$I->executeJS('document.querySelector("#cb-select-all-1").click()');
		$I->click('#bulk-action-selector-top');
		$I->wait(2);
		$I->click('#bulk-action-selector-top > option:nth-child(6)');
		$I->click('#doaction');
		$I->waitForText('order status changed.', 300);
		$I->amOnPage('/wp-admin/admin.php?page=wcv-commissions');
		//$I->dontSee('No items found.');
		$I->executeJS('document.querySelector("#cb-select-all-1").click()');
		$I->executeJS('document.querySelector("#bulk-action-selector-top").click()');
		$I->wait(2);
		$I->executeJS('document.querySelector("#bulk-action-selector-top > option:nth-child(2)").click()');
		$I->executeJS('document.querySelector("#doaction").click()');
		$I->waitForText('Commission marked paid.', 300);
    }
}
