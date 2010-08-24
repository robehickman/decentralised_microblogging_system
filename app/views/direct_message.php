<?php display_errors(); ?>

<div id="chars_remaining">
    <p>140</p>
</div>

<form action="<?php echo make_url("messages", "create"); ?>" method="POST" >
    <textarea name="message" id="message_input"></textarea>

    <p><input type="Submit" value="Submit" name="Submit" id="message_submit" /></p>
</form>

<?php if(count($messages) > 0): ?>
<?php foreach ($messages as $message): ?>
    <div class="message">
        <div class="message_left">
            <img src="<?php echo esc($message['Remote_avatar']); ?>"
                alt="<?php echo esc($message['Remote_name']); ?>s avatar" />
        </div>

        <div class="message_right">
            <h4><a href="<?php echo esc($message['Remote_profile']) ?>"><?php echo esc($message['Remote_name']); ?></a></h4>
            <p class="msg-content"><?php echo esc($message['Remote_message']); ?></p>

            <div class="message_bottom">
                <p><?php echo format_time($message['Remote_time'])?></p>

                <?php if(isset($form_message)): ?>
                <form action="<?php echo $form_target; ?>" method="post" />
                    <input type="Submit" name="Submit" value="<?php echo $form_message; ?>" />
                    <input type="hidden" name="id" value="<?php echo $message['ID']; ?>" />
                </form>
                <?php endif; ?>
                <div style="clear: both;"></div>
            </div>
        </div>
        <div style="clear: both;"></div>
    </div>
<?php endforeach; ?>
<?php else: ?>
    <p>Nothing here</p>
<?php endif; ?>
