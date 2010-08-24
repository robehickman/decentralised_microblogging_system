<?php if(count($GLOBALS['errors']) > 0): ?>
<div id="errors">
<h4 id="errors_head">Errors</h4>

    <ul>
    <?php foreach($GLOBALS['errors'] as $error): ?>
        <li><?php print esc($error); ?></li>
    <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>
