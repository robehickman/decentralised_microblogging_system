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
require_once 'app/models/users.php';
require_once 'app/models/relations.php';
require_once 'tests/models/mocks/remotes.php';
require_once 'app/helpers/users.php';
require_once 'app/helpers/messages.php';
require_once 'app/controllers/messages.php';

class ctrlMessagesTest extends PHPUnit_Framework_TestCase 
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
        $_SERVER['SERVER_NAME'] = 'localhost/';
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
 * Test index method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_index_logged_off()
    {
        $msg = new ctrl_messages();

        try
        {
            $msg->index();
            $this->fail();
        }
        catch(exception $e)
        {
            $this->assertEquals(preg_match("/users/", $e->getMessage()), 1);
        }
    }

// +++++++++++++++++++++
    function test_index_logged_in()
    {
        $_SESSION['active_user'] = array('name' => 'fred', 'id' => 1);

        $rmt_mck = new mck_mdl_remotes('sue', 'Big fat human');

        $msg = new ctrl_messages();
        $msg->index($rmt_mck);
        $result = $msg->display_outer(true);

    // Check that displays ok

        $this->assertEquals(preg_match('/Message by fred/', $result), 1);
        $this->assertEquals(preg_match('/alt="freds avatar"/', $result), 1);
        $this->assertEquals(preg_match('/Message by sue/', $result), 1);
        $this->assertEquals(preg_match('/alt="sues avatar"/', $result), 1);

        $_SESSION = array();
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test follow method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_follow_valid_user()
    {
        $usr = new mdl_users();
        $usr->update_avatar(1, APP_ROOT . 'media/default_avatar.jpg');

        $msg = new ctrl_messages();
        $msg->params = array('messages', 'follow', 'fred');

    // catch result with output buffering
        ob_start();
        $msg->follow();
        $result = ob_get_contents();
        ob_end_clean();

    // run through get_message_stream() to validate
        $rmt = instance_model('remotes');
        $rmt->get_message_stream(APP_ROOT . 'messages/follow/fred',
            $result);
    }

// +++++++++++++++++++++
    function test_follow_invalid_username()
    {
        $usr = new mdl_users();
        $usr->update_avatar(1, APP_ROOT . 'media/default_avatar.jpg');

        $_SERVER['HTTP_HOST'] = 'localhost';

        // catch result with output buffering
        ob_start();

        $msg = new ctrl_messages();
        $msg->params = array('messages', 'follow', 'invalid_+++');
        $msg->follow();

        $result = ob_get_contents();
        ob_end_clean();

        $this->assertEquals($result, 'Invalid username');
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test create method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_create_logged_off()
    {
        $msg = new ctrl_messages();

        try
        {
            $msg->create();
            $this->fail();
        }
        catch(exception $e)
        {
            $this->assertEquals(preg_match("/users\/login/", $e->getMessage()), 1);
        }
    }

// +++++++++++++++++++++
    function test_create_plain()
    {
        $_SESSION['active_user'] = array('name' => 'fred', 'id' => 1);
        $_SESSION['direct_to']   = make_url('messages');

        $_POST = array('message' => 'a new message by fred');

        $msg = new ctrl_messages();
        try
        {
            $msg->create();
            $this->fail();
        }
        catch(exception $e)
        {
            $this->assertEquals(preg_match("/messages/", $e->getMessage()), 1);
        }

    // check message exists
        $query = "SELECT * FROM `messages` where `Message` = 'a new message by fred'";
        $result = mysql_query($query);

        $this->assertEquals(mysql_num_rows($result), 1);
    } 

// +++++++++++++++++++++
    function test_create_at_valid()
    {
        $_SESSION['active_user'] = array('name' => 'fred', 'id' => 1);
        $_SESSION['direct_to']   = make_url('messages');

        $_POST = array('message' => '@sue fred sending a message to sue');

        $rmt_mck = new mck_mdl_remotes('sue', 'Big fat human');

        $msg = new ctrl_messages();

        try
        {
            $msg->create($rmt_mck);
            $this->fail();
        }
        catch(exception $e)
        {
            $this->assertEquals(preg_match("/messages/", $e->getMessage()), 1);
        }

        $this->assertTrue($rmt_mck->calls['send_ping'] > 0);
        $this->assertTrue($rmt_mck->calls['decode_ping_response'] > 0);
    } 

// +++++++++++++++++++++
    function test_create_at_invalid()
    {
        $_SESSION['active_user'] = array('name' => 'fred', 'id' => 1);
        $_SESSION['direct_to']   = make_url('messages');

        $_POST = array('message' => '@nobody fred sending a message to nobody');

        $rmt_mck = new mck_mdl_remotes('sue', 'Big fat human');

        $msg = new ctrl_messages();

        try
        {
            $msg->create($rmt_mck);
            $this->fail();
        }
        catch(exception $e)
        {
            $this->assertEquals(preg_match("/messages/", $e->getMessage()), 1);
        }

    // Check that warning message is displayed
        $msg->__construct();
        $msg->index($rmt_mck);

        $result = $msg->display_outer(true);

        $this->assertEquals(preg_match('/<h4 id="errors_head">Errors/', $result), 1);
        $this->assertEquals(preg_match('/User.+not found/', $result), 1);

        $_SESSION = array();
    } 

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test destroy method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_destroy_logged_out()
    {
        $rmt_mck = new mck_mdl_remotes('sue', 'Big fat human');

        $msg = new ctrl_messages();

        try
        {
            $msg->destroy($rmt_mck);
            $this->fail();
        }
        catch(exception $e)
        {
            $this->assertEquals(preg_match("/users\/login/", $e->getMessage()), 1);
        }
    }

// +++++++++++++++++++++
    function test_destroy_valid()
    {
        $_SESSION['active_user'] = array('name' => 'fred', 'id' => 1);

        $_POST = array(
            'id'     => '2',
            'Submit' => 'Submit');

        $rmt_mck = new mck_mdl_remotes('sue', 'Big fat human');

        $msg = new ctrl_messages();

        try
        {
            $msg->destroy($rmt_mck);
            $this->fail();
        }
        catch(exception $e)
        {
            $this->assertEquals(preg_match("/profile/", $e->getMessage()), 1);
        }

    // check message has bean deleted
        $query = "SELECT * FROM `messages` where `ID` = '2'";
        $result = mysql_query($query);

        $this->assertEquals(mysql_num_rows($result), 0);
    }

// +++++++++++++++++++++
    function test_destroy_invalid()
    {
        $_SESSION['active_user'] = array('name' => 'fred', 'id' => 1);

        $_POST = array(
            'id'     => '200',
            'Submit' => 'Submit');

        $rmt_mck = new mck_mdl_remotes('sue', 'Big fat human');

        $msg = new ctrl_messages();
        try
        {
            $msg->destroy($rmt_mck);
            $this->fail();
        }
        catch(exception $e)
        {
            $this->assertEquals(preg_match("/profile/", $e->getMessage()), 1);
        }
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test update pingback handler
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_ping_update()
    {
        $rmt_mck = new mck_mdl_remotes('sue', 'Big fat human');

        $rmt = new mdl_remotes();
        $_POST['data'] = 
            $rmt->send_ping('http://localhost/',
                'update', 'null', $rmt_mck->get_pub_key(), $rmt_mck->get_priv_key(),
                'http://localhost/messages/follow/fred', true);

        $msg = new ctrl_messages();

    // call ping
        ob_start();
        $msg->ping();
        $result = ob_get_contents();
        ob_end_clean();

        $xml = $rmt->decode_ping_response($result);

        $this->assertTrue($xml->state == 'success');

        $csh = new mdl_message_cache();
        $result = $csh->get_cached_user('http://localhost/messages/follow/fred');

        $this->assertEquals($result[0]['Update_cache'], 1);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test public message pingback handler
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_ping_public()
    {
        $rmt_mck = new mck_mdl_remotes('sue', 'Big fat human');

        $xml = new SimpleXMLElement("<data></data>");
        $xml->addChild('remote_name',    'fred');
        $xml->addChild('remote_profile',  make_profile_url('fred'));
        $xml->addChild('remote_avatar',   APP_ROOT . 'media/default_avatar.jpg');
        $xml->addChild('remote_message', '@sue a message from fred');

        $rmt = new mdl_remotes();
        $_POST['data'] = 
            $rmt->send_ping('http://localhost/',
                'public', 'sue', $rmt_mck->get_pub_key(), $rmt_mck->get_priv_key(),
                $xml->asXML(), true);

        $msg = new ctrl_messages();

    // call ping
        ob_start();
        $msg->ping();
        $result = ob_get_contents();
        ob_end_clean();

        $xml = $rmt->decode_ping_response($result);

        $this->assertTrue($xml->state == 'success');

        $query = "SELECT * FROM `direct-message` WHERE
            `Remote_message` = '@sue a message from fred'";

        $result = mysql_query($query);

        $this->assertEquals(mysql_num_rows($result), 1);
    }
}
