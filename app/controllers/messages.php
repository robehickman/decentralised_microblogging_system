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

class ctrl_messages extends controller_base
{
/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Setup
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function __CONSTRUCT ()
    {
        $this->load_outer_template('main');
        load_helper('relations');
        load_helper('users');
        load_helper('errors');
        load_helper('crypto');
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Display a users message timeline
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function index($rmt = null)
    {
    // if not logged in, display list of users registered on this node
        if(!isset($_SESSION['active_user']))
            redirect_to(make_url("users"));

        $_SESSION['direct_to'] = make_url('messages');

    // if logged in, display timeline
        $msg = instance_model("messages");
        $csh = instance_model("message_cache");
        $rel = instance_model("relations");

        if($rmt == null)
            $rmt = instance_model("remotes");

        $local_user_id = $_SESSION['active_user']['id'];

    // get array of folowed users
        $folowed_users = $rel->get_following($local_user_id);

    // Check if the remote cache needs updating, update it if it does
        foreach($folowed_users as $remote_user)
            $csh->check_update($remote_user['Remote_URL']);

   // Combine messages from current user with cached messages from the users
    // it is following
        $message_list = array();

        $local_messages = $msg->get_by_user_id($local_user_id);

        $remote_url  = make_follow_url($_SESSION['active_user']['name']);
        $profile_url = make_profile_url($_SESSION['active_user']['name']);
        $remote_name = $_SESSION['active_user']['name'];

        $usr = instance_model('users');
        $user = $usr->get_user_by_id($_SESSION['active_user']['id']);

        foreach($local_messages as $message)
        {
            array_push($message_list, array(
                'Remote_URL'     => $remote_url,
                'Remote_profile' => $profile_url,
                'Remote_avatar'  => $user[0]['Avatar'],
                'Remote_name'    => $_SESSION['active_user']['name'],
                'Remote_time'    => $message['Time'],
                'Remote_message' => $message['Message']));
        }

        foreach($folowed_users as $user)
        {
            $cache = $csh->get_remote($user['Remote_URL']);

            foreach($cache as $item)
                array_push($message_list, $item);
        }

    // sort message list by time
        $sort_array = array();

        foreach($message_list as $item)
            array_push($sort_array, $item['Remote_time']);

        arsort($sort_array);

        $sorted_messages = array();

        foreach($sort_array as $key => $value)
            array_push($sorted_messages, $message_list[$key]);

    // display messages from the cache
        $view = instance_view("messages");
        $view = $view->parse_to_variable(array(
            "messages" => $sorted_messages));

    // Display sidebar
        $sb_view = instance_view("feed_sidebar");
        $sb_view = $sb_view->parse_to_variable(array(
            'uid'   => $_SESSION['active_user']['id'],
            'uname' => $_SESSION['active_user']['name']));

        $this->set_template_paramiters(
            array('main_content' => $view,
                  'sidebar'      => $sb_view));
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Generate XML stream of a users messages
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function follow()
    {
        try
        {
            $this->outer_template = null;
            $rmt = instance_model("remotes");

            if(!isset($this->params[2]))
                die("no user specified");

        // get the user from the database
            $usr = instance_model("users");
            $user = $usr->get_user_by_name($this->params[2]); 

            if($user == array())
                throw new invalid_username_exception();

            $msg = instance_model("messages");

            $messages = $msg -> get_by_user_id($user[0]['ID']);

        // Output
            @Header('Content-type: text/xml');

            echo $rmt->make_messages_xml(
                $user[0]['User_name'],
                $user[0]['Pub_key'],
                $user[0]['Priv_key'],
                $user[0]['Bio'],
                $user[0]['Avatar'],
                make_profile_url($user[0]['User_name']),
                $messages,
                make_ext_url('messages', 'ping'),
                make_ext_url('relations', 'ping')
            );
        }
        catch(invalid_username_exception $e)
        {
            print "Invalid username";
        }
        catch(exception $e)
        {
            print "Server error";
        }
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Create a new message
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function create($rmt = false)
    {
    // if not logged in, display list of users registered on this node
        if(!isset($_SESSION['active_user']))
            redirect_to(make_url('users', 'login'));

        $this->outer_template = null;

        $message = $_POST['message'];

        try {
        validate_message($message);
        } catch(exception $e) {
            new_flash("Invalid message", 1);
            redirect_to($_SESSION['direct_to']);
        }

    // Instance models
        $usr = instance_model('users');
        $msg = instance_model("messages");
        $rel = instance_model("relations");
        if($rmt == false)
            $rmt = instance_model("remotes");

        $time = time();

        $user = $usr->get_user_by_id($_SESSION['active_user']['id']);

        if($user == array())
            throw new exception('Databse eror');

    // Check for at tags
        $at_to_user = extract_at($message);

        foreach($at_to_user as $row)
        {
            try {
            validate_username($row);
            } catch (exception $e) {
                continue;
            }

        // Get followers and followings with the user name
            $following = $rel->get_following_by_rmt_name
                ($_SESSION['active_user']['id'], $row);

            $followers = $rel->get_followers_by_rmt_name
                ($_SESSION['active_user']['id'], $row);

            $retrieved = array_merge($following, $followers);

            if(count($retrieved) > 0)
            {
                if(count($retrieved) > 1)
                    $fetched_users = find_unique_users($retrieved);
                else
                    $fetched_users = $retrieved;

            // send message in message pingback
                foreach($fetched_users as $rmt_user)
                {
                    $xml = new SimpleXMLElement("<data></data>");
                    $xml->addChild('remote_name',    $user[0]['User_name']);
                    $xml->addChild('remote_profile', make_profile_url($_SESSION['active_user']['name']));
                    $xml->addChild('remote_avatar',  $user[0]['Avatar']);
                    $xml->addChild('remote_message', $message);
                    $xml->addChild('remote_time',    $time);

                    $response = $rmt->send_ping($rmt_user['Message_pingback'], "public",
                        $rmt_user['Remote_name'], $user[0]['Pub_key'], $user[0]['Priv_key'],
                        $xml->asXML()); 

                    try {
                        $response = $rmt->decode_ping_response($response);
                    } catch(exception $e) {
                        die;
                    }

                    if(defined('APP_MODE') && APP_MODE == 'test' && $response->state == 'fail')
                        throw new exception($response->error_msg);
                }
            }
            else
                new_flash("User $row not found in follwing or followers", 1);
        }

    // Add to local database
        $msg->create($_SESSION['active_user']['id'], $message, $time);

    // Send pings to update remote caches
        $remote_users = $rel->get_followers($_SESSION['active_user']['id']);

        foreach($remote_users as $rmt_user)
            $rmt->send_ping($rmt_user['Message_pingback'], "update", 'null',
                $user[0]['Pub_key'], $user[0]['Priv_key'],
                make_follow_url($_SESSION['active_user']['name']));

    // redirect
        redirect_to($_SESSION['direct_to']);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Delete a message
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function destroy($rmt = false)
    {
        $this->outer_template = null;

        if(!isset($_SESSION['active_user']))
            redirect_to(make_url('users', 'login'));

        if(!isset($_POST['Submit']))
            redirect_to(make_profile_url($_SESSION['active_user']['name']));

        $local_id = $_SESSION['active_user']['id'];

    // Instance models
        $rel = instance_model('relations');
        $usr = instance_model('users');
        $msg = instance_model('messages');
        if($rmt == false)
            $rmt = instance_model('remotes');

    // Get local user
        $user = $usr->get_user_by_id($local_id); 

        if($user == array())
            throw new no_such_user_exception();

    // Get the message
        $message = $msg->get_by_id($local_id, $_POST['id']);

        if($message == array())
        {
            new_flash('Message does not exist', 1);
            redirect_to(make_profile_url($_SESSION['active_user']['name']));
        }

    // Delete
        $msg->delete_by_id($local_id, $_POST['id']);

    // Send pings to update remote caches
        $remote_users = $rel->get_followers($local_id);

        foreach($remote_users as $rmt_user)
            $rmt->send_ping($rmt_user['Message_pingback'], "update", 'null',
                $user[0]['Pub_key'], $user[0]['Priv_key'],
                make_follow_url($_SESSION['active_user']['name']));

    // Redirect
        redirect_to(make_profile_url($_SESSION['active_user']['name']));
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Pingback to handle cache updating and direct messages
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function ping($rmt = false)
    {
        try 
        {

            $this->outer_template = null;

            if($rmt == false)
                $rmt = instance_model('remotes');

            @header('Content-type: text/xml');

            if(!isset($_POST['data']))
                throw new exception();

        // Decode ping
            $ping_data = $rmt->decode_ping($_POST['data']);


/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
            switch($ping_data->type)
            {
            case 'update':
                $csh = instance_model('message_cache');
                $csh->flag_cache_update($ping_data->data);

                echo $rmt->make_ping_response('success');
                break;

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
            case 'public':
            // Get the user
                $usr = instance_model('users');
                $user = $usr->get_user_by_name($ping_data->user);

                $dim = instance_model('direct_message');

            // Decode data xml
                $XML = simplexml_load_string($ping_data->data);

                $remote_name    = (string) $XML->remote_name;
                $remote_profile = (string) $XML->remote_profile;
                $remote_avatar  = (string) $XML->remote_avatar;
                $remote_message = (string) $XML->remote_message;
                $remote_time    = (string) $XML->remote_time;

            // Create DM
                $dim->new_dm($user[0]['ID'], $ping_data->type,
                    $remote_name, $remote_profile, $remote_avatar,
                    $remote_message, $remote_time);

                echo $rmt->make_ping_response('success');
                break;

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
            default:
                echo $rmt->make_ping_response('fail', 'Invalid ping type');
            }

        }
        catch(Exception $e)
        {
            throw $e;
            echo $rmt->make_ping_response('fail', 'Server error');
        }
    }
}
