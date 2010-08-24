<div class="attributes">
    <p><strong>Messages: </strong><?php echo esc(get_message_count($uid)); ?></p>

    <?php if($fname != ""): ?>
    <p><strong>Name</strong> <?php echo esc($fname) ?></p>
    <?php endif; ?>

    <?php if($location != ""): ?>
    <p><strong>Location</strong> <?php echo esc($location) ?></p>
    <?php endif; ?>

    <?php if($web != ""): ?>
    <p><strong>Web</strong> <a href="<?php echo esc($web) ?>"><?php echo esc($web) ?></a></p>
    <?php endif; ?>

    <?php if($bio != ""): ?>
    <p><strong>Bio</strong> <?php echo esc($bio) ?></p>
    <?php endif; ?>

    <p><strong>Message address</strong> <?php echo esc(make_follow_url($uname)); ?></p>
</div>

<?php display_template('relations', array('uid' => $uid, 'uname' => $uname)); ?>
