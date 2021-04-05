<?php

class VendorloginCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->see('wcvendors');
    }

    // tests
    public function frontpageWorks(AcceptanceTester $I)
    {
		$I->click('My account');
		$I->fillField('#username', 'howdyvendor');
		$I->fillField('#password', 'k@sperskyPure3.0');
		$I->click('#customer_login > div.u-column1.col-1 > form > p:nth-child(3) > button');
		$I->see('Hello HowdyVendor');
    }
}