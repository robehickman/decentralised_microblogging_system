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

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Validate public key
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
function validate_pub_key($pub_key)
{
    $key = @openssl_get_publickey($pub_key);

    if($key === false)
        throw new invalid_public_key_exception();
}

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Validate private key
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
function validate_priv_key($priv_key)
{
    $key = @openssl_get_privatekey($priv_key);

    if($key === false)
        throw new invalid_private_key_exception();
}

// Exceptions
class invalid_public_key_exception extends exception { }
class invalid_private_key_exception extends exception { }
