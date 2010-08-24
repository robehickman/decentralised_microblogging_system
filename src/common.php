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
* Escape HTML special charicters
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
function esc($text)
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Validate a URL
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
function validate_url($url)
{
    if(!preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url))
        throw new invalid_url_exception($url);
}

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Validate a email address
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
function validate_email($email)
{
    if(!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+"
        ."(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/", $email))
        throw new invalid_email_exception($email);
}

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Get the path to the applications root directory
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
function get_app_root()
{
    $root = dirname($_SERVER["PHP_SELF"]);

    if($root[strlen($root) - 1] == '/')
        $root = substr($root, 0, strlen($root) - 1);

    return $root;
}

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Build a internal url form the paramiters, vararg function
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
function make_url()
{
    $root = get_app_root();

    if(func_num_args() > 0)
    {

        for($i = 0; $i < func_num_args(); $i ++)
        {
            $root .= '/' . func_get_arg($i);
        }
    }

    return $root;
}

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Build a external url form the paramiters, vararg function
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
function make_ext_url()
{
    $prot = 'http';

    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
        $prot .= 's';

    $prot .= "://";

    if ($_SERVER['SERVER_PORT'] != '80')
        $prot .= $_SERVER["SERVER_NAME"]. ':' .$_SERVER["SERVER_PORT"];
    else
        $prot .= $_SERVER["SERVER_NAME"];

    $root = get_app_root();

    $root = $prot . $root;

    if(func_num_args() > 0)
    {

        for($i = 0; $i < func_num_args(); $i ++)
        {
            $root .= '/' . func_get_arg($i);
        }
    }

    return $root;
}

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Import a helper from the helpers directory
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
function load_helper($name)
{
    $hlp_path = "app/helpers/$name.php";

    if(file_exists($hlp_path))
        include_once $hlp_path;
}

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Redirect the browser to anouther page
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
function redirect_to($target)
{
    @header("location: " . $target);
// Throw to alow testing and prevent code execution after the
// redirect
    throw new redirecting_to($target);
}

// Exceptions
class invalid_url_exception extends exception { }
class invalid_email_exception extends exception { }
class redirecting_to extends exception { }
