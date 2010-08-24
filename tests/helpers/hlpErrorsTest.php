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

require_once "src/view.php";

require_once "tests/models/mocks/remotes.php";
require_once "app/helpers/errors.php";

class hlpErrorsTest extends PHPUnit_Framework_TestCase 
{
    function setUp()
    {
        $_SESSION['flash'] = array();
        $GLOBALS['errors'] = array();
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
* Test new_flash function
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_new_flash()
    {
        new_flash('Something went wrong', 1);

        $this->assertEquals($_SESSION['flash'][0]['message'], 'Something went wrong');
        $this->assertEquals($_SESSION['flash'][0]['ttd'], 1);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
* Test display_errors function
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_display_errors_none()
    {
        $result = display_errors(true);

        $this->assertEquals($result, "");
    }

// +++++++++++++++++++++
    function test_display_errors_one()
    {
        new_flash('Something went wrong', 1);

        $result = display_errors(true);

        $this->assertEquals(preg_match('/Something went wrong/', $result), 1);
        $this->assertEquals($_SESSION['flash'], array());

    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
* Test get_errors function
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_get_errors()
    {
        new_flash('Something went wrong', 1);

        $this->assertEquals(get_errors(), $_SESSION['flash']);
    }
}
