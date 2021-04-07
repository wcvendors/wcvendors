<?php

class VendoraddingregularproductCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->amOnPage('/');
		$I->click('My account');
		$I->fillField('#username', 'vendor1');
		$I->fillField('#password', 'mr4Xk)R2l)W^XuI^P85jP');
		$I->click('#customer_login > div.u-column1.col-1 > form > p:nth-child(3) > button');
    }

    // Entire flow of a regular product being added by a vendor and the same product being purchased by a customer successfully.
    public function frontpageWorks(AcceptanceTester $I)
    {
		$I->amOnPage('/wp-admin');//Launching the WordPress Admin panel for vendor as adding products using the admin panel.
		$I->click('Products');
		$I->see('Search Products');
		$I->click('#wpbody-content > div.wrap > a:nth-child(2)');//Clicking on to add a new products
		$I->fillField('#title', 'Automated Product Y KOne');
		$I->click('#in-product_cat-15'); //Setting uncategorized category for automation.
		$I->fillField('#_regular_price', '200');//Setting the price for automated product.
		$I->scrollTo('#title');
		$I->click('#publish');
		$I->see('Product published. View Product');
		$I->click('View Product');
		$I->see('Automated Product Y KOne');
		$I->click('My account');
		$I->click('Log out');
		$I->fillField('#username', 'customer1');
		$I->fillField('#password', 'dM^gc87RPE&Osuj(EKPY)X8(');
		$I->click('Log in');
		$I->fillField('#woocommerce-product-search-field-0', 'Automated Product Y KOne');//searching for the product added by vendor.
		$I->pressKey('#input', WebDriverKeys::ENTER);
		$I->click('Add to cart');
		$I->see('has been added to your cart.');
		$I->click('View cart');
		$I->scrollTo('Proceed to checkout');
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
		$I->click('#payment > ul > li.wc_payment_method.payment_method_wcvendors_test_gateway > label'); //Clicking the WC Vendors Test Gateway for payment.
		$I->see('This is a test gateway — not to be used on live sites for live transactions. Click here to visit WCVendors.com.');//Make sure that the test gateway is set correct.
		$I->scrollTo('Place order');
		$I->click('Place order');
		$I->see('Order received');
		$I->see('Thank you. Your order has been received.');
		$I->scrollTo('#post-8 > div > div > div > ul > li.woocommerce-order-overview__email.email');
		$I->see('automation.customer.one@yopmail.com');
    }
}