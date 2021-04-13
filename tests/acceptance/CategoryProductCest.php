<?php

class CategoryProductCest
{
    public function _before(AcceptanceTester $I)
    {
		$I->amOnPage('/wp-login.php');
        $I->fillField('#user_login', 'admin');
		$I->fillField('#user_pass', '123456');
		$I->click('Log In');
		$I->see('Recently Published');
    }

    // admin adding a new category and deleting the same category after addition.
    public function tryToTest(AcceptanceTester $I)
    {
		$I->click('#menu-posts-product > a > div.wp-menu-name');
		$I->see('Attributes');
		$I->click('#menu-posts-product > ul > li:nth-child(4) > a');
		$I->see('Product Categories');
		$I->fillField('#tag-name',"Auto Cat 1");
		$I->fillField('#tag-slug', 'Automation Slug');
		$I->fillField('#tag-description', 'Automation category sample description');
		$I->click('#_wcv_commission_type');
		$I->wait(1);
		$I->click('#_wcv_commission_type > option:nth-child(4)');//Commission type set as percentage.
		$I->fillField('#_wcv_commission_percent', "30");
		$I->click('#display_type');
		$I->wait(1);
		$I->click('#display_type > option:nth-child(4)');
		$I->click('#submit');
		$I->fillField('#tag-search-input', 'Auto Cat 1');
		$I->pressKey('#tag-search-input', \Facebook\WebDriver\WebDriverKeys::ENTER);
		$I->click('#cb-select-all-1');//selection all the categories displayed after searching for exact category match keyword.
		$I->click('#bulk-action-selector-top');
		$I->wait(1);
		$I->click('#bulk-action-selector-top > option:nth-child(2)');
		$I->click('#doaction');
		$I->see('Categories deleted.');
    }
}
