<?php

class VendorResetPasswordCest
{
    public function _before(AcceptanceTester $I)
    {
		$I->amOnPage('/');
        $I->see('wcvendors');
		$I->click('My account');
    }

    // Restting password for Vendor [Not working completely as getnada blocks the automation process.] Will need a more reliable test email service provider, the test is incomplete.
    public function tryToTest(AcceptanceTester $I)
    {
		$I->scrollTo('#username');
		$I->click('Lost your password');
		$I->waitForText('Lost password');
		$I->scrollTo('#post-9 > header > h1');
		$I->fillField('#user_login', 'vendor.four@robot-mail.com');
		$I->click('Reset password');
		$I->waitForText('Password reset email has been sent', 20);
		//Retreiving the password url from temp email
		$I->amOnURL('https://getnada.com/');
		$I->waitForText('Disposable Temporary Email', 20);
		$I->click('#__layout > div > div > div.container.mx-auto.px-4.mb-20 > nav > div > div > ul:nth-child(2) > li:nth-child(1) > button');
		$I->waitForText('Add inbox', 10);
		$I->fillField('#grid-first-name', 'vendor.four');
		$I->executeJS('document.querySelector("#__layout > div > div > div.container.mx-auto.px-4.mb-20 > nav > div.fixed.inset-0.w-full.h-screen.flex.items-center.justify-center.bg-smoke-800.z-50 > div > div > div > form > div > div.inline-block.relative.w-64 > select")');
		$I->wait(2);
		$I->click('#__layout > div > div > div.container.mx-auto.px-4.mb-20 > nav > div.fixed.inset-0.w-full.h-screen.flex.items-center.justify-center.bg-smoke-800.z-50 > div > div > div > form > div > div.inline-block.relative.w-64 > select > option:nth-child(9)');
		$I->executeJS('document.querySelector("#__layout > div > div > div.container.mx-auto.px-4.mb-20 > nav > div.fixed.inset-0.w-full.h-screen.flex.items-center.justify-center.bg-smoke-800.z-50 > div > div > div > form > button").click()');
		$I->wait(2);
		$I->scrollTo('#__layout > div > div > div.container.mx-auto.px-4.mb-20 > div > div.relative.dark\:bg-gray-600.bg-yellow-400.md\:pt-32.pb-32.pt-12 > div > div > div:nth-child(3) > div.ease-in-out.mb-8 > button');
		$I->click('#__layout > div > div > div.container.mx-auto.px-4.mb-20 > div > div.relative.dark\:bg-gray-600.bg-yellow-400.md\:pt-32.pb-32.pt-12 > div > div > div:nth-child(3) > div.ease-in-out.mb-8 > button');
		$I->waitForText('Someone has requested a new password for the following account on wcvendors:', 300);
		$I->scrollTo('#body_content_inner > p:nth-child(2)');
		$I->see('Click here to reset your password');
		$I->click('Click here to reset your password');
		I.switchToNextTab();
    }
}
