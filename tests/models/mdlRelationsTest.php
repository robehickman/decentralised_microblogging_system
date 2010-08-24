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
require_once 'tests/models/mocks/remotes.php';
require_once 'app/models/users.php';
require_once 'app/helpers/users.php';
require_once 'app/helpers/crypto.php';
require_once 'app/models/relations.php';

class mdlRelationsTest extends PHPUnit_Extensions_Database_TestCase 
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
 * Test create_followed method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_create_following_valid()
    {
        $rel = new mdl_relations();
        $rel->create_following(2, 'http://localhost/messages/follow/sue',
            'sue', 'http://localhost/users/profile/sue',
            APP_ROOT . '/media/default_avatar.jpg',
            'http://localhost/relations/ping', 'http://localhost/messages/ping');


        $query = "SELECT * FROM `following` WHERE `Remote_name` = 'sue'";
        $result = mysql_query($query);
        $this->assertEquals(mysql_num_rows($result), 2);
    }

// +++++++++++++++++++++
    function test_create_following_invalid_id()
    {
        $this->setExpectedException('no_such_user_exception');

        $rel = new mdl_relations();
        $rel->create_following(100, 'http://localhost/messages/follow/sue',
            'sue', 'http://localhost/users/profile/sue',
            APP_ROOT . '/media/default_avatar.jpg',
            'http://localhost/relations/ping', 'http://localhost/messages/ping');
    }

// +++++++++++++++++++++
    function test_create_following_invalid_remote_url()
    {
        $this->setExpectedException('invalid_url_exception');

        $rel = new mdl_relations();
        $rel->create_following(1, 'invalid_url',
            'sue', 'http://localhost/users/profile/sue',
            APP_ROOT . '/media/default_avatar.jpg',
            'http://localhost/relations/ping', 'http://localhost/messages/ping');
    }

// +++++++++++++++++++++
    function test_create_following_invalid_remote_user()
    {
        $this->setExpectedException('invalid_username_exception');

        $rel = new mdl_relations();
        $rel->create_following(1, 'http://localhost/messages/follow/sue',
            'su', 'http://localhost/users/profile/sue',
            APP_ROOT . '/media/default_avatar.jpg',
            'http://localhost/relations/ping', 'http://localhost/messages/ping');
    }

// +++++++++++++++++++++
    function test_create_following_invalid_profile()
    {
        $this->setExpectedException('invalid_url_exception');

        $rel = new mdl_relations();
        $rel->create_following(1, 'http://localhost/messages/follow/sue',
            'sue', 'invalid_url',
            APP_ROOT . '/media/default_avatar.jpg',
            'http://localhost/relations/ping', 'http://localhost/messages/ping');
    }

// +++++++++++++++++++++
    function test_create_following_invalid_avatar()
    {
        $this->setExpectedException('invalid_url_exception');

        $rel = new mdl_relations();
        $rel->create_following(1, 'http://localhost/messages/follow/sue',
            'sue', 'http://localhost/users/profile/sue',
            'invalid_url',
            'http://localhost/relations/ping', 'http://localhost/messages/ping');
    }

// +++++++++++++++++++++
    function test_create_following_invalid_relation_pingback()
    {
        $this->setExpectedException('invalid_url_exception');

        $rel = new mdl_relations();
        $rel->create_following(1, 'http://localhost/messages/follow/sue',
            'sue', 'http://localhost/users/profile/sue',
            APP_ROOT . '/media/default_avatar.jpg',
            'invalid_url', 'http://localhost/messages/ping');
    }

