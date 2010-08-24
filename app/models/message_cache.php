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

class mdl_message_cache extends database
{
/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Get cached user
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function get_cached_user($remote_url)
    {
        $query = "SELECT * FROM `message-cache-users`
            WHERE `Remote_URL` = '@v'";

        return $this->sql_to_array($this->query($query, $remote_url));
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Create a new cached user
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function new_cached_user($remote_url)
    {
        validate_url($remote_url);

        $query = "INSERT INTO `message-cache-users`
            (`Remote_URL`, `Update_cache`) VALUES
            ('@v', 1)";

        $this->query($query, $remote_url);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Flag cache update
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function flag_cache_update($remote_url)
    {
        $query = "UPDATE `message-cache-users` SET
            `Update_cache` = '1' WHERE Remote_URL = '@v'";

        $this->query($query, $remote_url);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Clear cache update
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function clear_cache_update($remote_url)
    {
        $query = "UPDATE `message-cache-users` SET
            `Update_cache` = '0' WHERE Remote_URL = '@v'";

        $this->query($query, $remote_url);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Check for cache updates
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function check_update($remote_url, $rmt = false)
    {
        $cached_user = $this->get_cached_user($remote_url);

        if($rmt == false)
            $rmt = instance_model('remotes');

        if($cached_user == array())
        {
            $this->new_cached_user($remote_url);
            $cached_user[0]['Update_cache'] = 1;
        }

        if($cached_user[0]['Update_cache'] == 1)
        {
        //download remotes message stream
            $messages = $rmt->get_message_stream($remote_url);

            $user_url     = $remote_url;
            $user_profile = $messages->head->user_profile;
            $user_avatar  = $messages->head->user_avatar;
            $user_alias   = $messages->head->by_user;

        //delete any existing cache from that user
            $this->purge_remote($remote_url);

            foreach($messages->message as $message)
            {
                $this->new_item($user_url, $user_alias, $user_profile,
                    $user_avatar, $message->time, $message->message);
            }

        // clear the cache update flag
            $this->clear_cache_update($remote_url);
        }
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Create a new cache item
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function new_item($remote_url, $remote_name, $remote_profile,
        $remote_avatar, $time, $message)
    {
        validate_url($remote_url);
        validate_url($remote_profile);
        validate_avatar($remote_avatar);
        validate_username($remote_name);
        validate_message($message);

        $query = "INSERT INTO `message-cache` (`Remote_URL`,
            `Remote_name`, `Remote_profile`, `Remote_avatar`,
            `Remote_time`, `Remote_message`) VALUES
            ('@v','@v','@v','@v','@v', '@v')";

        $this->query($query, $remote_url, $remote_name,
            $remote_profile, $remote_avatar, $time, $message);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Get the entire cache
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function get_all()
    {
        $query = "SELECT * FROM `message-cache`";

        return $this->sql_to_array($this->query($query));
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Get the cached messages for a user
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function get_remote($remote_url)
    {
        $query = "SELECT * FROM `message-cache` WHERE `Remote_URL` = '@v'
            ORDER BY `Remote_time` DESC";
        return $this->sql_to_array($this->query($query, $remote_url));
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Compleatly purge the cache
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function purge_all()
    {
        $query = "DELETE FROM `message-cache`";
        $this->query($query);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Purge the cache from a remote
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function purge_remote($remote_url)
    {
        $query = "DELETE FROM `message-cache` WHERE `Remote_URL` = '@v'";
        $this->query($query, $remote_url);
    } 
}
