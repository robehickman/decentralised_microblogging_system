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
require_once 'app/models/direct_message.php';

class mdlDirectMessageTest extends PHPUnit_Extensions_Database_TestCase 
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
 * Test new_dm method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_new_dm_valid()
    {
        $dm = new mdl_direct_message();
        $dm->new_dm(1, 'public', 'sue',
            'http://localhost/messages/follow/sue',
            APP_ROOT . 'media/default_avatar.jpg',
            'just a test message', 4000000);

        $query = "SELECT * FROM `direct-message` WHERE `Remote_name` = 'sue' LIMIT 1";
        $this->assertFalse(mysql_query($query) == false);
    }

// +++++++++++++++++++++
    function test_new_dm_invalid_user_id()
    {
        $this->setExpectedException('no_such_user_exception');

        $dm = new mdl_direct_message();
        $dm->new_dm(100, 'public', 'sue',
            'http://localhost/messages/follow/sue',
            APP_ROOT . 'media/default_avatar.jpg',
            'just a test message', 40000000);
    }

// +++++++++++++++++++++
    function test_new_dm_invalid_type()
    {
        $this->setExpectedException('invalid_dm_type_exception');

        $dm = new mdl_direct_message();
        $dm->new_dm(1, 'invalid', 'sue',
            'http://localhost/messages/follow/sue',
            APP_ROOT . 'media/default_avatar.jpg',
            'just a test message', 4000000000);
    }

// +++++++++++++++++++++
    function test_new_dm_invalid_remote_name()
    {
        $this->setExpectedException('invalid_username_exception');

        $dm = new mdl_direct_message();
        $dm->new_dm(1, 'public', 'ue',
            'http://localhost/messages/follow/sue',
            APP_ROOT . 'media/default_avatar.jpg',
            'just a test message', 4000000000);
    }

// +++++++++++++++++++++
    function test_new_dm_invalid_remote_profile()
    {
        $this->setExpectedException('invalid_url_exception');

        $dm = new mdl_direct_message();
        $dm->new_dm(1, 'public', 'sue',
            'invalid_url',
            APP_ROOT . 'media/default_avatar.jpg',
            'just a test message', 5000000000);
    }

// +++++++++++++++++++++
    function test_new_dm_invalid_avatar()
    {
        $this->setExpectedException('invalid_url_exception');

        $dm = new mdl_direct_message();
        $dm->new_dm(1, 'public', 'sue',
            'http://localhost/messages/follow/sue',
            'invalid_url',
            'just a test message', 30000000000);
    }

// +++++++++++++++++++++
    function test_new_dm_invalid_remote_message()
    {
        $this->setExpectedException('invalid_message_exception');

        $dm = new mdl_direct_message();
        $dm->new_dm(1, 'public', 'sue',
            'http://localhost/messages/follow/sue',
            APP_ROOT . 'media/default_avatar.jpg',
            '', 8000000000);
    }


/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test get_by_id method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_get_by_id()
    {
        $dm = new mdl_direct_message();
        $result = $dm->get_by_id(1, 1);

        $this->assertEquals(count($result), 1);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test get_by_user_id method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_get_by_user_id()
    {
        $dm = new mdl_direct_message();
        $result = $dm->get_by_user_id(1);

        $this->assertEquals(count($result), 2);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test delete_by_id method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_delete_by_id()
    {
        $dm = new mdl_direct_message();
        $result = $dm->delete_by_id(1, 1);

        $query = "SELECT * FROM `direct-message` WHERE `ID` = '1'"; 

        $result = mysql_query($query);
        $this->assertEquals(mysql_num_rows($result), 0);
    }
}
