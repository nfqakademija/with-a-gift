<?php


class CreateGiftListFormCest
{
    public function createGiftList(AcceptanceTester $I)
    {
        $I->amOnPage('/create');
        $I->fillField('#gift_list_firstName', 'Hulk');
        $I->fillField('#gift_list_email', 'hulk@email.com');
        $I->fillField('#gift_list_title', 'Party');
        $I->fillField('#gift_list_description', 'Some description');
        $I->seeElement('.btn');
        $I->seeCheckboxIsChecked('gift_list[gifts][0][reservable]');
        $I->clickWithLeftButton('#gift_list_Save');
        $I->moveForward();
    }
}
