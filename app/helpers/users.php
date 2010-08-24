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
 * Make register form values array
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
function make_reg_vals_array($name, $email, $pass, $pass_v)
{
    return array(
        'name'   => $name,
        'email'  => $email, 
        'pass'   => $pass,
        'pass_v' => $pass_v);
}

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Log in a user
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
function log_in_user($user, $user_id)
{
    $_SESSION['active_user'] = array(
        'name' => $user,
        'id'   => $user_id);

    redirect_to(make_url("messages"));
}

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Make a URL that points to a users profile
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
function make_profile_url($user_name)
{
    return make_ext_url("users", "profile", $user_name);
}

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Validate a username
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
function validate_username($user_name)
{
    if( strlen($user_name) < 3  ||
        strlen($user_name) > 30 ||
        !preg_match("/^[a-zA-Z0-9_]+$/", $user_name))
        throw new invalid_username_exception();
}

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Validate a password
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
function validate_password($password)
{
    if(mb_strlen($password, 'utf8') < 6)
        throw new invalid_password_exception();
}

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Validate a bio
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
function validate_bio($bio)
{
    if(mb_strlen($bio, 'utf8') > 160)
        throw new invalid_bio_exception();
}

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Check a string is less than 50 chars
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
function validate_50($password)
{
    if(mb_strlen($password, 'utf8') > 50)
        throw new over_50_exception();
}

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Validate an avatar
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
function validate_avatar($avatar_url)
{
    validate_url($avatar_url);

    if(!preg_match('/.+\.jpg/', $avatar_url))
        throw new invalid_avatar_exception();

    $image_size = @getimagesize($avatar_url);

    if($image_size === false)
        throw new invalid_avatar_exception();

    if($image_size[0] != 100 || $image_size[1] != 100)
        throw new invalid_avatar_exception();
}

// Exceptions
class invalid_username_exception extends exception { }
class invalid_password_exception extends exception { }
class over_50_exception extends exception { }
class invalid_avatar_exception extends exception { }
class invalid_bio_exception extends exception { }
