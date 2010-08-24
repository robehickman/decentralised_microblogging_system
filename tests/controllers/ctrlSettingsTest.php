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
require_once 'app/models/messages.php';
require_once 'app/models/message_cache.php';
require_once 'app/models/relations.php';
require_once 'tests/models/mocks/remotes.php';
require_once 'app/helpers/users.php';
require_once 'app/helpers/messages.php';
require_once 'app/controllers/settings.php';

class ctrlSettingsTest extends PHPUnit_Framework_TestCase 
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
        $_SERVER['HTTP_HOST'] = 'localhost';
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
 * Test index method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_index_logged_off()
    {
        $set = new ctrl_settings();

        try
        {
            $set->index();
            $this->fail();
        }
        catch(exception $e)
        {
            $this->assertEquals(preg_match("/users/", $e->getMessage()), 1);
        }
    }

// +++++++++++++++++++++
    function test_index_form()
    {
        $_SESSION['active_user'] = array('name' => 'fred', 'id' => 1);

        $set = new ctrl_settings();
        $set->index();
        $result = $set->display_outer(true);

        $this->assertEquals(preg_match('/<h2>Settings</', $result), 1);
        $this->assertEquals(preg_match('/<input.+>.+<td>Email<\/td>/s', $result), 1);
        $this->assertEquals(preg_match('/<input.+>.+<td>Full name<\/td>/s', $result), 1);
        $this->assertEquals(preg_match('/<input.+>.+<td>Location<\/td>/s', $result), 1);
        $this->assertEquals(preg_match('/<input.+>.+<td>Web<\/td>/s', $result), 1);
        $this->assertEquals(preg_match('/<textarea.+>.+<td>Bio.+<\/td>/s', $result), 1);
    }

// +++++++++++++++++++++
    function test_index_valid()
    {
        $_SESSION['active_user'] = array('name' => 'fred', 'id' => 1);

        $_POST = array(
            '0' => 'fred@example.com',
            '1' => 'Fred jones',
            '2' => 'Under the sea',
            '3' => 'http://fred.example.com',
            '4' => 'A human called fred',
            'Submit' => 'Submit');

        $set = new ctrl_settings();

        try
        {
            $set->index();
            $this->fail();
        }
        catch(exception $e)
        {
            $this->assertEquals(preg_match("/settings/", $e->getMessage()), 1);
        }
    }

// +++++++++++++++++++++
    function test_index_invalid_email()
    {
        $_SESSION['active_user'] = array('name' => 'fred', 'id' => 1);

        $_POST = array(
            '0' => 'invalid',
            '1' => 'Fred jones',
            '2' => 'Under the sea',
            '3' => 'http://fred.example.com',
            '4' => 'A human called fred',
            'Submit' => 'Submit');

        $set = new ctrl_settings();
        $set->index();

        $result = $set->display_outer(true);

        $this->assertEquals(preg_match('/<div id="errors">/', $result), 1);
        $this->assertEquals(preg_match('/<li>Email address is invalid</', $result), 1);
    }

// +++++++++++++++++++++
    function test_index_invalid_full_name()
    {
        $_SESSION['active_user'] = array('name' => 'fred', 'id' => 1);

        $_POST = array(
            '0' => 'fred@example.com',
            '1' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            '2' => 'Under the sea',
            '3' => 'http://fred.example.com',
            '4' => 'A human called fred',
            'Submit' => 'Submit');

        $set = new ctrl_settings();
        $set->index();

        $result = $set->display_outer(true);

        $this->assertEquals(preg_match('/<div id="errors">/', $result), 1);
        $this->assertEquals(preg_match('/<li>Full name is too long.+</', $result), 1);
    }

// +++++++++++++++++++++
    function test_index_invalid_location()
    {
        $_SESSION['active_user'] = array('name' => 'fred', 'id' => 1);

        $_POST = array(
            '0' => 'fred@example.com',
            '1' => 'Fred jones',
            '2' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            '3' => 'http://fred.example.com',
            '4' => 'A human called fred',
            'Submit' => 'Submit');

        $set = new ctrl_settings();
        $set->index();

        $result = $set->display_outer(true);

        $this->assertEquals(preg_match('/<div id="errors">/', $result), 1);
        $this->assertEquals(preg_match('/<li>Location is too long.+</', $result), 1);
    }

// +++++++++++++++++++++
    function test_index_invalid_web()
    {
        $_SESSION['active_user'] = array('name' => 'fred', 'id' => 1);

        $_POST = array(
            '0' => 'fred@example.com',
            '1' => 'Fred jones',
            '2' => 'Under the sea',
            '3' => 'invalid',
            '4' => 'A human called fred',
            'Submit' => 'Submit');

        $set = new ctrl_settings();
        $set->index();

        $result = $set->display_outer(true);

        $this->assertEquals(preg_match('/<div id="errors">/', $result), 1);
        $this->assertEquals(preg_match('/<li>Website URL is invalid</', $result), 1);
    }

