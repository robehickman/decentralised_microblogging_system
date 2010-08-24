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

class mdl_messages extends database
{
/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Create a new message
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function create($user_id, $message, $time)
    {
        $usr = instance_model('users');
        $usr->verify_user_id($user_id);

        validate_message($message);

        $query = "INSERT INTO `messages` (`User_ID`, `Time`, `Message`)
            VALUES ('@v', '@v', '@v')";

        $this->query($query, $user_id, $time, $message);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Get messages by a spasific user
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function get_by_user_id($user_id)
    {
        $query = "SELECT * FROM `messages` WHERE `User_ID` = '@v' ORDER BY `Time` DESC";

        return $this->sql_to_array($this->query($query, $user_id));
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Get a message by its table ID
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function get_by_id($user_id, $id)
    {
        $query = "SELECT * FROM `messages` WHERE `User_ID` = '@v'
            AND `ID` = '@v' LIMIT 1";

        return $this->sql_to_array($this->query($query, $user_id, $id));
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Delete a message by its table ID
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function delete_by_id($user_id, $id)
    {
        $query = "DELETE FROM `messages` WHERE `User_ID` = '@v'
            AND `ID` = '@v' LIMIT 1";

        $this->query($query, $user_id, $id);
    }
}
