<?php

class AdminloginCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->see('wcvendors');
    }

    // Simple admin login test.
    public function frontpageWorks(AcceptanceTester $I)
    {
		$I->click('My account');
		$I->fillField('#username', 'admin');
		$I->fillField('#password', '123456');
		$I->click('#customer_login > div.u-column1.col-1 > form > p:nth-child(3) > button');
		$I->see('Hello admin');
    }
}