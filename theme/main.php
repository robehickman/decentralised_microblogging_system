<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>text</title>

        <script type="text/javascript" src="<?php echo get_app_root(); ?>/src/libs/jquery.js"></script>
        <script type="text/javascript" src="<?php echo get_app_root(); ?>/app/script.js"></script>

        <link rel="stylesheet" type="text/css" href="<?php echo get_app_root(); ?>/theme/main.css" />
    </head>
    
    <body>
        <div id="container">
            <div id="options">
                <ul>
                <?php if(isset($_SESSION['active_user'])): ?>
                    <li><a href="<?php echo make_url('messages'); ?>">Home</a></li>
                    <li><a href="<?php echo make_url('users', 'profile', esc($_SESSION['active_user']['name'])); ?>">Profile</a></li>
                    <li><a href="<?php echo make_url('settings'); ?>">Settings</a></li>
                    <li><a href="<?php echo make_url('users'); ?>">Users</a></li>
                    <li><a href="<?php echo make_url('users', 'logout'); ?>">Sign out</a></li>

                <?php else: ?>
                    <li><a href="<?php echo make_url('users'); ?>">Users</a></li>
                    <li><a href="<?php echo make_url('users', 'login'); ?>">Sign in</a></li>
                    <?php if(ALLOW_REGISTRATION == true): ?>
                    <li><a href="<?php echo make_url('users', 'register'); ?>">Register</a></li>
                    <?php endif; ?>
                <?php endif; ?>
                </ul>
            </div>
            <div style="clear: both;"></div>

            <div id="sub-container">
                <div id="main-content">
                    <?php echo $main_content ?>
                </div>

                <div id="sidebar">
                    <?php echo $sidebar ?>
                </div>

                <div style="clear: both;"></div>
            </div>
        </div>
    </body>
</html>
