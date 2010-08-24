
<?php display_template('relations', array('uid' => $uid, 'uname' => $uname)); ?>

<h4>Follow new user</h4>
<form id="user_follow_form" action="<?php echo make_url("relations", "create"); ?>" method="POST">
    <input type="text" name="follow_user" />
    <input type="Submit" name="Submit" value="Follow" class="submit" />
</form>

<div class="small_gap"></div>

<div class="attributes">
    <p><strong>Messages: </strong><?php echo esc(get_message_count($uid)); ?></p>
    <p><strong><a href="<?php echo make_url("dmessages", "public_msg"); ?>">@<?php echo esc($uname); ?></a></strong></p>
</div>
