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
require_once 'app/controllers/dmessages.php';

class ctrlDmessagesTest extends PHPUnit_Framework_TestCase 
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
 * Test public_msg method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_public_msg_logged_out()
    {
        $dm = new ctrl_dmessages();

        try
        {
            $dm->public_msg();
            $this->fail();
        }
        catch(exception $e)
        {
            $this->assertEquals(preg_match("/users/", $e->getMessage()), 1);
        }
    }

// +++++++++++++++++++++
    function test_public_msg_logged_in()
    {
        $_SESSION['active_user'] = array('name' => 'fred', 'id' => 1);

        $dm = new ctrl_dmessages();
        $dm->public_msg();
        $result = $dm->display_outer(true);

        $this->assertEquals(preg_match('/>sue</', $result), 1);
        $this->assertEquals(preg_match('/@fred a message from sue/', $result), 1);
        $this->assertEquals(preg_match('/alt=\"sues avatar\"/', $result), 1);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test public_msg method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_destroy_public_logged_out()
    {
        $dm = new ctrl_dmessages();

        try
        {
            $dm->destroy_public();
            $this->fail();
        }
        catch(exception $e)
        {
            $this->assertEquals(preg_match("/users/", $e->getMessage()), 1);
        }
    }

// +++++++++++++++++++++
    function test_destroy_public_logged_in_valid()
    {
        $_SESSION['active_user'] = array('name' => 'fred', 'id' => 1);

        $_POST = array(
            'id'     => '1',
            'Submit' => 'Submit');

        $dm = new ctrl_dmessages();

        try
        {
            $dm->destroy_public();
            $this->fail();
        }
        catch(exception $e)
        {
            $this->assertEquals(preg_match("/dmessages/", $e->getMessage()), 1);
        }

        $query = "SELECT * FROM `direct-message` WHERE `ID` = '1'";

        $result = mysql_query($query);
        $this->assertEquals(mysql_num_rows($result), 0);
    }

// +++++++++++++++++++++
    function test_destroy_public_logged_in_invalid_id()
    {
        $_SESSION['active_user'] = array('name' => 'fred', 'id' => 1);

        $_POST = array(
            'id'     => '100',
            'Submit' => 'Submit');

        $dm = new ctrl_dmessages();

        try
        {
            $dm->destroy_public();
            $this->fail();
        }
        catch(exception $e)
        {
            $this->assertEquals(preg_match("/dmessages/", $e->getMessage()), 1);
        }
    }
}
