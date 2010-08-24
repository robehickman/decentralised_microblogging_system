<div id="relations">
    <p id="following"> <a href="
        <?php echo make_url("relations", "following", esc($uname)); ?>">
        <strong>Following</strong><br /><?php echo esc(get_following($uid)); ?>
    </a></p>

    <p id="followers"><a href="
        <?php echo make_url("relations", "followers", esc($uname)); ?>">
        <strong>Followers</strong><br /><?php echo esc(get_followers($uid)); ?>
    </a></p>
</div>
