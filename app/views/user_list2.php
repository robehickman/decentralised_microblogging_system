<h2>Users on this node</h2>

<div class="gap"></div>

<?php display_errors(); ?>

<?php if($users == array()): ?>
    <h4>There are no users on this node yet</h4>

<?php else: ?>
    <?php foreach($users as $user): ?>
    <div class="message">
        <div class="message_left">
            <img src="<?php echo esc($user['Avatar']); ?>" alt="" />
        </div>

        <div class="message_right">
            <?php $latest = get_latest_by_local($user['ID']); ?>
            <h4><a href="<?php echo make_profile_url($user['User_name']) ?>"><?php echo esc($user['User_name']); ?></a></h4>
            <?php if($latest != array()): ?>
            <p class="msg-content"><?php echo esc($latest[0]['Message']);?></p>
            <p><?php echo format_time($latest[0]['Time']); ?></p>
            <?php else: ?>
            <p class="msg-content">No messages yet</p>
            <?php endif; ?>
        </div>
        <div style="clear: both;"></div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>
