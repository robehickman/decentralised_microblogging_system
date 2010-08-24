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

if(!isset($GLOBALS['errors']))
    $GLOBALS['errors'] = array();

if(!isset($_SESSION['flash']))
    $_SESSION['flash'] = array();

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Create a new flash item, flash items survive page reloads and
 * are displayed the number of times spesified by the times to
 * display (`ttd') paramiter. 
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
function new_flash($message, $ttd)
{
    array_push($_SESSION['flash'],
        array(
            'message' => $message,
            'ttd'     => $ttd));
}

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Display errors in the flash buffer
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
function display_errors($return = false)
{
    $new_flash = array();

    foreach($_SESSION['flash'] as $flash)
    {
        if($flash['ttd'] > 0)
        {
            $flash['ttd'] --;

            array_push($GLOBALS['errors'], $flash['message']);

            if($flash['ttd'] > 0)
                array_push($new_flash, $flash);
        }
    }

    $_SESSION['flash'] = $new_flash;

    $view = instance_view('errors');
    if($return == false)
        $view->parse();
    else
        return $view->parse_to_variable();
}

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Get the contents of the flash array
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
function get_errors()
{
    return $_SESSION['flash'];
}
