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
require_once 'app/models/users.php';

class mdlUsersTest extends PHPUnit_Extensions_Database_TestCase 
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
 * Test new_user method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_new_user_valid()
    {
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SERVER_PORT'] = 80;
        $users = new mdl_users();
        $users->new_user('joe', 'joe@example.com', 'valid_password');

    // check that the user now eixsts in the DB
        $query = "SELECT * FROM `users` WHERE `User_name` = 'joe'";
        $this->assertFalse(mysql_query($query) == false);
    }

// +++++++++++++++++++++
    function test_new_user_invalid_username()
    {
        $this->setExpectedException('invalid_username_exception');

        $users = new mdl_users();
        $users->new_user('', 'joe@example.com', 'xxxxxxxxxxxxxxxxx');
     }

// +++++++++++++++++++++
    function test_new_user_invalid_email()
    {
        $this->setExpectedException('invalid_email_exception');

        $users = new mdl_users();
        $users->new_user('joe', 'joeexample.com', 'xxxxxxxxxxxxxxxxx');
    }

// +++++++++++++++++++++
    function test_new_user_invalid_password()
    {
        $this->setExpectedException('invalid_password_exception');

        $users = new mdl_users();
        $users->new_user('joe', 'joe@example.com', 'xxx');
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test verify_user method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_verify_user_valid()
    {
        $users = new mdl_users();
        $result = $users->verify_user('fred', 'aaaaaa');

        $this->assertFalse($result == false);
        $this->assertEquals($result[0]['ID'], '1');
        $this->assertEquals($result[0]['User_name'], 'fred');
    }

// +++++++++++++++++++++
    function test_verify_user_invalid_username()
    {
        $users = new mdl_users();

        $this->assertFalse(
            $users->verify_user('fredd', 'aaaaaa'));
    }

// +++++++++++++++++++++
    function test_verify_user_invalid_password()
    {
        $users = new mdl_users();

        $this->assertFalse(
            $users->verify_user('fred', 'fredfrfd'));
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test verify_user_id method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_varify_user_id_valid()
    {
        $users = new mdl_users();
        $users->verify_user_id(1);
    }

// +++++++++++++++++++++
    function test_varify_user_id_invalid()
    {
        $this->setExpectedException('no_such_user_exception');

        $users = new mdl_users();
        $users->verify_user_id(100);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test update_user method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_update_user_valid()
    {
        $users = new mdl_users();
        $users->update_user(
            1,'fred@example.com',
            'Fred Nobody Jones',
            'Bottom of the sea',
            'http://fred.example.com',
            'That doesn\'t work'
            );

    // Check that user has bean updated
        $query = "SELECT * FROM `users` WHERE `Location` = 'Bottom of the sea'";

        $result = mysql_query($query);
        $this->assertEquals(mysql_num_rows($result), 1);
    }

// +++++++++++++++++++++
    function test_update_user_invalid_id()
    {
        $this->setExpectedException('no_such_user_exception');

        $users = new mdl_users();
        $users->update_user(
            100,'fred@example.com',
            'Fred Nobody Jones',
            'Bottom of the sea',
            'http://fred.example.com',
            'That doesn\'t work'
            );
    }

// +++++++++++++++++++++
    function test_update_user_invalid_email()
    {
        $this->setExpectedException('invalid_email_exception');

        $users = new mdl_users();
        $users->update_user(
            1,'invalid',
            'Fred Nobody Jones',
            'Bottom of the sea',
            'http://fred.example.com',
            'That doesn\'t work'
            );
    }

// +++++++++++++++++++++
    function test_update_user_invalid_name()
    {
        $this->setExpectedException('over_50_exception');

        $name = '';
        for($i = 0; $i < 51; $i ++)
            $name .= 'a';

        $users = new mdl_users();
        $users->update_user(
            1,'fred@example.com',
            $name,
            'Bottom of the sea',
            'http://fred.example.com',
            'That doesn\'t work'
            );
    }

// +++++++++++++++++++++
    function test_update_user_invalid_location()
    {
        $this->setExpectedException('over_50_exception');

        $location = '';
        for($i = 0; $i < 51; $i ++)
            $location .= 'a';

        $users = new mdl_users();
        $users->update_user(
            1,'fred@example.com',
            'Fred Nobody Jones',
            $location,
            'http://fred.example.com',
            'That doesn\'t work'
            );
    }
    
// +++++++++++++++++++++
    function test_update_user_invalid_web()
    {
        $this->setExpectedException('invalid_url_exception');

        $users = new mdl_users();
        $users->update_user(
            1,'fred@example.com',
            'Fred Nobody Jones',
            'Bottom of the sea',
            'invalid',
            'That doesn\'t work'
            );
    }

// +++++++++++++++++++++
    function test_update_user_invalid_bio()
    {
        $this->setExpectedException('invalid_bio_exception');

        $bio = '';
        for($i = 0; $i < 161; $i ++)
            $bio .= 'a';

        $users = new mdl_users();
        $users->update_user(
            1,'fred@example.com',
            'Fred Nobody Jones',
            'Bottom of the sea',
            'http://fred.example.com',
            $bio
            );
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test update_password method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_update_password_vaid()
    {
        $users = new mdl_users();
        $users->update_password(1, 'newpass');

        $result = $users->verify_user('fred', 'newpass');
        $this->assertTrue($result != false);
    }

// +++++++++++++++++++++
    function test_update_password_invaid_id()
    {
        $this->setExpectedException('no_such_user_exception');

        $users = new mdl_users();
        $users->update_password(100, 'newpass');
    }

// +++++++++++++++++++++
    function test_update_password_invaid_password()
    {
        $this->setExpectedException('invalid_password_exception');

        $users = new mdl_users();
        $users->update_password(1, 's');
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test update_avatar method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_update_avatar_valid()
    {
        $avatar = APP_ROOT . 'tests/models/files/new_avatar.jpg';

        $users = new mdl_users();
        $users->update_avatar(1, $avatar);


    // Check the avatar has changed
        $query = "SELECT * FROM `users` WHERE `Avatar` = '$avatar'";

        $result = mysql_query($query);
        $this->assertEquals(mysql_num_rows($result), 1);
    }

// +++++++++++++++++++++
    function test_update_avatar_invalid_user_id()
    {
        $this->setExpectedException('no_such_user_exception');

        $avatar = APP_ROOT . 'tests/models/files/new_avatar.jpg';

        $users = new mdl_users();
        $users->update_avatar(100, $avatar);
    }

// +++++++++++++++++++++
    function test_update_avatar_invalid_avatar()
    {
        $this->setExpectedException('invalid_avatar_exception');

        $avatar = APP_ROOT . 'tests/models/fls/no_such_file.jpg';

        $users = new mdl_users();
        $users->update_avatar(1, $avatar);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test get_users method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_get_users()
    {
        $users = new mdl_users();
        $result = $users->get_users();

        $this->assertEquals($result[0]['User_name'], 'fred');
        $this->assertEquals($result[1]['User_name'], 'sue');
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test get_user_by_name method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_get_user_by_name()
    {
        $users = new mdl_users();
        $user = $users->get_user_by_name('fred');

        $this->assertEquals($user[0]['User_name'], 'fred');
        $this->assertEquals($user[0]['ID'], '1');
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test get_user_by_id method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_get_user_by_id()
    {
        $users = new mdl_users();
        $user = $users->get_user_by_id(1);

        $this->assertEquals($user[0]['User_name'], 'fred');
        $this->assertEquals($user[0]['ID'], '1');
    }
}
