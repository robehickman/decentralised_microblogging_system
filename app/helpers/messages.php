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
* Extract at tags from a message
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
function extract_at($message)
{
    $split = preg_split("/ +/", $message);

    $send_to = array();

    foreach($split as $word)
        if($word[0] == '@')
            array_push($send_to, substr($word, 1));

    return $send_to;
}

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Find unique users in an array form the database
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
function find_unique_users($users)
{
    $remote_urls = array();

    foreach($users as $row)
        array_push($remote_urls, $row['Remote_URL']);

    $unique_items = array_unique($remote_urls);

    $users_unique = array();

    foreach($unique_items as $key => $value)
        array_push($users_unique, $users[$key]);

    return $users_unique;
}

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Validate a message
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
function validate_message($message)
{
    if($message == "" || mb_strlen($message, 'utf8') > 140)
        throw new invalid_message_exception();
}

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Get the number of messages by a user
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
function get_message_count($user_id)
{
    $msg = instance_model('messages');
    $messages = $msg->get_by_user_id($user_id);
    return count($messages);
}

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Format a UNIX timestamp into something readable
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
function format_time($timestamp)
{
    return date('d/m/Y H:m', $timestamp);
}

// Exceptions
class invalid_message_exception extends exception { }
