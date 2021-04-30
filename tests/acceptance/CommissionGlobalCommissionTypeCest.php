<?php

class CommissionGlobalCommissionTypeCest
{
    public function _before(AcceptanceTester $I)
    {
		//Admin making sure that the percentage set for commission is 40 while an order is made.
		$I->amOnPage('/my-account');
    }

    // Evaluating all the different Global Commission Types to compare as per the type set to the commission generated at the commissions table. 
    public function tryToTest(AcceptanceTester $I)
    {
		$I->fillField('#username', 'vendor2');
		$I->fillField('#password', '1IZ)h7%J9wQNG@AUqE43y2%c');
		$I->click('Log in');
		$I->click('Vendor Dashboard');
		$I->waitForText('Add New Product', 60);
		$I->click("Add New Product");
		$I->waitForElement('#title', 300);
		$I->fillField('#title', 'Regular product with 100 unit amount');
		$I->click('#in-product_cat-15');
		$I->fillField('#_regular_price', '100');
		$I->scrollTo('#wp-word-count');
		$I->executeJS('document.querySelector("#_downloadable").click()');
		$I->executeJS('document.querySelector("#general_product_data > div.options_group.show_if_downloadable.hidden > div > table > tfoot > tr > th > a").click()');
		$I->wait(5);
		$I->fillField('#general_product_data > div.options_group.show_if_downloadable.hidden > div > table > tbody > tr > td.file_name > input.input_text', 'auto downloadable');
		$I->fillField('#general_product_data > div.options_group.show_if_downloadable.hidden > div > table > tbody > tr > td.file_url > input', 'http://localhost/wordpress/wp-content/uploads/woocommerce_uploads/2021/03/wcv_commissions_sum-2021-Feb-11-3n6sds.csv'); // You will need to upload a file and change the path accroding to your local setup.
		$I->executeJS('window.scrollTo(0, 0)');
		$I->doubleClick('#publish');
		$I->executeJS('window.scrollTo(0, 0)');
		$I->see('Product published. View Product');
		$I->amOnPage('/my-account');
		$I->click('Log out');
		$I->fillField('#username', 'customer1');
		$I->fillField('#password', 'dM^gc87RPE&Osuj(EKPY)X8(');
		$I->click('Log in');
		$I->fillField('#woocommerce-product-search-field-0', 'Regular product with 100 unit amount');//searching for the product added by vendor.
		$I->pressKey('#woocommerce-product-search-field-0', \Facebook\WebDriver\WebDriverKeys::ENTER);
		$I->wait(5);
		$I->scrollTo('#main > div:nth-child(2) > form > select');
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
		//Admin checking for the commission
		$I->fillField('#username', 'admin');
		$I->fillField('#password', '123456');
		$I->click('Log in');
		$I->amOnPage('/wp-admin/admin.php?page=wcv-commissions');
		$I->waitForText('40', 100, '#the-list > tr:nth-child(1) > td.total_due.column-total_due > span > bdi');
    }
}
