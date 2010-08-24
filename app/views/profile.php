<div id="user_info" />
    <img src="<?php echo esc($user[0]['Avatar']); ?>" alt="<?php echo esc($user[0]['User_name']); ?>s avatar" />
    <h2><?php echo esc($user[0]['User_name']); ?></h2>
    <div style="clear: both"></div>
</div>

<?php display_errors(); ?>

<?php if(count($messages) > 0): ?>
<?php foreach ($messages as $message): ?>
    <div class="message">
        <p class="msg-content"><?php echo esc($message['Message']); ?></p>

        <div class="message_bottom">
            <p><?php echo format_time($message['Time']) ?></p>

            <?php if(isset($_SESSION['active_user']) && isset($form_message)): ?>
            <form action="<?php echo esc($form_target); ?>" method="post" />
                <input type="Submit" name="Submit" value="<?php echo esc($form_message); ?>" />
                <input type="hidden" name="id" value="<?php echo esc($message['ID']); ?>" />
            </form>
            <?php endif; ?>
            <div style="clear: both;"></div>
        </div>
    </div>
<?php endforeach; ?>
<?php else: ?>
    <p>No messages yet</p>
<?php endif; ?>