// +++++++++++++++++++++
    function test_index_invalid_bio()
    {
        $_SESSION['active_user'] = array('name' => 'fred', 'id' => 1);

        $_POST = array(
            '0' => 'fred@example.com',
            '1' => 'Fred jones',
            '2' => 'Under the sea',
            '3' => 'http://fred.example.com',
            '4' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            'Submit' => 'Submit');

        $set = new ctrl_settings();
        $set->index();

        $result = $set->display_outer(true);

        $this->assertEquals(preg_match('/<div id="errors">/', $result), 1);
        $this->assertEquals(preg_match('/<li>Bio is invalid</', $result), 1);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test password method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_password_logged_off()
    {
        $set = new ctrl_settings();

        try
        {
            $set->password();
            $this->fail();
        }
        catch(exception $e)
        {
            $this->assertEquals(preg_match("/users/", $e->getMessage()), 1);
        }
    }

// +++++++++++++++++++++
    function test_password_form()
    {
        $_SESSION['active_user'] = array('name' => 'fred', 'id' => 1);

        $set = new ctrl_settings();
        $set->password();
        $result = $set->display_outer(true);

        $this->assertEquals(preg_match('/<h2>Password</', $result), 1);
        $this->assertEquals(preg_match('/<input.+>.+<td>Old password<\/td>/s', $result), 1);
        $this->assertEquals(preg_match('/<input.+>.+<td>New password<\/td>/s', $result), 1);
        $this->assertEquals(preg_match('/<input.+>.+<td>Verify new password<\/td>/s', $result), 1);
    }

// +++++++++++++++++++++
    function test_password_valid()
    {
        $_SESSION['active_user'] = array('name' => 'fred', 'id' => 1);

        $_POST = array(
            '0' => 'aaaaaa',
            '1' => 'bbbbbb',
            '2' => 'bbbbbb',
            'Submit' => 'Submit');

        $set = new ctrl_settings();

        try
        {
            $set->password();
            $this->fail();
        }
        catch(exception $e)
        {
            $this->assertEquals(preg_match("/password/", $e->getMessage()), 1);
        }
    }

// +++++++++++++++++++++
    function test_password_invalid_old()
    {
        $_SESSION['active_user'] = array('name' => 'fred', 'id' => 1);

        $_POST = array(
            '0' => 'wrong',
            '1' => 'bbbbbb',
            '2' => 'bbbbbb',
            'Submit' => 'Submit');

        $set = new ctrl_settings();
        $set->password();

        $result = $set->display_outer(true);

        $this->assertEquals(preg_match('/<div id="errors">/', $result), 1);
        $this->assertEquals(preg_match('/<li>Old password is incorrect</', $result), 1);

    }

// +++++++++++++++++++++
    function test_password_invalid_new()
    {
        $_SESSION['active_user'] = array('name' => 'fred', 'id' => 1);

        $_POST = array(
            '0' => 'aaaaaa',
            '1' => 'bbb',
            '2' => 'bbb',
            'Submit' => 'Submit');

        $set = new ctrl_settings();
        $set->password();

        $result = $set->display_outer(true);

        $this->assertEquals(preg_match('/<div id="errors">/', $result), 1);
        $this->assertEquals(preg_match('/<li>New password too short.+</', $result), 1);
    }

// +++++++++++++++++++++
    function test_password_mismatched_new()
    {
        $_SESSION['active_user'] = array('name' => 'fred', 'id' => 1);

        $_POST = array(
            '0' => 'aaaaaa',
            '1' => 'bbbbbb',
            '2' => 'cccccc',
            'Submit' => 'Submit');

        $set = new ctrl_settings();
        $set->password();

        $result = $set->display_outer(true);

        $this->assertEquals(preg_match('/<div id="errors">/', $result), 1);
        $this->assertEquals(preg_match('/<li>Passwords do not match</', $result), 1);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test avatar method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_avatar_logged_off()
    {
        $set = new ctrl_settings();

        try
        {
            $set->avatar();
            $this->fail();
        }
        catch(exception $e)
        {
            $this->assertEquals(preg_match("/users/", $e->getMessage()), 1);
        }
    }

// +++++++++++++++++++++
    function test_avatar_form()
    {
        $_SESSION['active_user'] = array('name' => 'fred', 'id' => 1);

        $set = new ctrl_settings();
        $set->avatar();
        $result = $set->display_outer(true);

        $this->assertEquals(preg_match('/<h2>Avatar</', $result), 1);
        $this->assertEquals(preg_match('/<input.+type="file".+>/', $result), 1);
    }
}
