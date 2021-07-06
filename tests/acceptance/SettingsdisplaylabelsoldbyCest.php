<?php

class SettingsdisplaylabelsoldbyCest
{
    public function _before(AcceptanceTester $I)
    {
		$I->amOnPage('/my-account');//Navigation to accounts page to log out.
		$I->fillField('#username', 'admin');
		$I->fillField('#password', '123456');
		$I->click('Log in');
		$I->amOnPage('/wp-admin/admin.php?page=wcv-settings&tab=display&section=labels');
		$I->waitForText('This enables the sold by labels used to show which vendor shop the product belongs to', 200);
		$I->fillField('#wcvendors_label_sold_by', 'Item by');
		$I->scrollTo('#wcvendors_store_total_sales_label');
		$I->click('Save changes');
		$I->waitForText('Your settings have been saved.', 300);
    }

    // Settings Display Label Sold By content to be altered and checked.
    public function tryToTest(AcceptanceTester $I)
    {
		//Logging in as vendor.
		$I->amOnPage('/my-account');//Navigation to accounts page to log out.
		$I->click('Log out');
		$I->fillField('#username', 'vendor1');
		$I->fillField('#password', '#*mr4Xk)R2l)W^XuI^P85jP');
		$I->click('Log in');
		$I->click('Shop');
		$I->see('Item by');
		
		//Reverting back to default
		$I->amOnPage('/my-account');//Navigation to accounts page to log out.
		$I->click('Log out');
		$I->fillField('#username', 'admin');
		$I->fillField('#password', '123456');
		$I->click('Log in');
		$I->amOnPage('/wp-admin/admin.php?page=wcv-settings&tab=display&section=labels');
		$I->waitForText('This enables the sold by labels used to show which vendor shop the product belongs to', 200);
		$I->fillField('#wcvendors_label_sold_by', 'Sold By');
		$I->scrollTo('#wcvendors_store_total_sales_label');
		$I->click('Save changes');
		$I->waitForText('Your settings have been saved.', 300);
    }
}
