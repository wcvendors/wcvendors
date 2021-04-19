<?php

class VendorResetPasswordCest
{
    public function _before(AcceptanceTester $I)
    {
		$I->amOnPage('/');
        $I->see('wcvendors');
		$I->click('My account');		
    }

    // Restting password for Vendor
    public function tryToTest(AcceptanceTester $I)
    {
		$I->scrollTo('#username');
		$I->click('Lost your password');
		$I->waitForText('Lost password');
		$I->scrollTo('#post-9 > header > h1');
		$I->fillField('#user_login', 'guru.bhargav@robot-mail.com');
		$I->click('Reset password');
		$I->waitForText('Password reset email has been sent', 20);
		//Retreiving the password url from temp email
		$I->amOnURL('https://getnada.com/');
		$I->waitForText('Disposable Temporary Email', 20);
		$I->click('ADD INBOXE');
		$I->waitForText('Add inbox', 10);
		$I->fillField('#grid-first-name', 'guru.bhargav');
		$I->click('#__layout > div > div > div.container.mx-auto.px-4.mb-20 > nav > div.fixed.inset-0.w-full.h-screen.flex.items-center.justify-center.bg-smoke-800.z-50 > div > div > div > form > div > div.inline-block.relative.w-64 > select');
		$I->wait(2);
		$I->click('#__layout > div > div > div.container.mx-auto.px-4.mb-20 > nav > div.fixed.inset-0.w-full.h-screen.flex.items-center.justify-center.bg-smoke-800.z-50 > div > div > div > form > div > div.inline-block.relative.w-64 > select > option:nth-child(9)');
		$I->click('ADD NOW!');
		$I->wait(2);
    }
}
