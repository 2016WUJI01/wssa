<div class="row">
  <div class="col-lg-12">
    <div class="page-title">
      <h1><?php echo $model->isNewRecord ? '新增' : '编辑'; ?>报名信息</h1>
    </div>
  </div>
  <!-- /.col-lg-12 -->
</div>
<div class="row">
  <div class="col-lg-12">
    <div class="portlet portlet-default">
      <div class="portlet-heading">
        <div class="portlet-title">
          <h4>报名信息</h4>
        </div>
        <div class="clearfix"></div>
      </div>
      <div class="panel-collapse collapse in">
        <div class="portlet-body">
          <?php $form = $this->beginWidget('ActiveForm', array(
            'htmlOptions'=>array(
            ),
          )); ?>
           <?php echo Html::formGroup(
              $model, 'user', array(),
              $form->labelEx($model, 'user_id'),
              CHtml::textField('', $model->user->getCompetitionName(), array(
                'class'=>'form-control',
                'disabled'=>true,
              ))
            );?>
           <?php echo Html::formGroup(
              $model, 'events', array(),
              $form->labelEx($model, 'events'),
              $this->widget('CompetitionRegisterForm', array(
                'model'=>$model,
                'competition'=>$model->competition,
                'name'=>'events',
                'form'=>$form,
              ), true),
              $form->error($model, 'events', array('class'=>'text-danger'))
            );?>
            <?php echo Html::formGroup(
              $model, 'comments', array(),
              $form->labelEx($model, 'comments'),
              $form->textArea($model, 'comments', array(
                'class'=>'form-control',
                'rows'=>4,
              )),
              $form->error($model, 'comments', array('class'=>'text-danger'))
            ); ?>
            <button type="submit" class="btn btn-theme"><?php echo Yii::t('common', 'Submit'); ?></button>
          <?php $this->endWidget(); ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
Yii::app()->clientScript->registerScript('registration',
<<<EOT
  $(document).on('change', '.registration-events', function() {
    const extra = $(this).parent().parent().parent().parent().find('.extra-info')
    console.log(extra)
    extra[this.checked ? 'addClass' : 'removeClass']('show')
  });
  $('.registration-events').trigger('change');
EOT
);
