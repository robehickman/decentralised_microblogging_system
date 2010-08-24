<?php display_errors(); ?>

<div id="chars_remaining">
</div>

<form action="<?php echo make_url("messages", "create"); ?>" method="POST" >
    <textarea name="message" id="message_input"></textarea>

    <p><input type="Submit" value="Submit" name="Submit" id="message_submit" /></p>
</form>

<?php if(count($messages) > 0): ?>
<?php foreach ($messages as $message): ?>
    <div class="message">
        <div class="message_left">
            <img src="<?php echo esc($message['Remote_avatar']); ?>" alt="<?php echo esc($message['Remote_name']) ?>s avatar" />
        </div>
        <div class="message_right">
            <a href="<?php echo esc($message['Remote_profile']); ?>"><strong><?php echo esc($message['Remote_name']) ?></strong></a>
            <p class="msg-content"><?php echo esc($message['Remote_message']); ?></p>
            <p><?php echo format_time($message['Remote_time']); ?></p>
        </div>
        <div style="clear: both;"></div>
    </div>
<?php endforeach; ?>
<?php else: ?>
Nothing here yet, type something into the box above
<?php endif; ?>

