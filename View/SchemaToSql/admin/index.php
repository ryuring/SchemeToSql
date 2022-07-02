<?php
/**
 * @var array $sql
 * @var array $plugins
 * @var BcAppView $this
 */
$this->pageTitle = 'SQL生成';
?>

<?php echo $this->BcForm->create('CuSchema') ?>
<?php echo $this->BcForm->input('plugin', ['type' => 'select', 'options' => $plugins]) ?>
<?php echo $this->BcForm->input('version', ['type' => 'text', 'placeholder' => 'バージョン']) ?>
<?php echo $this->BcForm->submit('SQL生成', ['div' => false, 'class' => 'button bca-btn bca-actions__item',
      'data-bca-btn-type' => 'save',
      'data-bca-btn-size' => 'lg',
      'data-bca-btn-width' => 'lg',]) ?>
<?php echo $this->BcForm->end() ?>

<?php if(!empty($sql)): ?>
<div style="border:1px dotted #CCC;padding:0 20px;margin-top:40px;">
<?php foreach($sql as $value): ?>
<p><?php echo nl2br(h($value)) ?></p>
<?php endforeach ?>
</div>
<?php endif ?>

