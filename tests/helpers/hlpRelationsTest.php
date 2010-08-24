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
require_once "app/helpers/relations.php";

class hlpRelationsTest extends PHPUnit_Framework_TestCase 
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
* Test get_following function
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_get_following()
    {
        $result = get_following(1);

        $this->assertEquals($result, 1);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
* Test get_followers function
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_get_followers()
    {
        $result = get_followers(2);

        $this->assertEquals($result, 1);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
* Test make_follow_url function
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_make_follow_url()
    {
        $result = make_follow_url('fred');

        $this->assertEquals($result, 'http://localhost/usr/bin/messages/follow/fred');
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
* Test get_latest_by_remote function
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_get_latest_by_remote()
    {
        $rmt_mock = new mck_mdl_remotes('','');

        $result = get_latest_by_remote($rmt_mock, 'http://localhost/messages/follow/fred');

        $this->assertEquals($result[0]['ID'], 3);
        $this->assertEquals($result[0]['Remote_URL'], 'http://localhost/messages/follow/fred');
        $this->assertEquals($result[0]['Remote_name'], 'fred');
        $this->assertEquals($result[0]['Remote_message'], 'Message 2 by fred');
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
* Test get_latest_by_local function
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_get_latest_by_local()
    {
        $result = get_latest_by_local(2);

        $this->assertEquals($result[0]['ID'], 1);
        $this->assertEquals($result[0]['Message'], 'Message by sue');
    }
}
