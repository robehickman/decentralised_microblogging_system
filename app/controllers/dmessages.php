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

class ctrl_dmessages extends controller_base
{
/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Setup
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function __CONSTRUCT ()
    {
        $this->load_outer_template('main');
        load_helper('messages');
        load_helper('relations');
        load_helper('users');
        load_helper('errors');
        load_helper('crypto');
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Display public messages sent to a user
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function public_msg()
    {
        if(!isset($_SESSION['active_user']))
            redirect_to(make_url('users', 'login'));

        $_SESSION['direct_to'] = make_url('dmessages', 'public_msg');

        $dm  = instance_model('direct_message');

    // display public messages
        $messages = $dm->get_by_user_id($_SESSION['active_user']['id']);

        $view = instance_view("direct_message");
        $view = $view->parse_to_variable(array(
            'messages' => $messages,
            'uname'    => $_SESSION['active_user']['name'],
            'uid'      => $_SESSION['active_user']['id'],
            'form_message' => 'Delete',
            'form_target' => make_url('dmessages', 'destroy_public')));

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
 * Delete a public message
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function destroy_public()
    {

        if(!isset($_SESSION['active_user']))
            redirect_to(make_url('users', 'login'));

        if(!isset($_POST['Submit']))
            redirect_to(make_url('dmessages', 'public_msg'));

        $user_id = $_SESSION['active_user']['id'];

    // get message
        $dm  = instance_model('direct_message');
        $message = $dm->get_by_id($user_id, $_POST['id']);

        if($message == array())
        {
            new_flash('Message does not exist', 1);
            redirect_to(make_url('dmessages', 'public_msg'));
        }

    // Delete
        $dm->delete_by_id($user_id, $_POST['id']);

        redirect_to(make_url('dmessages', 'public_msg'));
    }
}
