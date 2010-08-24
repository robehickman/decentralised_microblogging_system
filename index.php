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

// Includes
    include "src/common.php";
    include "src/dispatcher.php";
    include "src/database.php";
    include "src/controller.php";
    include "src/view.php";

// Load config file
    include "config.php";

// Check required dependencies exist
    if(ini_get('allow_url_fopen') != 1)
        die("Please enable url_fopen in the php ini file");

    if(!function_exists('openssl_pkey_new'))
        die("Please enable openssl support in the php ini file");

    if(!function_exists('gd_info'))
        die("Please enable GD support in the php ini file");

    if(!is_writable('media/'))
        die("'media/' directory must be writeable");
// Setup
    session_start();

    header('Content-Type: text/html; charset=UTF-8');

    mysql_connect(HOST, USERNAME, PASSWORD)or
        die("cannot connect");

    mysql_select_db(DB_NAME)or die("cannot select DB");

// DO NOT CHANGE
    define('DEFAULT_CONTROLLER', 'messages');

    mb_internal_encoding("UTF-8");

    define("PROTOCOL_VERSION", "0.1");

// Run the dispatcher
    try
    {
        dispatcher();
    }
    catch(exception $e)
    {
        if(APP_MODE == 'test')
            throw $e;
        else
            die('Something went wrong');
    }
