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
require_once "src/database.php";

// test specific dependencies
require_once "app/helpers/messages.php";

class hlpMessagesTest extends PHPUnit_Framework_TestCase 
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
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
* Test extract_at function
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_extract_at_none()
    {
        $includes_no_at  = "a simple message";
        $result = extract_at($includes_no_at);

        $this->assertEquals($result, array());
    }

// +++++++++++++++++++++
    function test_extract_at_one()
    {
        $includes_one_at = "@fred message to fred";
        $result = extract_at($includes_one_at);

        $this->assertEquals(count($result), 1);
        $this->assertEquals($result[0], 'fred');
    }

// +++++++++++++++++++++
    function test_extract_at_two()
    {
        $includes_two_at = "@fred message to fred via @joe";
        $result = extract_at($includes_two_at);

        $this->assertEquals(count($result), 2);
        $this->assertEquals($result[0], 'fred');
        $this->assertEquals($result[1], 'joe');
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
* Test find_unique_users function
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_find_unique_users()
    {
        $users = array(
            array('Remote_URL' => 'http://localhost/messages/follow/fred'),
            array('Remote_URL' => 'http://localhost/messages/follow/abcder'),
            array('Remote_URL' => 'http://localhost/messages/follow/sue'),
            array('Remote_URL' => 'http://localhost/messages/follow/fred'));

        $result = find_unique_users($users);

        $this->assertEquals(count($result), 3);
        $this->assertEquals(preg_match('/fred/', $result[0]['Remote_URL']), 1);
        $this->assertEquals(preg_match('/abcder/', $result[1]['Remote_URL']), 1);
        $this->assertEquals(preg_match('/sue/', $result[2]['Remote_URL']), 1);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
* Test find_unique_users function
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_validate_message_valid()
    {
        $valid_message = "Valid message";
        validate_message($valid_message);
    }

// +++++++++++++++++++++
    function test_validate_message_invalid_long()
    {
        $this->setExpectedException('invalid_message_exception');

        $invalid_message = "";
        for($i = 0; $i < 141; $i ++)
            $invalid_message .= "x";

        validate_message($invalid_message);
    }

// +++++++++++++++++++++
    function test_validate_message_invalid_short()
    {
        $this->setExpectedException('invalid_message_exception');
        validate_message('');
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
* Test get_message_count function
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_get_message_count()
    {
        $result = get_message_count(1);

        $this->assertEquals($result, 2);
    }
}
