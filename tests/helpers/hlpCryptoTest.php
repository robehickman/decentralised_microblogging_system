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

require_once "tests/models/mocks/remotes.php";
require_once "app/helpers/crypto.php";

class hlpCryptoTest extends PHPUnit_Framework_TestCase 
{
/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
* Test validate_pub_key function
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_validate_pub_key_valid()
    {
        $mck = new mck_mdl_remotes('','');

        validate_pub_key($mck->get_pub_key());
    }

// +++++++++++++++++++++
    function test_validate_pub_key_invalid()
    {
        $this->setExpectedException('invalid_public_key_exception');

        validate_pub_key('invalid_public_key');
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
* Test validate_priv_key function
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_validate_priv_key_valid()
    {
        $mck = new mck_mdl_remotes('','');

        validate_priv_key($mck->get_priv_key());
    }

// +++++++++++++++++++++
    function test_validate_priv_key_invalid()
    {
        $this->setExpectedException('invalid_private_key_exception');

        validate_priv_key('invalid_public_key');
    }
}
