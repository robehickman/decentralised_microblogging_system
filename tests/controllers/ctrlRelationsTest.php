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
require_once 'app/controllers/users.php';
require_once 'app/controllers/relations.php';
require_once 'app/controllers/messages.php';

class ctrlRelationsTest extends PHPUnit_Framework_TestCase 
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
 * Test followers method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_followers_valid_user()
    {
        $rmt_mck = new mck_mdl_remotes('fred', 'Person called fred');

        $rel = new ctrl_relations();
        $rel->params = array('relations', 'followers', 'sue');
        $rel->followers($rmt_mck);
        $result = $rel->display_outer(true);

        $this->assertEquals(preg_match('/>fred</', $result), 1);
        $this->assertEquals(preg_match('/freds avatar/', $result), 1);
        $this->assertEquals(preg_match('/class="msg-content">Message</', $result), 1);
    }

// +++++++++++++++++++++
    function test_followers_no_user()
    {
        $rel = new ctrl_relations();

        $rmt_mck = new mck_mdl_remotes('fred', 'Person called fred');

        try
        {
            $rel->followers($rmt_mck);
            $this->fail();
        }
        catch(exception $e)
        {
            $this->assertEquals(preg_match("/users/", $e->getMessage()), 1);
        }

    // After redirect, should display error
        $usr = new ctrl_users();
        $usr->index();
        $result = $usr->display_outer(true);
        $this->assertEquals(preg_match("/The specified user does not exist/", $result), 1);
    }

