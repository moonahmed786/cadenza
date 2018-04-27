<?php


class CadenzaUnitCest
{
    public function _before(UnitTester $I)
    {
    }

    public function _after(UnitTester $I)
    {
    }

    // tests
    public function tryToTest(UnitTester $I)
    {
    }


    public function frontpageWorks(AcceptanceTester $I)
    {
        $I->amOnPage('/index.php');
        $I->see('Cadenza');  
    }

    public function testLoginWorks(AcceptanceTester $I)
    {
        $I->amOnPage('/login.php');
        $I->see('Cadenza');
        $I->fillField('username', 'moonahmed786@gmail.com');
        $I->fillField('password', 'ahmed@2255');
        $I->click('Login');
    }

    // public function afterLoginWorks(AcceptanceTester $I)
    // {
    //     $I->amOnPage('/student/teachers.php');
    //     $I->see('Cadenza');
    // }
}
