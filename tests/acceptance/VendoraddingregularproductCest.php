<?php

class VendoraddingregularproductCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->amOnPage('/');
		$I->click('My account');
		$I->fillField('#username', 'vendor1');
		$I->fillField('#password', '#*mr4Xk)R2l)W^XuI^P*85jP');
		$I->click('#customer_login > div.u-column1.col-1 > form > p:nth-child(3) > button');
    }

    // Entire flow of a regular product being added by a vendor and the same product being purchased by a customer successfully.
    public function frontpageWorks(AcceptanceTester $I)
    {
		$I->amOnPage('/wp-admin');//Launching the WordPress Admin panel for vendor as adding products using the admin panel.
		$I->click('Products');
		$I->see('Add New');
		$I->doubleClick('#wpbody-content > div.wrap > a:nth-child(2)');//Clicking on to add a new products
		$I->fillField('#title', 'Automated Product Y2K2 J');
		$I->click('#in-product_cat-15'); //Setting uncategorized category for automation.
		$I->fillField('#_regular_price', '201');//Setting the price for automated product.
		$I->scrollTo('#show-settings-link');
		$I->waitForElement('#publish', 30);
		$I->wait(5);//Multiple wait statement because of waiting for the same element.
		$I->doubleClick('#publish');
		$I->waitForText('Product published. View Product', 30);
		$I->click('View Product');
		$I->see('Automated Product Y2K2 J');
		$I->click('My account');
		$I->click('Log out');
		$I->fillField('#username', 'customer1');
		$I->fillField('#password', 'dM^gc87RPE&Osuj(EKPY)X8(');
		$I->click('Log in');
		$I->fillField('#woocommerce-product-search-field-0', 'Automated Product Y2K2 J');//searching for the product added by vendor.
		$I->pressKey('#woocommerce-product-search-field-0', \Facebook\WebDriver\WebDriverKeys::ENTER);//Was difficult to find the exact syntax to pass enter key lol.
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
    }
}