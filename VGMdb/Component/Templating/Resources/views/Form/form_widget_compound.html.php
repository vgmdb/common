<div <?php echo $view['form']->block($form, 'widget_container_attributes') ?>>
    <?php if (!$form->parent && $errors): ?>
    <div class="control-group error alert alert-error"><?php echo $view['form']->errors($form) ?></div>
    <?php endif ?>
    <?php echo $view['form']->block($form, 'form_rows') ?>
    <?php echo $view['form']->rest($form) ?>
</div>