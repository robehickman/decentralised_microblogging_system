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

require_once 'PHPUnit/Extensions/Database/TestCase.php';

require_once 'config_tests.php';
require_once 'src/common.php';
require_once 'src/database.php';
require_once 'app/models/users.php';
require_once 'app/helpers/users.php';
require_once 'app/helpers/messages.php';
require_once 'app/models/messages.php';

class mdlMessagesTest extends PHPUnit_Extensions_Database_TestCase 
{
/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Setup
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    protected function getConnection()
    {
    // connect for unit testing framework
        $pdo = new PDO('mysql:host=' . HOST . ';dbname=' .
            DB_NAME, USERNAME, PASSWORD);
        return $this->createDefaultDBConnection($pdo, DB_NAME);
    }

// +++++++++++++++++++++
    protected function getDataSet()
    {
        return $this->createFlatXMLDataSet(
            dirname(__FILE__).'/files/users.xml');
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test create method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_create_valid()
    {
        $msg = new mdl_messages();
        $msg->create(1, 'a message', 300);

    // check the message now exists in the DB
        $query = "SELECT * FROM `messages` WHERE `Message` = 'a message'";
        $result = mysql_query($query);

        $this->assertEquals(mysql_num_rows($result), 1);
    }

// +++++++++++++++++++++
    function test_create_invalid_user_id()
    {
        $this->setExpectedException('no_such_user_exception');

        $msg = new mdl_messages();
        $msg->create(100, 'a message', 300);
    }

// +++++++++++++++++++++
    function test_create_invalid_message()
    {
        $this->setExpectedException('invalid_message_exception');

        $msg = new mdl_messages();
        $msg->create(1, '', 300);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test get_by_user_id method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_get_by_user_id()
    {
        $msg = new mdl_messages();
        $result = $msg->get_by_user_id(1);

        $this->assertEquals($result[0]['User_ID'], 1);
        $this->assertEquals($result[1]['User_ID'], 1);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test get_by_id method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_get_by_id()
    {
        $msg = new mdl_messages();
        $result = $msg->get_by_id(2, 1);

        $this->assertEquals($result[0]['ID'], 1);
        $this->assertEquals($result[0]['User_ID'], 2);
        $this->assertEquals($result[0]['Message'], 'Message by sue');
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test delete_by_id method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_delete_by_id()
    {
        $msg = new mdl_messages();
        $msg->delete_by_id(2, 1);

        $query = "SELECT * FROM `messages` WHERE `ID` = '1'";

        $result = mysql_query($query);
        $this->assertEquals(mysql_num_rows($result), 0);
    }
}
