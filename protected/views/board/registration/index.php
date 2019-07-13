<div class="row">
  <div class="col-lg-12">
  <div class="portlet portlet-default">
    <div class="portlet-heading">
      <div class="portlet-title">
        <h4>报名管理</h4>
      </div>
      <div class="clearfix"></div>
    </div>
    <div class="panel-collapse collapse in">
    <div class="portlet-body">
      <?php $form = $this->beginWidget('ActiveForm', array(
        'action'=>array('/board/registration/index'),
        'method'=>'get',
        'id'=>'registration-form',
        'htmlOptions'=>array(
        ),
      )); ?>
      <?php echo Html::formGroup(
        $model, 'competition_id', array(),
        $form->dropDownList($model, 'competition_id', Competition::getRegistrationCompetitions(), array(
          'prompt'=>'',
        ))
      ); ?>
      <?php $this->endWidget(); ?>
      <?php if ($model->competition !== null): ?>
      <?php echo CHtml::link('导出名单', array('/board/registration/export', 'id'=>$model->competition_id), array('class'=>'btn btn-square btn-large btn-purple')); ?>
      <?php echo CHtml::link('发邮件给选手', array('/board/registration/sendNotice', 'id'=>$model->competition_id), array('class'=>'btn btn-square btn-large btn-blue')); ?>
      <?php if ($model->competition->show_qrcode): ?>
      <?php echo CHtml::link('签到管理', array('/board/registration/signin', 'Registration'=>['competition_id'=>$model->competition_id]), array('class'=>'btn btn-square btn-large btn-red')); ?>
      <?php endif; ?>
      <?php endif; ?>
      <?php $columns = $model->getAdminColumns(); ?>
      <?php $this->widget('RepeatHeaderGridView', array(
        'dataProvider'=>$model->search($columns, false, true),
        'template'=>'{pager}{items}{pager}',
        // 'filter'=>$model,
        'columns'=>$columns,
      )); ?>
    </div>
    </div>
  </div>
  </div>
</div>
<div tabindex="-1" id="comments-modal" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
      </div>
      <div class="modal-footer">
        <button data-dismiss="modal" class="btn btn-default" type="button">关闭</button>
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<?php
Yii::app()->clientScript->registerScript('registration',
<<<EOT
  var modalBody = $('#comments-modal').find('.modal-body');
  $(document).on('change', '#Registration_competition_id', function() {
    $('#registration-form').submit();
  }).on('click', '.view-comments', function() {
    modalBody.text($(this).data('comments'));
  });
  if ('ontouchstart' in window) {
    $('.modal.fade').removeClass('fade');
  }
EOT
);
