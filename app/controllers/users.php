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

class ctrl_users extends controller_base 
{
/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Setup
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function __CONSTRUCT ()
    {
        $this->load_outer_template('main');
        load_helper('relations');
        load_helper('errors');
        load_helper('messages');
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Allow a user to log in
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function login()
    {
        if(!isset($_POST['Submit']))
        {
        // display login form
            $view = instance_view('login');
            $view = $view->parse_to_variable(array());

            $this->set_template_paramiters(
                array('main_content' => $view,
                      'sidebar'      => ''));
        }
        else
        {
            try
            {
            // handle log in
                $user     = $_POST['user'];
                $password = $_POST['pass'];

                $usr = instance_model('users');
                $selected_user = $usr->verify_user($user, $password);

                if($selected_user == false)
                {
                    throw new exception();
                }
                else
                    log_in_user($selected_user[0]['User_name'],
                        $selected_user[0]['ID']);
            }
            catch(redirecting_to $e)
            {
                throw $e;
            }
            catch(exception $e)
            {
                new_flash('Username or password is incorrect', 1);

            // display login form
                $view = instance_view('login');
                $view = $view->parse_to_variable(array());

                $this->set_template_paramiters(
                    array('main_content' => $view,
                          'sidebar'      => ''));
            }
        }
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Allow a user to log out
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function logout()
    {
        $this->outer_template = '';
        $_SESSION = array();

        redirect_to(make_url('messages'));
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Allow a user to register
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function register()
    {
        if(ALLOW_REGISTRATION == false)
            die('Registration has bean disabled on this node');

        if(!isset($_POST['Submit']))
        {
            $form_vals = make_reg_vals_array('', '', '', '');

        // display register form
            $view = instance_view('register');
            $view = $view->parse_to_variable(array(
                'form_vals' => $form_vals));

            $this->set_template_paramiters(
                array('main_content' => $view,
                      'sidebar'      => ''));
        }
        else
        {
        // reed the form
            $form_vals = array(
                'errs'   => array(),
                'name'   => $_POST['name'],
                'email'  => $_POST['email'], 
                'pass'   => $_POST['pass'],
                'pass_v' => $_POST['pass_v']);

        // Instance users model
            $usr = instance_model('users');
            $test_exists = array();

        // Validate user name
            try
            {
                validate_username($form_vals['name']);
                $test_exists = $usr->get_user_by_name($form_vals['name']);

                if($test_exists != array())
                {
                    new_flash('User name is already tacken on this node', 1);
                    $form_vals['name'] = '';
                }
            }
            catch(exception $e)
            {
                if(strlen($form_vals['name']) < 3)
                {
                    new_flash('User name too short, min 3 charicters', 1);
                    $form_vals['name'] = '';
                }

                else if(strlen($form_vals['name']) > 30)
                {
                    new_flash('User name too long, max 30 charicters', 1);
                    $form_vals['name'] = '';
                }

                else if(!preg_match('/^[a-zA-Z0-9_]+$/', $form_vals['name']))
                {
                    new_flash('User names must contain only alphanumeric charicters and the underscore', 1);
                    $form_vals['name'] = '';
                }
            }

        // Validate email
            if(!preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+'
                .'(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/', $form_vals['email']))
            {
                new_flash('Email address is invalid', 1);
                $form_vals['email'] = "";
            }

        // Validate passwords
            if(mb_strlen($form_vals['pass'], 'utf8') < 6)
                new_flash('Password too short, min 6 charicters', 1);

            else if(sha1($form_vals['pass']) != sha1($form_vals['pass_v']))
                new_flash('Passwords do not match', 1);

            if(count(get_errors()) == 0)
            {
            // Everything was valid, save, login and redirect
                $usr->new_user($form_vals['name'], $form_vals['email'], $form_vals['pass']);

                $new_id = $usr->get_user_by_name($form_vals['name']);

                log_in_user($new_id[0]['User_name'], $new_id[0]['ID']);
            }

        // else re-display the register form and show errors
            else
            {
                $view = instance_view("register");
                $view = $view->parse_to_variable(array(
                    'form_vals' => $form_vals));

                $this->set_template_paramiters(
                    array('main_content' => $view,
                          'sidebar'      => ''));
            }
        }
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Display a list of the users on this node
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function index()
    {
        $usr = instance_model('users');
        $users = $usr->get_users();

        $view = instance_view('user_list2');
        $view = $view->parse_to_variable(array(
            'users' => $users));

        $this->set_template_paramiters(
            array('main_content' => $view,
                  'sidebar'      => "Welcome to distributed microblogging"));
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Display a users profile
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function profile()
    {
        $flash = 'The specified user does not exist, here are the users on this node';
        if(!isset($this->params[2]))
        {
            new_flash($flash, 1);
            redirect_to(make_url('users'));
        }

        $user_name = $this->params[2];

        $usr = instance_model('users');
        $user = $usr->get_user_by_name($user_name);

        if($user == array())
        {
            new_flash($flash, 1);
            redirect_to(make_url('users'));
        }

        $msg = instance_model('messages');
        $messages = $msg->get_by_user_id($user[0]['ID']);

        $view = instance_view('profile');
        $view = $view->parse_to_variable(array(
            'messages' => $messages,
            'user'     => $user,
            'form_message' => 'Delete',
            'form_target'  => make_url('messages', 'destroy')));

        $sb_view = instance_view('profile_sidebar');
        $sb_view = $sb_view->parse_to_variable(array(
            'uid'      => $user[0]['ID'],
            'uname'    => $user[0]['User_name'],
            'fname'    => $user[0]['Full_name'],
            'location' => $user[0]['Location'],
            'web'      => $user[0]['Web'],
            'bio'      => $user[0]['Bio']));

    // Display
        $this->set_template_paramiters(
            array('main_content' => $view,
                  'sidebar'      => $sb_view));
    }
}
