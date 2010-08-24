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

class mdl_relations extends database
{
/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
* Create a new followed user
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function create_following($id, $remote_url, $remote_name,
        $remote_profile, $remote_avatar, $relation_pingback, $message_pingback)
    {

        $users = instance_model('users');
        $user = $users->verify_user_id($id);

        validate_url($remote_url);
        validate_username($remote_name);
        validate_url($remote_profile);
        validate_avatar($remote_avatar);
        validate_url($relation_pingback);
        validate_url($message_pingback);

        $query = "INSERT INTO `following` 
            (`User_ID`, `Remote_URL`, `Remote_name`, `Remote_profile`,
            `Remote_avatar`, `Relation_pingback`, `Message_pingback`)
            VALUES ('@v', '@v', '@v', '@v', '@v', '@v', '@v')";

        $this->query($query, $id, $remote_url, $remote_name,
            $remote_profile, $remote_avatar, $relation_pingback,
            $message_pingback);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Get a following by table id
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function get_following_by_id($user_id, $id)
    {
        $query = "SELECT * FROM `following` WHERE
            `User_ID` = '@v' AND `ID` = '@v'";

        return $this->sql_to_array($this->query($query, $user_id, $id));
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Get the remotes that a user is following
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function get_following($id)
    {
        $query = "SELECT * FROM `following` WHERE `User_ID` = '@v'";

        return $this->sql_to_array($this->query($query, $id));
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Get a remotes that a user is following by the remote url
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function get_following_by_rmt_url($id, $remote_url)
    {
        $query = "SELECT * FROM `following` WHERE `User_ID` = '@v'
            AND `Remote_URL` = '@v'";

        return $this->sql_to_array($this->query($query, $id, $remote_url));
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Get the remotes that a user is following by the remote name,
 * may return more than one result as different nodes may have
 * users with the same name
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function get_following_by_rmt_name($id, $remote_name)
    {
        $query = "SELECT * FROM `following` WHERE `User_ID` = '@v'
            AND `Remote_name` = '@v'";

        return $this->sql_to_array($this->query($query, $id, $remote_name));
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Remove a following by table id
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function remove_following_by_id($user_id, $id)
    {
        $query = "DELETE FROM `following` WHERE
            `User_ID` = '@v' AND `ID` = '@v'";

        $this->query($query, $user_id, $id);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Create a new follower
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function create_follower($id, $remote_url, $remote_name, 
        $remote_profile, $remote_avatar, $pub_key, $relation_pingback,
        $message_pingback)
    {
        $users = instance_model('users');
        $user = $users->verify_user_id($id);

        validate_pub_key($pub_key);
        $pub_key = base64_encode($pub_key);

        validate_url($remote_url);
        validate_username($remote_name);
        validate_url($remote_profile);
        validate_url($remote_avatar);
        validate_url($relation_pingback);
        validate_url($message_pingback);

        $query = "INSERT INTO `followers`
            (`User_ID`, `Remote_URL`, `Remote_name`, `Remote_profile`,
            `Remote_avatar`, `Remote_pub_key`, `Relation_pingback`,
            `Message_pingback`) VALUES
            ('@v', '@v', '@v', '@v', '@v', '@v', '@v', '@v')";

        $this->query($query, $id, $remote_url, $remote_name,
            $remote_profile, $remote_avatar, $pub_key,
            $relation_pingback, $message_pingback);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Get the followers of a local user
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function get_followers($id)
    {
        $query = "SELECT * FROM `followers` WHERE `User_ID` = '@v'";

        return $this->decode($this->sql_to_array($this->query($query, $id)));
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Get a follower of a local user by the remotes name
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function get_followers_by_rmt_name($id, $remote_name)
    {
        $query = "SELECT * FROM `followers` WHERE `User_ID` = '@v'
            AND `Remote_name` = '@v'";

        return $this->decode($this->sql_to_array($this->query($query, $id, $remote_name)));
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Get a followers of a local user by the remotes message stream URL
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function get_follower_by_rmt_url($id, $remote_url)
    {
        $query = "SELECT * FROM `followers` WHERE `User_ID` = '@v'
            AND `Remote_URL` = '@v'";

        return $this->decode($this->sql_to_array($this->query($query, $id, $remote_url)));
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Remove a follower by table id
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function remove_follower_by_id($user_id, $id)
    {
        $query = "DELETE FROM `followers` WHERE
            `User_ID` = '@v' AND `ID` = '@v'";

        $this->query($query, $user_id, $id);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Decode public key
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    private function decode($result)
    {
        for($i = 0; $i < count($result); $i ++)
            $result[$i]['Remote_pub_key'] = base64_decode($result[$i]['Remote_pub_key']);

        return $result;
    }
}
