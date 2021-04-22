<?php

class FirstCest
{
    public function _before(AcceptanceTester $I)
    {
		$I->amOnPage('/');
        $I->see('wcvendors');
    }

    // Login password errors validation.
    public function frontpageWorks(AcceptanceTester $I)
    {
        $I->amOnPage('/my-account');
		$I->see('My account');
		$I->fillField('#username', 'admin');
		$I->click('Log in');
		$I->waitForText('Error: The password field is empty.', 300);
		$I->fillField('#password', 'Invalidpassword');
		$I->click('Log in');
		$I->waitForText('Error: The password you entered for the username admin is incorrect. Lost your password?', 300);
    }
}