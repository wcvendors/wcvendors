<?php

class VendorloginCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->see('wcvendors');
    }

    // Simple Vendor Login check.
    public function frontpageWorks(AcceptanceTester $I)
    {
		$I->click('My account');
		$I->fillField('#username', 'vendor2');
		$I->fillField('#password', '1IZ)h7%J9wQNG@AUqE43y2%c');
		$I->click('#customer_login > div.u-column1.col-1 > form > p:nth-child(3) > button');
		$I->see('Hello Vendor');
    }
}