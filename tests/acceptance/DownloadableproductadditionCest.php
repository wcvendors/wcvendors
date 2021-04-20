<?php

class DownloadableproductadditionCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->amOnPage('/');
		$I->click('My account');
		$I->fillField('#username', 'vendor1');
		$I->fillField('#password', '#*mr4Xk)R2l)W^XuI^P*85jP');
		$I->click('#customer_login > div.u-column1.col-1 > form > p:nth-child(3) > button');
    }

    //adding a downloadable product and purchasing the product and approving the purchase and downloading the purchased product.
    public function frontpageWorks(AcceptanceTester $I)
    {
		$I->amOnPage('/wp-admin');
		$I->executeJS('document.querySelector("#menu-posts-product > a > div.wp-menu-name").click()');
		$I->see('Search Products');
		$I->wait(5);
		$I->executeJS('document.querySelector("#wpbody-content > div.wrap > a:nth-child(2)").click()');
		$I->wait(3);
		$I->fillField('#title', 'ADP 1');
		$I->click('#in-product_cat-15'); //Adding uncategorized category for automation.
		$I->fillField('#_regular_price', '239');//Setting the price for automated product.
		$I->scrollTo('#wp-word-count');//This function is not working
		$I->executeJS('document.querySelector("#_downloadable").click()');//Setting the product to be downloadabled via clicking the downloadable checkbox.
		$I->executeJS('document.querySelector("#general_product_data > div.options_group.show_if_downloadable.hidden > div > table > tfoot > tr > th > a").click()');
		$I->wait(5);
		$I->fillField('#general_product_data > div.options_group.show_if_downloadable.hidden > div > table > tbody > tr > td.file_name > input.input_text', 'auto downloadable');
		$I->fillField('#general_product_data > div.options_group.show_if_downloadable.hidden > div > table > tbody > tr > td.file_url > input', 'http://localhost/wordpress/wp-content/uploads/woocommerce_uploads/2021/03/wcv_commissions_sum-2021-Feb-11-3n6sds.csv'); // You will need to upload a file and change the path accroding to your local setup.
		//$I->scrollTo('#title');
		$I->executeJS('window.scrollTo(0, 0)');
		$I->doubleClick('#publish');
		//$I->scrollTo('#wpbody-content > div.wrap > h1');
		$I->executeJS('window.scrollTo(0, 0)');
		$I->see('Product published. View Product');
		$I->click('View Product');
		$I->see('ADP 1');
		$I->click('My account');
		$I->click('Log out');
		//Login in as customer to place the purchase order for downloadable product added above.
		$I->fillField('#username', 'customer1');
		$I->fillField('#password', 'dM^gc87RPE&Osuj(EKPY)X8(');
		$I->click('Log in');
		$I->fillField('#woocommerce-product-search-field-0', 'ADP 1');//searching for the product added by vendor.
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
		//Product purchased by the customer
		//Loggin in as Admin to complete the purchase.
		//$I->amOnPage('/my-account');//Navigation to accounts page to log out.
		$I->fillField('#username', 'admin');
		$I->fillField('#password', '123456');
		$I->click('Log in');
		$I->amOnPage('/wp-admin');
		$I->executeJS('document.querySelector("#toplevel_page_woocommerce > a > div.wp-menu-name").click()');
		$I->executeJS('document.querySelector("#toplevel_page_woocommerce > ul > li:nth-child(3) > a").click()');
		$I->fillField('#post-search-input', 'ADP 1');//searching for the product added by vendor.
		$I->pressKey('#post-search-input', \Facebook\WebDriver\WebDriverKeys::ENTER);
		$I->wait(3);
		$I->executeJS('document.querySelector("#cb-select-345").click()');
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
		$I->click('#post-9 > div > div > nav > ul > li.woocommerce-MyAccount-navigation-link.woocommerce-MyAccount-navigation-link--downloads > a');//Clicking on download to check all the download options.
		$I->see('Downloads remaining');
		$I->see('auto downloadable');
    }
}