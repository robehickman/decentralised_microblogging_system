<h2>Settings</h2>

<div class="gap"></div>

<table class="register">
    <form action="<?php echo make_url("settings"); ?>" method="post">
        <tr>
            <td><input name="0" type="text" value="<?php echo $form_vals[0]; ?>"/></td>
            <td>Email</td>
        </tr>

        <tr>
            <td><input name="1" type="text" value="<?php echo $form_vals[1]; ?>"/></td>
            <td>Full name</td>
        </tr>

        <tr>
            <td><input name="2" type="text" value="<?php echo $form_vals[2]; ?>"/></td>
            <td>Location</td>
        </tr>

        <tr>
            <td><input name="3" type="text" value="<?php echo $form_vals[3]; ?>"/></td>
            <td>Web</td>
        </tr>

        <tr>
            <td><textarea name="4" id="bio_input"><?php echo $form_vals[4]; ?></textarea></td>
            <td>Bio (<span id="bio_remaining">160</span>)</td>
        </tr>
    

        <tr>
            <td class="submit" colspan="2"><input type="Submit" name="Submit" value="Save"
                id="settings_submit" /></td>
        </tr>
    </form>
</table>

<div class="small_gap"></div>

<?php display_errors(); ?>
