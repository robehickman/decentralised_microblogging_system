<?php
/*
 * Copyright 2010 Robert Hickman
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
 * implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Extensions/Database/TestCase.php';
require_once 'PHPUnit/Extensions/Database/DataSet/FlatXmlDataSet.php';

// framework stuff
require_once 'config_tests.php';
require_once 'src/common.php';
require_once 'src/database.php';
require_once 'src/controller.php';
require_once 'src/view.php';

// test specific dependencies
require_once 'app/models/users.php';
require_once 'app/helpers/users.php';
require_once 'app/controllers/users.php';

class ctrlUsersTest extends PHPUnit_Framework_TestCase 
{
/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Setup
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    var $tester = null;

    function __construct()
    {
    // connect for unit testing framework
        $pdo = new PDO('mysql:host=' . HOST . ';dbname=' .
            DB_NAME, USERNAME, PASSWORD);

        $connection = new PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection($pdo, 'test');
        $tester     = new PHPUnit_Extensions_Database_DefaultTester($connection);

        $tester->setSetUpOperation(PHPUnit_Extensions_Database_Operation_Factory::CLEAN_INSERT());
        $tester->setTearDownOperation(PHPUnit_Extensions_Database_Operation_Factory::NONE());
        $tester->setDataSet(new PHPUnit_Extensions_Database_DataSet_FlatXMLDataSet
            (dirname(__FILE__).'/../models/files/users.xml'));

        $this->tester = $tester;
    }

// +++++++++++++++++++++
    function setUp()
    {
        $GLOBALS['errors'] = array();
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SERVER_PORT'] = 80;
        $_SESSION['flash'] = array();
        $this->tester->onSetUp();
    }

// +++++++++++++++++++++
    function tearDown()
    {
        $_SESSION = array();
        $_POST = array();
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test login method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_login_form()
    {
        $usr = new ctrl_users();
        $usr->login();
        $result = $usr->display_outer(true);

    // check existance of HTML form
        $this->assertEquals(preg_match("/<h2>Sign in<\/h2>/", $result), 1);
        $this->assertEquals(preg_match("/<form .+ id=\"login\">/", $result), 1);
        $this->assertEquals(preg_match("/<input name=\"user\".+>/", $result), 1);
        $this->assertEquals(preg_match("/<input name=\"pass\".+>/", $result), 1);
        $this->assertEquals(preg_match("/<div\ +id=\"errors\">/", $result), 0);
    }

// +++++++++++++++++++++
    function test_login_valid()
    {
        $_POST = array(
            'user' => 'fred',
            'pass' => 'aaaaaa',
            'Submit' => 'Submit');

        $usr = new ctrl_users();

        try
        {
            $usr->login();
            $this->fail();
        }
        catch(exception $e)
        {
            $this->assertEquals(preg_match("/messages/", $e->getMessage()), 1);
        }
    }

// +++++++++++++++++++++
    function test_login_incorrect_user_pass()
    {
        $_POST = array(
            'user' => 'd',
            'pass' => 'aa',
            'Submit' => 'Submit');

        $usr = new ctrl_users();

        $usr->login();
        $result = $usr->display_outer(true);

    // Check that form is redisplayed with invalid user/pass error 
        $this->assertEquals(preg_match("/<div\ +id=\"errors\">/", $result), 1);

        $this->assertEquals(preg_match
            ("/<li>Username or password is incorrect<\/li>/", $result), 1);

        $this->assertEquals(preg_match("/<form .+ id=\"login\">/", $result), 1);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test logout method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_logout()
    {
        $_SESSION['active_user'] = array('name' => 'fred', 'id' => 1);

        $usr = new ctrl_users();

        try
        {
            $usr->logout();

            $this->fail();
        }
        catch(exception $e)
        {
            $this->assertEquals(preg_match("/messages/", $e->getMessage()), 1);
        }

        $this->assertEquals($_SESSION, array());
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test register method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_register_form()
    {
        $usr = new ctrl_users();
        $usr->register();
        $result = $usr->display_outer(true);

    // check existance of HTML form
        $this->assertEquals(preg_match("/<h2>Register<\/h2>/", $result), 1);
        $this->assertEquals(preg_match("/<input name=\"name\".+>/", $result), 1);
        $this->assertEquals(preg_match("/<input name=\"email\".+>/", $result), 1);
        $this->assertEquals(preg_match("/<input name=\"pass\".+>/", $result), 1);
        $this->assertEquals(preg_match("/<input name=\"pass_v\".+>/", $result), 1);
        $this->assertEquals(preg_match("/<div\ +id=\"errors\">/", $result), 0);
    }

// +++++++++++++++++++++
    function test_register_valid()
    {
        $_POST = array(
            'name'   => 'test',
            'email'  => 'test@example.com',
            'pass'   => 'aaaaaa',
            'pass_v' => 'aaaaaa',
            'Submit' => 'Submit');

        $usr = new ctrl_users();

        try
        {
            $usr->register();
            $this->fail();
        }
        catch(exception $e)
        {
            $this->assertEquals(preg_match("/messages/", $e->getMessage()), 1);
        }

        $users = instance_model('users');
        $user = $users->get_user_by_name('test');

        $this->assertFalse($user == array());
    }

// +++++++++++++++++++++
    function test_register_invalid_username_short()
    {
        $_POST = array(
            'name'   => '',
            'email'  => 'abcdefg@example.com',
            'pass'   => 'aaaaaa',
            'pass_v' => 'aaaaaa',
            'Submit' => 'Submit');

        $usr = new ctrl_users();
        $usr->register();

        $result = $usr->display_outer(true);

    // Check page redisplay with invalid username error
        $this->assertEquals(preg_match("/<h2>Register<\/h2>/", $result), 1);
        $this->assertEquals(preg_match("/<input name=\"name\".+>/", $result), 1);
        $this->assertEquals(preg_match("/<div\ +id=\"errors\">/", $result), 1);
        $this->assertEquals(preg_match("/<li>User name too short/", $result), 1);
    }

// +++++++++++++++++++++
    function test_register_invalid_username_long()
    {
        $_POST = array(
            'name'   => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            'email'  => 'abcdefg@example.com',
            'pass'   => 'aaaaaa',
            'pass_v' => 'aaaaaa',
            'Submit' => 'Submit');

        $usr = new ctrl_users();
        $usr->register();

        $result = $usr->display_outer(true);

    // Check page redisplay with invalid username error
        $this->assertEquals(preg_match("/<h2>Register<\/h2>/", $result), 1);
        $this->assertEquals(preg_match("/<input name=\"name\".+>/", $result), 1);
        $this->assertEquals(preg_match("/<div\ +id=\"errors\">/", $result), 1);
        $this->assertEquals(preg_match("/<li>User name too long/", $result), 1);
    }

// +++++++++++++++++++++
    function test_register_invalid_username_bad_chars()
    {
        $_POST = array(
            'name'   => '@++++{}',
            'email'  => 'abcdefg@example.com',
            'pass'   => 'aaaaaa',
            'pass_v' => 'aaaaaa',
            'Submit' => 'Submit');

        $usr = new ctrl_users();
        $usr->register();

        $result = $usr->display_outer(true);

    // Check page redisplay with invalid username error
        $this->assertEquals(preg_match("/<h2>Register<\/h2>/", $result), 1);
        $this->assertEquals(preg_match("/<input name=\"name\".+>/", $result), 1);
        $this->assertEquals(preg_match("/<div\ +id=\"errors\">/", $result), 1);
        $this->assertEquals(preg_match("/only alphanumeric charicters/", $result), 1);
    }

// +++++++++++++++++++++
    function test_register_duplicate_username()
    {
        $_POST = array(
            'name'   => 'fred',
            'email'  => 'fred@example.com',
            'pass'   => 'aaaaaa',
            'pass_v' => 'aaaaaa',
            'Submit' => 'Submit');

        $usr = new ctrl_users();
        $usr->register();

        $result = $usr->display_outer(true);

    // Check page redisplay with invalid username error
        $this->assertEquals(preg_match("/<h2>Register<\/h2>/", $result), 1);
        $this->assertEquals(preg_match("/<input name=\"name\".+>/", $result), 1);
        $this->assertEquals(preg_match("/<div\ +id=\"errors\">/", $result), 1);
        $this->assertEquals(preg_match("/<li>User name is already tacken/", $result), 1);
    }

// +++++++++++++++++++++
    function test_register_invalid_email()
    {

        $_POST = array(
            'name'   => 'abcdefg',
            'email'  => 'abcdefgexamplecom',
            'pass'   => 'aaaaaa',
            'pass_v' => 'aaaaaa',
            'Submit' => 'Submit');

        $usr = new ctrl_users();
        $usr->register();

        $result = $usr->display_outer(true);

    // Check page redisplay with invalid username error
        $this->assertEquals(preg_match("/<h2>Register<\/h2>/", $result), 1);
        $this->assertEquals(preg_match("/<input name=\"name\".+>/", $result), 1);
        $this->assertEquals(preg_match("/<div\ +id=\"errors\">/", $result), 1);
        $this->assertEquals(preg_match("/<li>Email address is invalid/", $result), 1);
    }

// +++++++++++++++++++++
    function test_register_invalid_password()
    {
        $_POST = array(
            'name'   => 'abcdefg',
            'email'  => 'abcdefg@example.com',
            'pass'   => 'aaaaa',
            'pass_v' => 'aaaaa',
            'Submit' => 'Submit');

        $usr = new ctrl_users();
        $usr->register();

        $result = $usr->display_outer(true);

    // Check page redisplay with invalid username error
        $this->assertEquals(preg_match("/<h2>Register<\/h2>/", $result), 1);
        $this->assertEquals(preg_match("/<input name=\"name\".+>/", $result), 1);
        $this->assertEquals(preg_match("/<div\ +id=\"errors\">/", $result), 1);
        $this->assertEquals(preg_match("/<li>Password too short/", $result), 1);
    }

// +++++++++++++++++++++
    function test_register_mismatched_passwords()
    {
        $_POST = array(
            'name'   => 'abcdefg',
            'email'  => 'abcdefg@example.com',
            'pass'   => 'aaaaa',
            'pass_v' => 'aaaaa',
            'Submit' => 'Submit');

        $usr = new ctrl_users();
        $usr->register();

        $result = $usr->display_outer(true);

    // Check page redisplay with invalid username error
        $this->assertEquals(preg_match("/<h2>Register<\/h2>/", $result), 1);
        $this->assertEquals(preg_match("/<input name=\"name\".+>/", $result), 1);
        $this->assertEquals(preg_match("/<div\ +id=\"errors\">/", $result), 1);
        $this->assertEquals(preg_match("/<li>Password too short/", $result), 1);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test index method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_index()
    {
        $usr = new ctrl_users();
        $usr->index();
        $result = $usr->display_outer(true);

        $this->assertEquals(preg_match("/<h2>Users on this node<\/h2>/", $result), 1);
        $this->assertEquals(preg_match("/<h4>.+fred<\/a><\/h4>/", $result), 1);
        $this->assertEquals(preg_match("/<h4>.+sue<\/a><\/h4>/", $result), 1);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test profile method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_profile_valid_user()
    {
        $params = array(
            'users',
            'profile',
            'fred');

        $usr = new ctrl_users();
        $usr->params = $params;
        $usr->profile();
        $result = $usr->display_outer(true);
        $this->assertEquals(preg_match("/<h2>fred/", $result), 1);
        $this->assertEquals(preg_match("/Message 2 by fred/", $result), 1);
        $this->assertEquals(preg_match("/Message by fred/", $result), 1);
        $this->assertEquals(preg_match("/Name.+Fred jones/", $result), 1);
        $this->assertEquals(preg_match("/Location.+The moon/", $result), 1);
        $this->assertEquals(preg_match('/alt="freds avatar"/', $result), 1);
    }

// +++++++++++++++++++++
    function test_profile_no_user()
    {
        $usr = new ctrl_users();

        try
        {
            $usr->profile();
            $this->fail();
        }
        catch(exception $e)
        {
            $this->assertEquals(preg_match("/users/", $e->getMessage()), 1);
        }

    // After redirect, should display error
        $usr->index();
        $result = $usr->display_outer(true);
        $this->assertEquals(preg_match("/The specified user does not exist/", $result), 1);
    }

// +++++++++++++++++++++
    function test_profile_invalid_user()
    {
        $params = array(
            'users',
            'profile',
            'no_such_user++');

        $usr = new ctrl_users();
        $usr->params = $params;

        try
        {
            $usr->profile();
            $this->fail();
        }
        catch(exception $e)
        {
            $this->assertEquals(preg_match("/users/", $e->getMessage()), 1);
        }

    // After redirect, should display error
        $usr->index();
        $result = $usr->display_outer(true);
        $this->assertEquals(preg_match("/The specified user does not exist/", $result), 1);
    }
}
