<h2><?php echo $title ?></h2>

<div class="gap"></div>

<?php if(count($users) > 0): ?>
<?php foreach($users as $user): ?>
<div class="message">
    <div class="message_left">
        <img src="<?php echo esc($user['Remote_avatar']); ?>" alt="<?php echo esc($user['Remote_name']); ?>s avatar" />
    </div>

    <div class="message_right">
        <?php $latest = get_latest_by_remote($rmt, $user['Remote_URL']); ?>
        <h4><a href="<?php echo esc($user['Remote_profile']) ?>"><?php echo esc($user['Remote_name']); ?></a></h4>

        <?php if(count($latest) > 0): ?>
        <p class="msg-content"><?php echo esc($latest[0]['Remote_message']); ?></p>

        <div class="message_bottom">
            <p><?php echo format_time($latest[0]['Remote_time']); ?></p>

            <?php if(isset($_SESSION['active_user']) && isset($form_message)): ?>
            <form action="<?php echo esc($form_target); ?>" method="post" />
                <input type="Submit" name="Submit" value="<?php echo esc($form_message); ?>" />
                <input type="hidden" name="id" value="<?php echo $user['ID']; ?>" />
            </form>
            <?php endif; ?>
            <div style="clear: both;"></div>
        </div>
        <?php else: ?>
        <p>No messages yet</p>
        <?php endif; ?>

        </div>
        <div style="clear: both;"></div>
</div>
<?php endforeach; ?>
<?php else: ?>
<p>Nothing here</p>
<?php endif; ?>
