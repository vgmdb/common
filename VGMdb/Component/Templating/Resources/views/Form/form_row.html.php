<div class="control-group <?php if ($errors): ?>error<?php endif ?>">
    <?php echo $view['form']->label($form) ?>
    <div class="controls">
    <?php echo $view['form']->widget($form) ?>
    <?php echo $view['form']->errors($form) ?>
    </div>
</div>
