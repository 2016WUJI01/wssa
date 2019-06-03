<dl>
  <?php if ($competition->type == Competition::TYPE_WCA): ?>
  <dt><?php echo Yii::t('Competition', 'WCA Competition'); ?></dt>
  <dd>
    <?php echo Yii::t('Competition', 'This competition is recognized as an official World Cube Association competition. Therefore, all competitors should be familiar with the {regulations}.', array(
    '{regulations}'=>CHtml::link(Yii::t('Competition', 'WCA regulations'), $competition->getWcaRegulationUrl(), array('target'=>'_blank')),
  ));?>
  </dd>
  <?php endif; ?>
  <dt><?php echo Yii::t('Competition', 'Regulations'); ?></dt>
  <dd><?php echo $competition->getAttributeValue('regulations'); ?></dd>
</dl>
