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

class mdl_direct_message extends database
{
/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Create a new direct message
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function new_dm($user_id, $type, $remote_name, $remote_profile,
        $remote_avatar, $remote_message, $remote_time)
    {
        $users = instance_model('users');
        $users->verify_user_id($user_id);

        if(!($type == "public" || $type == 'private'))
            throw new invalid_dm_type_exception();

        validate_username($remote_name);
        validate_url($remote_profile);
        validate_avatar($remote_avatar);
        validate_message($remote_message);

        $query = "INSERT INTO `direct-message`
            (`User_ID`, `Type`, `Remote_name`, `Remote_profile`,
                `Remote_avatar`, `Remote_message`, `Remote_time`)
            VALUES ('@v','@v','@v','@v','@v', '@v', '@v')";

        $this->query($query, $user_id, $type, $remote_name,
            $remote_profile, $remote_avatar, $remote_message, $remote_time);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Get a message by table id
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function get_by_id($user_id, $id)
    {
        $query = "SELECT * FROM `direct-message`
            WHERE `User_ID` = '@v' AND `ID` = '@v'";

        return $this->sql_to_array($this->query($query, $user_id, $id));
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Get messages beloging to a local user
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function get_by_user_id($user_id)
    {
        $query = "SELECT * FROM `direct-message`
            WHERE `User_ID` = '@v' ORDER BY `Remote_time` DESC";

        return $this->sql_to_array($this->query($query, $user_id));
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Delete a message by table id
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function delete_by_id($user_id, $id)
    {
        $query = "DELETE FROM `direct-message`
            WHERE `User_ID` = '@v' AND `ID` = '@v'";

        $this->query($query, $user_id, $id);
    }
}

// Exceptions
class invalid_dm_type_exception extends exception { }
