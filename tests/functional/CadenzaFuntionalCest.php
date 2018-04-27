<?php


class CadenzaFuntionalCest
{
    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    // tests
    public function tryToTest(FunctionalTester $I)
    {
    }

    public function frontpageWorks(FunctionalTester $I)
    {
        $I->amOnPage('/index.php');
        $I->see('Cadenza');  
    }

    public function loginPageWorks(FunctionalTester $I)
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
    public function signupPageWorks(FunctionalTester $I)
    {
        $I->amOnPage('/signup/signup.php');
        $I->see('Cadenza');
        $I->fillField('name', 'Ahmed Mustafa');
        $I->fillField('email', 'moonahmed786@gmail.com');
        $I->fillField('username', 'moonahmed786');
        $I->fillField('password', '123123123');
        $I->fillField('password_confirm', '123123123');
        $I->fillField('user_type', 'student');
        $I->click('Sign Up');

    }

    public function loginPageSubmitWorks(FunctionalTester $I)
    {
        $I->amOnPage('/login.php');
        $userType = 'user=1';
        $message = 'login';
        $I->fillField('username', 'moonahmed786@gmail.com');
        $I->fillField('password', 'ahmed@2255');
        $I->submitForm('#id_form_login_login_btn', array('user' => $userType , 'message' => $message));
        $I->see('"loginsuccess":true');
    }

    public function signupPageSubmitWorks(FunctionalTester $I)
    {
            $I->amOnPage('/signup/signup.php');
            $I->see('Cadenza');
            $I->fillField('name', 'Test User');
            $I->fillField('email', 'testuser@gmail.com');
            $I->fillField('username', 'testuser');
            $I->fillField('password', '123123123');
            $I->fillField('password_confirm', '123123123');
            $I->fillField('user_type', 'student');
            $name = 'Test User';
            $email = 'testuser@gmail.com';
            $username = 'testuser';
            $password = '123123123';
            $password_confirm = '123123123';
            $user_type =  'student';
            $I->submitForm('#id_form_signup_signup_btn', array ('name' => $name, 'email' => $email, 'username'=> $username, 'password' => $password, 'password_confirm' => $password_confirm, 'user_type' => $user_type));
            $I->see('Cadenza');
    }
}