// +++++++++++++++++++++
    function test_create_following_invalid_message_pingback()
    {
        $this->setExpectedException('invalid_url_exception');

        $rel = new mdl_relations();
        $rel->create_following(1, 'http://localhost/messages/follow/sue',
            'sue', 'http://localhost/users/profile/sue',
            APP_ROOT . '/media/default_avatar.jpg',
            'http://localhost/relations/ping', 'invalid_url');
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test get_following_by_id method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_get_following_by_id()
    {
        $rel = new mdl_relations();
        $result = $rel->get_following_by_id(1, 1);

        $this->assertEquals($result[0]['User_ID'], 1);
        $this->assertEquals($result[0]['Remote_name'], 'sue');
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test get_following method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_get_following()
    {
        $rel = new mdl_relations();
        $result = $rel->get_following(1);

        $this->assertEquals($result[0]['User_ID'], 1);
        $this->assertEquals($result[0]['Remote_name'], 'sue');
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test get_following_by_rmt_url method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_get_following_by_rmt_url()
    {
        $rel = new mdl_relations();
        $result = $rel->get_following_by_rmt_url(1,
            'http://localhost/messages/follow/sue');

        $this->assertEquals($result[0]['User_ID'], 1);
        $this->assertEquals($result[0]['Remote_name'], 'sue');
        $this->assertEquals($result[0]['Remote_URL'],
            'http://localhost/messages/follow/sue');
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test get_following_by_rmt_name method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_get_following_by_rmt_name()
    {
        $rel = new mdl_relations();
        $result = $rel->get_following_by_rmt_name(1, 'sue');

        $this->assertEquals($result[0]['User_ID'], 1);
        $this->assertEquals($result[0]['Remote_name'], 'sue');
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test remove_following_by_id method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_remove_following_by_id()
    {
        $rel = new mdl_relations();
        $result = $rel->remove_following_by_id(1, 1);

        $query = "SELECT * FROM `following` WHERE `ID` = '1'";
        $result = mysql_query($query);
        $this->assertEquals(mysql_num_rows($result), 0);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test create_follower method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_create_follower_valid()
    {
        $rmt_mck = new mck_mdl_remotes('', '');

        $rel = new mdl_relations();
        $rel->create_follower(2, 'http://localhost/messages/follow/fred',
            'fred', 'http://localhost/users/profile/fred',
            APP_ROOT . '/media/default_avatar.jpg', $rmt_mck->get_pub_key(),
            'http://localhost/relations/ping', 'http://localhost/messages/ping');

        $query = "SELECT * FROM `followers` WHERE `Remote_name` = 'fred'";
        $result = mysql_query($query);
        $this->assertEquals(mysql_num_rows($result), 2);
    }

// +++++++++++++++++++++
    function test_create_follower_invalid_id()
    {
        $rmt_mck = new mck_mdl_remotes('', '');

        $this->setExpectedException('no_such_user_exception');

        $rel = new mdl_relations();
        $rel->create_follower(100, 'http://localhost/messages/follow/fred',
            'fred', 'http://localhost/users/profile/fred',
            APP_ROOT . '/media/default_avatar.jpg', $rmt_mck->get_pub_key(),
            'http://localhost/relations/ping', 'http://localhost/messages/ping');
    }

// +++++++++++++++++++++
    function test_create_follower_invalid_remote_url()
    {
        $rmt_mck = new mck_mdl_remotes('', '');

        $this->setExpectedException('invalid_url_exception');

        $rel = new mdl_relations();
        $rel->create_follower(2, 'invalid_url',
            'fred', 'http://localhost/users/profile/fred',
            APP_ROOT . '/media/default_avatar.jpg', $rmt_mck->get_pub_key(),
            'http://localhost/relations/ping', 'http://localhost/messages/ping');
    }

// +++++++++++++++++++++
    function test_create_follower_invalid_remote_name()
    {
        $rmt_mck = new mck_mdl_remotes('', '');

        $this->setExpectedException('invalid_username_exception');

        $rel = new mdl_relations();
        $rel->create_follower(2, 'http://localhost/messages/follow/fred',
            'fr', 'http://localhost/users/profile/fred',
            APP_ROOT . '/media/default_avatar.jpg', $rmt_mck->get_pub_key(),
            'http://localhost/relations/ping', 'http://localhost/messages/ping');
    }

// +++++++++++++++++++++
    function test_create_follower_invalid_profile()
    {
        $rmt_mck = new mck_mdl_remotes('', '');

        $this->setExpectedException('invalid_url_exception');

        $rel = new mdl_relations();
        $rel->create_follower(2, 'http://localhost/messages/follow/fred',
            'fred', 'invalid_url',
            APP_ROOT . '/media/default_avatar.jpg', $rmt_mck->get_pub_key(),
            'http://localhost/relations/ping', 'http://localhost/messages/ping');
    }

// +++++++++++++++++++++
    function test_create_follower_invalid_avatar()
    {
        $rmt_mck = new mck_mdl_remotes('', '');

        $this->setExpectedException('invalid_url_exception');

        $rel = new mdl_relations();
        $rel->create_follower(2, 'http://localhost/messages/follow/fred',
            'fred', 'http://localhost/users/profile/fred',
            'invalid_url', $rmt_mck->get_pub_key(),
            'http://localhost/relations/ping', 'http://localhost/messages/ping');
    }

// +++++++++++++++++++++
    function test_create_follower_invalid_pubkey()
    {
        $rmt_mck = new mck_mdl_remotes('', '');

        $this->setExpectedException('invalid_public_key_exception');

        $rel = new mdl_relations();
        $rel->create_follower(2, 'http://localhost/messages/follow/fred',
            'fred', 'http://localhost/users/profile/fred',
            APP_ROOT . '/media/default_avatar.jpg', 'invalid',
            'http://localhost/relations/ping', 'http://localhost/messages/ping');
    }

// +++++++++++++++++++++
    function test_create_follower_invalid_relation_pingback()
    {
        $rmt_mck = new mck_mdl_remotes('', '');

        $this->setExpectedException('invalid_url_exception');

        $rel = new mdl_relations();
        $rel->create_follower(2, 'http://localhost/messages/follow/fred',
            'fred', 'http://localhost/users/profile/fred',
            APP_ROOT . '/media/default_avatar.jpg', $rmt_mck->get_pub_key(),
            'invalid_url', 'http://localhost/messages/ping');
    }

// +++++++++++++++++++++
    function test_create_follower_invalid_message_pingback()
    {
        $rmt_mck = new mck_mdl_remotes('', '');

        $this->setExpectedException('invalid_url_exception');

        $rel = new mdl_relations();
        $rel->create_follower(2, 'http://localhost/messages/follow/fred',
            'fred', 'http://localhost/users/profile/fred',
            APP_ROOT . '/media/default_avatar.jpg', $rmt_mck->get_pub_key(),
            'http://localhost/relations/ping', 'invalid_url');
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test get_followers method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_get_followers()
    {
        $rel = new mdl_relations();
        $result = $rel->get_followers(2);

        $this->assertEquals($result[0]['User_ID'], 2);
        $this->assertEquals($result[0]['Remote_name'], 'fred');
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test get_followers_by_rmt_name method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_get_followers_by_rmt_name()
    {
        $rel = new mdl_relations();
        $result = $rel->get_followers_by_rmt_name(2, 'fred');

        $this->assertEquals($result[0]['User_ID'], 2);
        $this->assertEquals($result[0]['Remote_name'], 'fred');
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test get_followers_by_rmt_url method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_get_followers_by_rmt_url()
    {
        $rel = new mdl_relations();
        $result = $rel->get_follower_by_rmt_url(2,
            'http://localhost/messages/follow/fred');

        $this->assertEquals($result[0]['User_ID'], 2);
        $this->assertEquals($result[0]['Remote_name'], 'fred');
        $this->assertEquals($result[0]['Remote_URL'],
            'http://localhost/messages/follow/fred');
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test remove_follower_by_id method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function remove_follower_by_id()
    {
        $rel = new mdl_relations();
        $result = $rel->remove_follower_by_id(2, 1);

        $query = "SELECT * FROM `followers` WHERE `ID` = '1'";
        $result = mysql_query($query);
        $this->assertEquals(mysql_num_rows($result), 0);
    }
}

