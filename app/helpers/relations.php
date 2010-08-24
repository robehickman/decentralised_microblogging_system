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
 * Get the number of users that a user is following
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
function get_following($uid)
{
    $rel = instance_model('relations');
    $relations = $rel->get_following($uid);

    return count($relations);

}

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Get the number of users that are following a user
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
function get_followers($uid)
{
    $rel = instance_model('relations');
    $relations = $rel->get_followers($uid);

    return count($relations);
}

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Make a url pointing to a users message stream
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
function make_follow_url($uname)
{
    return make_ext_url('messages', 'follow', $uname);
}

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Get the most recent message by a remote user
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
function get_latest_by_remote($rmt, $remote_url)
{
    $csh = instance_model("message_cache");
    $csh->check_update($remote_url, $rmt);
    return $csh->get_remote($remote_url);
}

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Get the most recent message by a local user
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
function get_latest_by_local($user_id)
{
    $msg = instance_model("messages");
    return $msg->get_by_user_id($user_id);
}
