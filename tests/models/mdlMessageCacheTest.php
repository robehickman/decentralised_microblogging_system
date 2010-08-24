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
require_once 'app/helpers/users.php';
require_once 'app/helpers/messages.php';
require_once 'app/helpers/crypto.php';
require_once 'tests/models/mocks/remotes.php';
require_once 'app/models/message_cache.php';

class mdlMessageCacheTest extends PHPUnit_Extensions_Database_TestCase 
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
 * Test get_cached_user method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_get_cached_user()
    {
        $remote_url = 'http://localhost/messages/follow/fred';

        $csh = new mdl_message_cache();
        $result = $csh->get_cached_user($remote_url);

        $this->assertEquals(count($result), 1);
        $this->assertEquals($result[0]['Remote_URL'], $remote_url);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test new_cached_user method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_new_cached_user()
    {
        $remote_url = 'http://localhost/messages/follow/abcder';

        $csh = new mdl_message_cache();
        $csh->new_cached_user($remote_url);

        $result = $csh->get_cached_user($remote_url);
        $this->assertEquals($result[0]['Remote_URL'], $remote_url);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test flag_cache_update method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_flag_cache_update()
    {
        $remote_url = 'http://localhost/messages/follow/fred';

        $csh = new mdl_message_cache();
        $csh->flag_cache_update($remote_url);

        $result = $csh->get_cached_user($remote_url);
        $this->assertEquals($result[0]['Update_cache'], 1);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test clear_cache_update method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_clear_cache_update()
    {
        $remote_url = 'http://localhost/messages/follow/fred';

        $csh = new mdl_message_cache();
        $csh->flag_cache_update($remote_url);

        $csh->clear_cache_update($remote_url);

        $result = $csh->get_cached_user($remote_url);
        $this->assertEquals($result[0]['Update_cache'], 0);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test check_update method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_check_update()
    {
        $remote_url = 'http://localhost/messages/follow/fred';
        
        $csh = new mdl_message_cache();
        $csh->flag_cache_update($remote_url);

        $messages = array(
            array(
                'Time'    => '44444444444',
                'Message' => 'new message from fred'));

    // Pass in a posts array
        $rmt = new mck_mdl_remotes('fred', 'Somoeone called fred', $messages);

        $csh->check_update($remote_url, $rmt);

    // Check that the new cached message exists
        $messages = $csh->get_remote($remote_url);

        $this->assertEquals($messages[0]['Remote_message'], 'new message from fred');
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test new_item method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_new_item_valid()
    {
        $csh = new mdl_message_cache();
        $csh->new_item('http://localhost/messages/follow/sue', 'sue',
            'http://localhost/users/profile/sue',
            APP_ROOT . 'media/default_avatar.jpg',
            444444444, 'a message');

        $query = "SELECT * FROM `message-cache` WHERE `Remote_message` = 'a message'";
        $result = mysql_query($query);
       
        $this->assertEquals(mysql_num_rows($result), 1);
    }

// +++++++++++++++++++++
    function test_new_item_invalid_remote_url()
    {
        $this->setExpectedException('invalid_url_exception');

        $csh = new mdl_message_cache();
        $csh->new_item('invalid_url', 'sue',
            'http://localhost/users/profile/sue',
            APP_ROOT . 'media/default_avatar.jpg',
            444444444, 'a message');

    }

// +++++++++++++++++++++
    function test_new_item_invalid_remote_name()
    {
        $this->setExpectedException('invalid_username_exception');

        $csh = new mdl_message_cache();
        $csh->new_item('http://localhost/messages/follow/sue', 'e',
            'http://localhost/users/profile/sue',
            APP_ROOT . 'media/default_avatar.jpg',
            444444444, 'a message');
    }

// +++++++++++++++++++++
    function test_new_item_invalid_profile_url()
    {
        $this->setExpectedException('invalid_url_exception');

        $csh = new mdl_message_cache();
        $csh->new_item('http://localhost/messages/follow/sue', 'e',
            'invalid_url',
            APP_ROOT . 'media/default_avatar.jpg',
            444444444, 'a message');
    }

// +++++++++++++++++++++
    function test_new_item_invalid_avatar()
    {
        $this->setExpectedException('invalid_url_exception');

        $csh = new mdl_message_cache();
        $csh->new_item('http://localhost/messages/follow/sue', 'e',
            'http://localhost/users/profile/sue',
            'invalid_url',
            444444444, 'a message');
    }

// +++++++++++++++++++++
    function test_new_item_invalid_message()
    {
        $this->setExpectedException('invalid_message_exception');

        $csh = new mdl_message_cache();
        $csh->new_item('http://localhost/messages/follow/sue', 'sue',
            'http://localhost/users/profile/sue',
            APP_ROOT . 'media/default_avatar.jpg',
            444444444, '');
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test get_all method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_get_all()
    {
        $csh = new mdl_message_cache();
        $result = $csh->get_all();

        $this->assertEquals(count($result), 3);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test get_remote method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_get_remote()
    {
        $remote_url = 'http://localhost/messages/follow/fred';

        $csh = new mdl_message_cache();
        $result = $csh->get_remote($remote_url);

        $this->assertEquals(count($result), 2);
        $this->assertEquals($result[0]['Remote_URL'], $remote_url);
        $this->assertEquals($result[1]['Remote_URL'], $remote_url);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test purge_all method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_purge_all()
    {
        $csh = new mdl_message_cache();
        $csh->purge_all();

        $this->assertEquals($csh->get_all(), array());

    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test purge_remote method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_purge_remote()
    {
        $csh = new mdl_message_cache();
        $csh->purge_remote('http://localhost/messages/follow/fred');

        $result = $csh->get_remote('http://localhost/messages/follow/fred');
        $this->assertEquals($result, array());
    }
}
