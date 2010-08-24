<h2>Register</h2>

<div class="gap"></div>

<table class="register">
    <form action="<?php echo esc(make_url("users", "register")); ?>" method="post">
        <tr>
        <td><input name="name"  type="text" value="<?php echo $form_vals['name']; ?>" /></td>
            <td>User name</td>
        </tr>

        <tr>
            <td><input name="email" type="text" value="<?php echo $form_vals['email']; ?>"/></td>
            <td>Email</td>
        </tr>
    
        <tr>
            <td><input name="pass"   type="password" /></td>
            <td>Password</td>
        </tr>

        <tr>
            <td><input name="pass_v"   type="password" /></td>
            <td>Retype password</td>
        </tr>

        <tr>
            <td class="submit" colspan="2"><input type="Submit" name="Submit" value="Register" /></td>
        </tr>
    </form>
</table>

<?php display_errors(); ?>
