<h2>Password</h2>

<div class="gap"></div>

<table class="register">
    <form action="<?php echo make_url('settings', 'password'); ?>" method="post">
        <tr>
            <td><input name="0" type="password" /></td>
            <td>Old password</td>
        </tr>

        <tr>
            <td><input name="1" type="password" /></td>
            <td>New password</td>
        </tr>

        <tr>
            <td><input name="2" type="password" /></td>
            <td>Verify new password</td>
        </tr>

        <tr>
            <td class="submit" colspan="2"><input type="Submit" name="Submit" value="Save" /></td>
        </tr>
    </form>
</table>

<div class="small_gap"></div>

<?php display_errors(); ?>
