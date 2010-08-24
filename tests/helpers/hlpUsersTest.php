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
require_once "src/database.php";

// test specific dependencies
require_once 'tests/models/mocks/remotes.php';
require_once "app/helpers/users.php";

class hlpUsersTest extends PHPUnit_Framework_TestCase 
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
        $this->tester->onSetUp();

        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SERVER_PORT'] = 80;
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * test make_reg_vals_array function
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_make_reg_vals_array()
    {
        $reuslt = make_reg_vals_array(
            'name',
            'name@example.com',
            'password',
            'password varifiy');

        $this->assertEquals($reuslt['name'] ,   'name');
        $this->assertEquals($reuslt['email'] ,  'name@example.com');
        $this->assertEquals($reuslt['pass'] ,   'password');
        $this->assertEquals($reuslt['pass_v'] , 'password varifiy');
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * test log_in_user function
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_log_in_user()
    {
        try
        {
            log_in_user('fred', 1);
            $this->fail();
        }
        catch(exception $e)
        {
            $this->assertEquals(preg_match('/messages/', $e->getMessage()), 1);
        }

        $this->assertEquals($_SESSION['active_user']['name'], 'fred');
        $this->assertEquals($_SESSION['active_user']['id'],   1);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * test make_profile_url function
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_make_profile_url()
    {
        $result = make_profile_url('fred');

        $this->assertEquals(preg_match('/http:\/\/localhost\/.+\/users\/profile\/fred/', $result), 1);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * test validate_username function
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_validate_username_valid()
    {
        validate_username('fred');
    }

// +++++++++++++++++++++
    function test_validate_username_invalid_short()
    {
        $this->setExpectedException('invalid_username_exception');

        validate_username('a');
    }

// +++++++++++++++++++++
    function test_validate_username_invalid_long()
    {
        $this->setExpectedException('invalid_username_exception');

        $username = '';
        for($i = 0; $i < 31; $i ++)
            $username .= 'a';

        validate_username($username);
    }

// +++++++++++++++++++++
    function test_validate_username_invalid_bad_chars()
    {
        $this->setExpectedException('invalid_username_exception');

        validate_username('invalid +++++_+_++::');
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * test validate_password function
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_validate_password_valid()
    {
        validate_password('/-\pa$$\/\/0r3');
    }

// +++++++++++++++++++++
    function test_validate_password_invalid()
    {
        $this->setExpectedException('invalid_password_exception');

        validate_password('cat');
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * test validate_bio function
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_validate_bio_valid()
    {
        $bio = '';
        for($i = 0; $i < 160; $i ++)
            $bio .= 'く';

        validate_bio($bio);
    }

// +++++++++++++++++++++
    function test_validate_bio_invalid()
    {
        $this->setExpectedException('invalid_bio_exception');

        $bio = '';
        for($i = 0; $i < 161; $i ++)
            $bio .= 'a';

        validate_bio($bio);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * test validate_50 function
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_validate_50_valid()
    {
        $str = '';
        for($i = 0; $i < 50; $i ++)
            $str .= 'か';

        validate_50($str);
    }

// +++++++++++++++++++++
    function test_validate_50_invalid()
    {
        $this->setExpectedException('over_50_exception');

        $str = '';
        for($i = 0; $i < 51; $i ++)
            $str .= 'a';

        validate_50($str);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * test validate_avatar function
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_validate_avatar_valid()
    {
        validate_avatar(APP_ROOT . 'media/default_avatar.jpg');
    }

// +++++++++++++++++++++
    function test_validate_avatar_invalid_url()
    {
        $this->setExpectedException('invalid_url_exception');

        validate_avatar('invalid_url');
    }

// +++++++++++++++++++++
    function test_validate_avatar_dead_url()
    {
        $this->setExpectedException('invalid_avatar_exception');

        validate_avatar(APP_ROOT . 'media/nosuchfolder/default_avatar.jpg');
    }

// +++++++++++++++++++++
    function test_validate_avatar_wrong_size()
    {
        $this->setExpectedException('invalid_avatar_exception');

        validate_avatar(APP_ROOT . 'tests/models/files/bad_avatar.jpg');
    }
}
