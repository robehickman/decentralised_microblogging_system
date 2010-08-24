<?php
/*++++++++++++++++ Database configuration +++++++++++++++++*/
// Host name
    define('HOST',         'localhost');       

// Database username
    define('USERNAME',     '');

// Database password
    define('PASSWORD',     ''); 

// Database name
    define('DB_NAME',      '');        

/*+++++++++++++++++++ Application root +++++++++++++++++++++*/
    define('APP_ROOT',      'http://localhost/');

/*
 * This should be set to the path to the root of the
 * application in your localweb server, the test suite
 * will fail if this is not correct
 */

/*+++++++++++++++++++++ Do not change ++++++++++++++++++++++*/
    define("ALLOW_REGISTRATION", true);

    define("PROTOCOL_VERSION", 0.1);

    define("APP_MODE", 'test');

// Connect for database
    mysql_connect(HOST, USERNAME, PASSWORD)or
        die("cannot connect");

    mysql_select_db(DB_NAME)or die("cannot select DB");
