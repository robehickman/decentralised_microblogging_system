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

class ctrl_relations extends controller_base
{
/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Setup
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function __CONSTRUCT ()
    {
        $this->load_outer_template('main');
        load_helper('relations');
        load_helper('users');
        load_helper('messages');
        load_helper('errors');
        load_helper('crypto');
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Display a list of users that are following a user
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function followers($rmt = false)
    {
        $flash = 'The specified user does not exist, here are the users on this node';

        if($rmt == false)
            $rmt = instance_model('remotes');

        if(!isset($this->params[2]))
        {
            new_flash($flash, 1);
            redirect_to(make_url('users'));
        }

        $usr = instance_model('users');
        $user = $usr->get_user_by_name($this->params[2]);

        if($user == array())
        {
            new_flash($flash, 1);
            redirect_to(make_url('users'));
        }

        $rel = instance_model('relations');
        $followers = $rel->get_followers($user[0]['ID']);

    // Flag the cache to update
        $csh = instance_model('message_cache');

        foreach($followers as $follower)
            $csh->flag_cache_update($follower['Remote_URL']);

    // display main
        $view = instance_view('user_list');
        $view = $view->parse_to_variable(array(
            'users' => $followers,
            'title' => 'Followers',
            'rmt'   => $rmt));

    // display sidebar
        $sb_view = instance_view('profile_sidebar');
        $sb_view = $sb_view->parse_to_variable(array(
            'uid'      => $user[0]['ID'],
            'uname'    => $user[0]['User_name'],
            'fname'    => $user[0]['Full_name'],
            'location' => $user[0]['Location'],
            'web'      => $user[0]['Web'],
            'bio'      => $user[0]['Bio']));

        $this->set_template_paramiters(
            array('main_content' => $view,
                  'sidebar'      => $sb_view));
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Display a list of users that a user is following
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function following($rmt = false)
    {
        $flash = 'The specified user does not exist, here are the users on this node';

        if($rmt == false)
            $rmt = instance_model('remotes');

        if(!isset($this->params[2]))
        {
            new_flash($flash, 1);
            redirect_to(make_url('users'));
        }

        $usr = instance_model('users');
        $user = $usr->get_user_by_name($this->params[2]);

        if($user == array())
        {
            new_flash($flash, 1);
            redirect_to(make_url('users'));
        }

        $rel = instance_model('relations');
        $following = $rel->get_following($user[0]['ID']);

    // display main
        $view = instance_view('user_list');
        $view = $view->parse_to_variable(array(
            'users' => $following,
            'title' => 'Following',
            'form_message' => 'Unfollow',
            'form_target'  => make_url('relations', 'destroy'),
            'rmt'          => $rmt));

    // display sidebar
        $sb_view = instance_view('profile_sidebar');
        $sb_view = $sb_view->parse_to_variable(array(
            'uid'      => $user[0]['ID'],
            'uname'    => $user[0]['User_name'],
            'fname'    => $user[0]['Full_name'],
            'location' => $user[0]['Location'],
            'web'      => $user[0]['Web'],
            'bio'      => $user[0]['Bio']));

        $this->set_template_paramiters(
            array('main_content' => $view,
                  'sidebar'      => $sb_view));
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Add a new remote user to a users following list
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function create($rmt = false)
    {
        if(!isset($_SESSION['active_user']))
            redirect_to('users', 'login');

        if(!isset($_POST['Submit']))
            redirect_to(make_url('messages'));

        $this->outer_template = null;

    // validate URL
        $remote_url = $_POST['follow_user'];

        try{
        validate_url($remote_url);
        } catch(exception $e) {
            new_flash("url is invalid", 1);
            redirect_to(make_url('messages'));
        }

        $local_id = $_SESSION['active_user']['id'];

    // Get local user
        $usr = instance_model('users');
        $user = $usr->get_user_by_id($local_id); 

        if($user == array())
            throw new exception('Databse eror');

    // check locally to see if user is already being followed
        $rel = instance_model('relations');
        $remote_user = $rel->get_following_by_rmt_url($local_id, $remote_url);

        if($remote_user != array())
        {
            new_flash("Already following this user", 1);
            redirect_to(make_url('messages'));
        }
        
    // Fetch pingback URL from remote stream
        if($rmt == false)
            $rmt = instance_model('remotes');

        try {
        $msg_stream = $rmt->get_message_stream($remote_url);
        } catch(exception $e) {
            new_flash("Problem with message stream", 1);
            redirect_to(make_url('messages'));
        }

        $ping_url    = $msg_stream->head->relation_pingback;
        $remote_user = $msg_stream->head->by_user;

    // Send add ping
        $response = $rmt->send_ping($ping_url, 'add', $remote_user, 
            $user[0]['Pub_key'], $user[0]['Priv_key'],
            make_follow_url($user[0]['User_name']));

        $response = $rmt->decode_ping_response($response);

    // If ping failed, display error message
        if($response->state == 'fail' && $response->error_msg != "You are already following this user.")
        {
            new_flash("Remote error, could not follow", 1);
            redirect_to(make_url('messages'));
        }

    // Else add to current users following users
        $rel = instance_model("relations");
        $rel->create_following($_SESSION['active_user']['id'], $remote_url, $remote_user,
            $msg_stream->head->user_profile, $msg_stream->head->user_avatar,
            $msg_stream->head->relation_pingback, $msg_stream->head->message_pingback);

    // Redirect to index
        redirect_to(make_url("messages"));
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Remove a remote user to a users following list
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function destroy($rmt = false)
    {
        $this->outer_template = null;

        if(!isset($_SESSION['active_user']))
            redirect_to(make_url('users', 'login'));

        if(!isset($_POST['Submit']))
            redirect_to(make_url('messages'));

        $local_id = $_SESSION['active_user']['id'];

        $rel = instance_model('relations');
        $usr = instance_model('users');
        if($rmt == false)
            $rmt = instance_model('remotes');

    // Get local user
        $user = $usr->get_user_by_id($local_id); 

        if($user == array())
            throw new no_such_user_exception();

    // get user being followed
        $flw_user = $rel->get_following_by_id($local_id, $_POST['id']);

        if($flw_user == array())
            throw new no_such_user_exception();

    // Send remove ping
        $response = $rmt->send_ping($flw_user[0]['Relation_pingback'],
            'remove', $flw_user[0]['Remote_name'], 
            $user[0]['Pub_key'], $user[0]['Priv_key'],
            make_follow_url($user[0]['User_name']));

        $response = $rmt->decode_ping_response($response);

    // If ping failed, display error message
        if($response->state == 'fail')
        {
            new_flash('User not found on remote', 1);
            redirect_to(make_url('messages'));
        }

    // remove from local db if successful
        $rel->remove_following_by_id($local_id, $_POST['id']);

        redirect_to(make_url('messages'));
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Pingback to handle follower addition and removal
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function ping($rmt = false)
    {
        try
        {
            $this->outer_template = null;

            $usr = instance_model('users');
            $rel = instance_model("relations");
            if($rmt == false)
                $rmt = instance_model('remotes');

            $ping_data = $rmt->decode_ping($_POST['data']);

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
            switch($ping_data->type)
            {
            case 'add':
            // Validate remote stream
                try {
                    validate_url($ping_data->data);
                } catch(exception $e) {
                    echo $rmt->make_ping_response('fail',
                        "Message stream URL is invalid");
                    return;
                }

                $messages = $rmt->get_message_stream($ping_data->data);

            // Check if the user exists
                $user = $usr->get_user_by_name($ping_data->user);

                if($user == array())
                {
                    echo $rmt->make_ping_response('fail',
                        "The requested user does not exist on this node");
                    return;
                }

            // check if the user from the remote is already registered as a follower
                $follower = $rel->get_follower_by_rmt_url($user[0]['ID'], $ping_data->data);

                if($follower != array())
                {
                    echo $rmt->make_ping_response('fail',
                        "You are already following this user.");
                    return;
                }

            // If not, add it
                $rel->create_follower($user[0]['ID'], $ping_data->data, $messages->head->by_user,
                    $messages->head->user_profile, $messages->head->user_avatar, $ping_data->user_pub_key,
                    $messages->head->relation_pingback, $messages->head->message_pingback);

                echo $rmt->make_ping_response('success');
                break;

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
            case 'remove':
            // Get the user
                $user = $usr->get_user_by_name($ping_data->user);

                if($user == array())
                {
                    echo $rmt->make_ping_response('fail',
                        "The requested user does not exist on this node");
                    return;
                }

            // Get follower from followers table
                $follower = $rel->get_follower_by_rmt_url($user[0]['ID'], $ping_data->data);

                if($follower == array())
                {
                    echo $rmt->make_ping_response('fail',
                        "Follower not found");
                    return;
                }
                
                $rmt->varify_ping_signature($ping_data, $follower[0]['Remote_pub_key']);

            // If valid, remove the remote user as a follower
                $rel->remove_follower_by_id($user[0]['ID'], $follower[0]['ID']);

                echo $rmt->make_ping_response('success');

                break;

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
            default:
                echo $rmt->make_ping_response('fail', 'Invalid ping type');
            }
        }
        catch(exception $e)
        {
            echo $rmt->make_ping_response('fail', "Server error");
        }
    }
}
