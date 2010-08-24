<h2>Avatar</h2>

<div class="gap"></div>

<div class="avatar_settings">
    <h4>Current avatar</h4>
    <img src="<?php echo esc($user[0]['Avatar']); ?>" alt="<?php echo esc($user[0]['User_name']); ?>s avatar" />


    <h4>Change avatar</h4>

    <form enctype="multipart/form-data" action="<?php echo make_url('settings', 'avatar'); ?>" method="post">
        <input type="hidden" name="MAX_FILE_SIZE" value="1000000000000000" />
        <input name="file" type="file" /><br />
        <input type="Submit" name="Submit" value="Change" />
    </form>
    <p>(PNG and JPG formats supported, image will be resized to 100 X 100 px)</p>
</div>

<div class="small_gap"></div>

<?php display_errors(); ?>