// +++++++++++++++++++++
    function test_followers_invalid_user()
    {
        $rel = new ctrl_relations();
        $rel->params = array('relations', 'followers', 'unknows++');

        $rmt_mck = new mck_mdl_remotes('fred', 'Person called fred');

        try
        {
            $rel->followers($rmt_mck);
            $this->fail();
        }
        catch(exception $e)
        {
            $this->assertEquals(preg_match("/users/", $e->getMessage()), 1);
        }

    // After redirect, should display error
        $usr = new ctrl_users();
        $usr->index();
        $result = $usr->display_outer(true);
        $this->assertEquals(preg_match("/The specified user does not exist/", $result), 1);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test following method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_following_valid_user()
    {
        $rmt_mck = new mck_mdl_remotes('sue', 'Big fat human');

        $rel = new ctrl_relations();
        $rel->params = array('relations', 'following', 'fred');
        $rel->following($rmt_mck);
        $result = $rel->display_outer(true);

        $this->assertEquals(preg_match('/>sue</', $result), 1);
        $this->assertEquals(preg_match('/sues avatar/', $result), 1);
        $this->assertEquals(preg_match('/Message/', $result), 1);
    }

// +++++++++++++++++++++
    function test_following_no_user()
    {
        $rel = new ctrl_relations();

        $rmt_mck = new mck_mdl_remotes('sue', 'Big fat human');

        try
        {
            $rel->following($rmt_mck);
            $this->fail();
        }
        catch(exception $e)
        {
            $this->assertEquals(preg_match("/users/", $e->getMessage()), 1);
        }

    // After redirect, should display error
        $usr = new ctrl_users();
        $usr->index();
        $result = $usr->display_outer(true);
        $this->assertEquals(preg_match("/The specified user does not exist/", $result), 1);
    }

// +++++++++++++++++++++
    function test_following_invalid_user()
    {
        $rel = new ctrl_relations();
        $rel->params = array('relations', 'followers', 'unknows++');

        $rmt_mck = new mck_mdl_remotes('sue', 'Big fat human');

        try
        {
            $rel->following($rmt_mck);
            $this->fail();
        }
        catch(exception $e)
        {
            $this->assertEquals(preg_match("/users/", $e->getMessage()), 1);
        }

    // After redirect, should display error
        $usr = new ctrl_users();
        $usr->index();
        $result = $usr->display_outer(true);
        $this->assertEquals(preg_match("/The specified user does not exist/", $result), 1);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test create method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_create_logged_off()
    {
        $rel = new ctrl_relations();

        try
        {
            $rel->create();
            $this->fail();
        }
        catch(exception $e)
        {
            $this->assertEquals(preg_match("/users/", $e->getMessage()), 1);
        }
    }

// +++++++++++++++++++++
    function test_create_logged_in_valid()
    {
        $_SESSION['active_user'] = array('name' => 'sue', 'id' => 2);
        $_POST = array(
            'follow_user' => 'http://localhost/messages/follow/sue',
            'Submit'      => 'Submit');

        $rmt_mck = new mck_mdl_remotes('sue', 'Big fat human');

        $rel = new ctrl_relations();

        try
        {
            $rel->create($rmt_mck);
            $this->fail();
        }
        catch(exception $e)
        {
            $this->assertEquals(preg_match("/messages/", $e->getMessage()), 1);
        }
    }

// +++++++++++++++++++++
    function test_create_logged_in_invalid_url()
    {
        $_SESSION['active_user'] = array('name' => 'sue', 'id' => 2);
        $_POST = array(
            'follow_user' => 'invalid_url',
            'Submit'      => 'Submit');

        $rmt_mck = new mck_mdl_remotes('sue', 'Big fat human');

        $rel = new ctrl_relations();

        try
        {
            $rel->create($rmt_mck);
            $this->fail();
        }
        catch(exception $e)
        {
            $this->assertEquals(preg_match("/messages/", $e->getMessage()), 1);
        }

    // After redirect, should display error
        $usr = new ctrl_messages();
        $usr->index();
        $result = $usr->display_outer(true);

        $this->assertEquals(preg_match("/url is invalid/", $result), 1);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test destroy method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_destroy_logged_off()
    {
        $rel = new ctrl_relations();

        try
        {
            $rel->destroy();
            $this->fail();
        }
        catch(exception $e)
        {
            $this->assertEquals(preg_match("/users/", $e->getMessage()), 1);
        }
    }

// +++++++++++++++++++++
    function test_destroy_logged_in_valid()
    {
        $_SESSION['active_user'] = array('name' => 'sue', 'id' => 1);
        $_POST = array(
            'id'     => 1,
            'Submit' => 'Submit');

        $rmt_mck = new mck_mdl_remotes('sue', 'Big fat human');

        $rel = new ctrl_relations();


        try
        {
            $rel->destroy($rmt_mck);
            $this->fail();
        }
        catch(exception $e)
        {
            $this->assertEquals(preg_match("/messages/", $e->getMessage()), 1);
        }

        $query = "SELECT * FROM `following` WHERE `ID` = 1";

        $result = mysql_query($query);

        $this->assertEquals(mysql_num_rows($result), 0);
    }

// +++++++++++++++++++++
    function test_destroy_logged_in_invalid()
    {
        $this->setExpectedException('no_such_user_exception');

        $_SESSION['active_user'] = array('name' => 'sue', 'id' => 2);
        $_POST = array(
            'id'     => 100,
            'Submit' => 'Submit');

        $rmt_mck = new mck_mdl_remotes('sue', 'Big fat human');

        $rel = new ctrl_relations();
        $rel->destroy($rmt_mck);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test add pingback handler
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_ping_add()
    {
        $rmt_mck = new mck_mdl_remotes('sue', 'Big fat human');

        $rmt = new mdl_remotes();
        $_POST['data'] = $rmt->send_ping(
            'http://localhost',
            'add',
            'fred',
            $rmt_mck->get_pub_key(),
            $rmt_mck->get_priv_key(),
            'http://localhost/messages/follow/sue',
            true);

        $rel = new ctrl_relations();

        ob_start();
        $rel->ping($rmt_mck);
        $result = ob_get_contents();
        ob_end_clean();

        $response = $rmt_mck->decode_ping_response($result);

        $this->assertEquals((string) $response->state, 'success');
        $this->assertTrue($rmt_mck->calls['decode_ping'] > 0);
        $this->assertTrue($rmt_mck->calls['make_ping_response'] > 0);

    // check that follower was added to followers table
        $query = "SELECT * FROM `followers` WHERE
            `Remote_name` = 'sue'";

        $result = mysql_query($query);

        $this->assertEquals(mysql_num_rows($result), 1);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test remove pingback handler
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_ping_remove()
    {
        $rmt_mck = new mck_mdl_remotes('sue', 'Big fat human');

        $rmt = new mdl_remotes();
        $_POST['data'] = $rmt->send_ping(
            'http://localhost',
            'remove',
            'fred',
            $rmt_mck->get_pub_key(),
            $rmt_mck->get_priv_key(),
            'http://localhost/messages/follow/sue',
            true);

        $rel = new ctrl_relations();

        ob_start();
        $rel->ping($rmt_mck);
        $result = ob_get_contents();
        ob_end_clean();

        $ping_response = $rmt->decode_ping_response($result);

        $this->assertEquals((string) $ping_response->state, 'fail');
    }
}
