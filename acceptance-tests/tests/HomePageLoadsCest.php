<?php


class HomePageLoadsCest
{
    public function homePageLoads(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->see('Think of event. Create a Gift list. Share it with friends. And get awesome presents!');
        $I->seeElement('.btn');
    }
}
