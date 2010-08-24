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

class ctrl_settings extends controller_base
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
 * Alow the user to change there profile
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function index()
    {
        if(!isset($_SESSION['active_user']))
            redirect_to(make_url("users"));

        $usr = instance_model('users');
        $user = $usr->get_user_by_id($_SESSION['active_user']['id']);

        if($user == array())
            throw new no_such_user_exception();

        if(!isset($_POST['Submit']))
        {
            $form_vals = array(
                $user[0]['E-mail'],
                $user[0]['Full_name'],
                $user[0]['Location'],
                $user[0]['Web'],
                $user[0]['Bio']);

        // Display main
            $view = instance_view("settings_main");
            $view = $view->parse_to_variable(array(
                'form_vals' => $form_vals));
        }
        else
        {
            $form_vals = $_POST;

        // Validate email
            try {
                validate_email($form_vals[0]);
            } catch(exception $e) {
                new_flash('Email address is invalid', 1);
                $form_vals[0] = $user[0]['E-mail'];
            }

        // Validate full name
            try {
                validate_50($form_vals[1]);
            } catch(exception $e) {
                new_flash('Full name is too long, max 50 chars', 1);
                $form_vals[1] = $user[0]['User_name'];
            }
            
        // Validate location
            try {
                validate_50($form_vals[2]);
            } catch(exception $e) {
                new_flash('Location is too long, max 50 chars', 1);
                $form_vals[2] = $user[0]['Location'];
            }

        // Validate web
            try {
                validate_url($form_vals[3]);
            } catch(exception $e) {
                new_flash('Website URL is invalid', 1);
                $form_vals[3] = $user[0]['Web'];
            }

        // Validate bio
            try {
                validate_bio($form_vals[4]);
            } catch(exception $e) {
                new_flash('Bio is invalid', 1);
                $form_vals[4] = $user[0]['Bio'];
            }

            if(count(get_errors()) == 0)
            {
            // Everything was vald, save updated user options
                $usr->update_user(
                    $user[0]['ID'],
                    $form_vals[0],
                    $form_vals[1],
                    $form_vals[2],
                    $form_vals[3],
                    $form_vals[4]);

                redirect_to(make_url('settings'));
            }
            else
            {
            // Something was invalid, redisplay main
                $view = instance_view("settings_main");
                $view = $view->parse_to_variable(array(
                    'form_vals' => $form_vals));
            }
        }

    // Display sidebar
        $sb_view = instance_view("settings_sidebar");
        $sb_view = $sb_view->parse_to_variable(array(
            'uid'   => $_SESSION['active_user']['id'],
            'uname' => $_SESSION['active_user']['name']));

        $this->set_template_paramiters(
            array('main_content' => $view,
                  'sidebar'      => $sb_view));
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Alow the user to change there password
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function password()
    {
        if(!isset($_SESSION['active_user']))
            redirect_to(make_url("users"));

        $usr = instance_model('users');
        $user = $usr->get_user_by_id($_SESSION['active_user']['id']);

        if($user == array())
            throw new no_such_user_exception();

        if(!isset($_POST['Submit']))
        {
        // Display main
            $view = instance_view("settings_password");
            $view = $view->parse_to_variable(array());
        }
        else
        {
            $form_vals = $_POST;

        // Varify old password
            $selected_user = $usr->verify_user($user[0]['User_name'], $form_vals[0]);

            if($selected_user === false)
                new_flash('Old password is incorrect', 1);

        // Validate new password
            if(mb_strlen($form_vals[1], 'utf8') < 6)
                new_flash('New password too short, min 6 charicters', 1);

            else if(sha1($form_vals[1]) != sha1($form_vals[2]))
                new_flash('Passwords do not match', 1);

            if(count(get_errors()) == 0)
            {
            // Everything was vald, save updated password
                $usr->update_password(
                    $user[0]['ID'],
                    $form_vals[1]);

                redirect_to(make_url('settings', 'password'));
            }
            else
            {
            // Something was invalid, redisplay main
                $view = instance_view("settings_password");
                $view = $view->parse_to_variable(array());
            }
        }

    // Display sidebar
        $sb_view = instance_view("settings_sidebar");
        $sb_view = $sb_view->parse_to_variable(array(
            'uid'   => $_SESSION['active_user']['id'],
            'uname' => $_SESSION['active_user']['name']));

        $this->set_template_paramiters(
            array('main_content' => $view,
                  'sidebar'      => $sb_view));
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Alow the user to change there avitar
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function avatar()
    {
        if(!isset($_SESSION['active_user']))
            redirect_to(make_url("users"));

        $usr = instance_model('users');
        $user = $usr->get_user_by_id($_SESSION['active_user']['id']);

        if($user == array())
            throw new no_such_user_exception();

        if(!isset($_POST['Submit']))
        {
        // Display main
            $view = instance_view('settings_avatar');
            $view = $view->parse_to_variable(array(
                'user' => $user));
        }
        else
        {
        // Validate file type
            $type = array_pop(preg_split('/\./', $_FILES['file']['name']));

            $valid_extensions = array('png', 'jpg', 'jpeg', 'JPG', 'JPEG');

            for($found_type = 0; $found_type < count($valid_extensions); $found_type ++)
                if($type == $valid_extensions[$found_type])
                {
                    $found_type = -1;
                    break;
                }

            if($found_type != -1)
            {
                new_flash('Invalid file type', 1);
                redirect_to(make_url('settings', 'avatar'));
            }

            $tmpname = 'media/' . sha1(time()) . '.' . $type;

            if (@move_uploaded_file($_FILES['file']['tmp_name'], $tmpname)) 
            {
            // Load the image
                if($type == 'png')
                    $img = @imagecreatefrompng($tmpname);
                else
                    $img = @imagecreatefromjpeg($tmpname);

                if($img == false)
                {
                    new_flash('Problem with image', 1);
                    redirect_to(make_url('settings', 'avatar'));
                }

            // Resize
                $oldsize = getimagesize($tmpname);

                $img_n = imagecreatetruecolor(100, 100);
                imagecopyresampled($img_n, $img, 0, 0, 0, 0,
                    100, 100, $oldsize[0], $oldsize[1]);

                $avatar = 'media/' . $_SESSION['active_user']['name'] . '.jpg';
                $result = imagejpeg($img_n, $avatar , 90); 

                unlink($tmpname);

                if($result == false)
                {
                    new_flash('Problem with image', 1);
                    redirect_to(make_url('settings', 'avatar'));
                }

                print make_ext_url($avatar);

                $usr->update_avatar($user[0]['ID'], 
                    make_ext_url($avatar));

            // Delete the old avatar as long as it is not the default
                $old_avatar = basename($user[0]['Avatar']);
                if(preg_match('/.+default_avatar\.jpg/', $old_avatar))
                    unlink('media/' . $old_avatar);

                redirect_to(make_url('settings', 'avatar'));
            }
            else
            {
                new_flash("File failed to upload");
                redirect_to(make_url('settings', 'avatar'));
            }
        }

    // Display sidebar
        $sb_view = instance_view("settings_sidebar");
        $sb_view = $sb_view->parse_to_variable(array(
            'uid'   => $_SESSION['active_user']['id'],
            'uname' => $_SESSION['active_user']['name']));

        $this->set_template_paramiters(
            array('main_content' => $view,
                  'sidebar'      => $sb_view));
    }
}
