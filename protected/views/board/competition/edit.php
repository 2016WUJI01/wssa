<div class="row">
  <div class="col-lg-12">
    <div class="page-title">
      <h1>比赛信息</h1>
    </div>
  </div>
  <!-- /.col-lg-12 -->
</div>
<div class="row">
  <div class="col-lg-12">
    <div class="portlet portlet-default">
      <div class="portlet-heading">
        <div class="portlet-title">
          <h4>比赛信息</h4>
        </div>
        <div class="clearfix"></div>
      </div>
      <div class="panel-collapse collapse in">
        <div class="portlet-body">
          <?php $form = $this->beginWidget('ActiveForm', array(
            'htmlOptions'=>array(
              'class'=>'clearfix row',
            ),
            'enableClientValidation'=>true,
          )); ?>
          <?php echo $form->errorSummary($model, null, null, array(
            'class'=>'text-danger col-lg-12',
          )); ?>
          <div class="col-sm-12">
            <p class="text-danger">
              <b>友情提示</b>：比赛信息可以多次编辑，请注意保存。
            </p>
          </div>
          <?php if ($this->user->isOrganizer() && $model->isPublic()): ?>
          <div class="col-lg-12">
            <div class="alert alert-danger">该比赛已公示，基本信息不能修改，如需修改请联系<a href="mailto:<?php echo Yii::app()->params->adminEmail; ?>"><i class="fa fa-envelope"></i>管理员</a></div>
          </div>
          <?php endif; ?>
          <?php echo Html::formGroup(
            $model, 'name_zh', array(
              'class'=>'col-lg-12',
            ),
            $form->labelEx($model, 'name_zh', array(
              'label'=>'中文名',
            )),
            '<div class="input-group">',
            CHtml::tag('div', ['class'=>'input-group-addon'], $model->prefix),
            Html::activeTextField($model, 'name_zh'),
            '</div>',
            $form->error($model, 'name_zh', array('class'=>'text-danger'))
          );?>
          <?php echo Html::formGroup(
            $model, 'name', array(
              'class'=>'col-lg-12',
            ),
            $form->labelEx($model, 'name', array(
              'label'=>'英文名',
            )),
            '<div class="input-group">',
            CHtml::tag('div', ['class'=>'input-group-addon'], $model->prefix),
            Html::activeTextField($model, 'name'),
            '</div>',
            '<div class="help-text">用以生成比赛链接，如Beijing Open</div>',
            $form->error($model, 'name', array('class'=>'text-danger'))
          );?>
          <div class="clearfix"></div>
          <?php echo Html::formGroup(
            $model, 'person_num', array(
              'class'=>'col-md-4',
            ),
            $form->labelEx($model, 'person_num', array(
              'label'=>'人数限制',
            )),
            Html::activeTextField($model, 'person_num'),
            $form->error($model, 'person_num', array('class'=>'text-danger'))
          );?>
          <?php echo Html::formGroup(
            $model, 'type', array(
              'class'=>'col-md-4',
            ),
            $form->labelEx($model, 'type', array(
              'label'=>'类型',
            )),
            $form->dropDownList($model, 'type', $types, array(
              'class'=>'form-control',
            )),
            $form->error($model, 'type', array('class'=>'text-danger'))
          );?>
          <?php echo Html::formGroup(
            $model, 'entry_fee', array(
              'class'=>'col-md-4'
            ),
            $form->labelEx($model, 'entry_fee', array(
              'label'=>'基础报名费',
            )),
            Html::activeTextField($model, 'entry_fee'),
            $form->error($model, 'entry_fee', array('class'=>'text-danger'))
          );?>
          <div class="clearfix"></div>
          <?php if ($this->user->isAdministrator()): ?>
          <?php echo Html::formGroup(
            $model, 'wssa_url', array(
              'class'=>'col-sm-12'
            ),
            $form->labelEx($model, 'wssa_url'),
            Html::activeTextField($model, 'wssa_url'),
            $form->error($model, 'wssa_url', array('class'=>'text-danger'))
          );?>
          <div class="clearfix"></div>
          <?php endif; ?>
          <?php echo Html::formGroup(
            $model, 'second_stage_date', array(
              'class'=>'col-md-4',
            ),
            $form->labelEx($model, 'second_stage_date', array(
              'label'=>'第二阶段时间' . Html::fontAwesome('question-circle', 'b'),
              'data-toggle'=>'tooltip',
              'title'=>'不采用分阶段报名费的比赛忽略此项',
            )),
            Html::activeTextField($model, 'second_stage_date', array(
              'class'=>'datetime-picker',
              'data-date-format'=>'yyyy-mm-dd hh:ii:00',
            )),
            $form->error($model, 'second_stage_date', array('class'=>'text-danger'))
          );?>
          <?php echo Html::formGroup(
            $model, 'second_stage_ratio', array(
              'class'=>'col-md-4',
            ),
            $form->labelEx($model, 'second_stage_ratio', array(
              'label'=>'第二阶段倍率',
            )),
            Html::activeTextField($model, 'second_stage_ratio'),
            $form->error($model, 'second_stage_ratio', array('class'=>'text-danger'))
          );?>
          <div class="clearfix"></div>
          <?php echo Html::formGroup(
            $model, 'third_stage_date', array(
              'class'=>'col-md-4',
            ),
            $form->labelEx($model, 'third_stage_date', array(
              'label'=>'第三阶段时间' . Html::fontAwesome('question-circle', 'b'),
              'data-toggle'=>'tooltip',
              'title'=>'不采用分阶段报名费的比赛忽略此项',
            )),
            Html::activeTextField($model, 'third_stage_date', array(
              'class'=>'datetime-picker',
              'data-date-format'=>'yyyy-mm-dd hh:ii:00',
            )),
            $form->error($model, 'third_stage_date', array('class'=>'text-danger'))
          );?>
          <?php echo Html::formGroup(
            $model, 'third_stage_ratio', array(
              'class'=>'col-md-4',
            ),
            $form->labelEx($model, 'third_stage_ratio', array(
              'label'=>'第三阶段倍率',
            )),
            Html::activeTextField($model, 'third_stage_ratio'),
            $form->error($model, 'third_stage_ratio', array('class'=>'text-danger'))
          );?>
          <div class="clearfix"></div>
          <?php echo Html::formGroup(
            $model, 'date', array(
              'class'=>'col-lg-3 col-md-6',
            ),
            $form->labelEx($model, 'date', array(
              'label'=>'日期',
            )),
            Yii::app()->user->checkRole(User::ROLE_ADMINISTRATOR) ? $form->labelEx($model, 'tba', array(
              'label'=>$form->checkBox($model, 'tba') . '待定',
            )) : '',
            Html::activeTextField($model, 'date', array(
              'class'=>'datetime-picker',
              'data-date-format'=>'yyyy-mm-dd',
              'data-min-view'=>'2',
            )),
            $form->error($model, 'date', array('class'=>'text-danger'))
          );?>
          <?php echo Html::formGroup(
            $model, 'end_date', array(
              'class'=>'col-lg-3 col-md-6',
            ),
            $form->labelEx($model, 'end_date', array(
              'label'=>'结束日期',
            )),
            Html::activeTextField($model, 'end_date', array(
              'class'=>'datetime-picker',
              'data-date-format'=>'yyyy-mm-dd',
              'data-min-view'=>'2',
            )),
            $form->error($model, 'end_date', array('class'=>'text-danger'))
          );?>
          <div class="clearfix hidden-lg"></div>
          <?php echo Html::formGroup(
            $model, 'reg_start', array(
              'class'=>'col-lg-3 col-md-6',
            ),
            $form->labelEx($model, 'reg_start'),
            Html::activeTextField($model, 'reg_start', array(
              'class'=>'datetime-picker',
              'data-date-format'=>'yyyy-mm-dd hh:ii:00',
              'placeholder'=>'留空默认公示后即开放报名',
            )),
            $form->error($model, 'reg_start', array('class'=>'text-danger'))
          );?>
          <?php echo Html::formGroup(
            $model, 'reg_end', array(
              'class'=>'col-lg-3 col-md-6',
            ),
            $form->labelEx($model, 'reg_end'),
            Html::activeTextField($model, 'reg_end', array(
              'class'=>'datetime-picker',
              'data-date-format'=>'yyyy-mm-dd hh:ii:00',
            )),
            $form->error($model, 'reg_end', array('class'=>'text-danger'))
          );?>
          <div class="clearfix"></div>
          <?php if ($model->isAccepted()): ?>
          <?php echo Html::formGroup(
            $model, 'refund_type', array(
              'class'=>'col-lg-3 col-md-6',
            ),
            $form->labelEx($model, 'refund_type', array(
              'label'=>'退赛退费比例',
            )),
            $form->dropDownList($model, 'refund_type', Competition::getRefundTypes(), array(
              'class'=>'form-control',
            )),
            $form->error($model, 'refund_type', array('class'=>'text-danger'))
          );?>
          <?php echo Html::formGroup(
            $model, 'cancellation_end_time', array(
              'class'=>'col-lg-3 col-md-6',
            ),
            $form->labelEx($model, 'cancellation_end_time', [
              'label'=>'退赛截止时间',
            ]),
            Html::activeTextField($model, 'cancellation_end_time', array(
              'class'=>'datetime-picker',
              'data-date-format'=>'yyyy-mm-dd hh:ii:00',
              'placeholder'=>'请务必早于报名结束时间至少一天',
            )),
            $form->error($model, 'cancellation_end_time', array('class'=>'text-danger'))
          );?>
          <div class="clearfix hidden-lg"></div>
          <?php echo Html::formGroup(
            $model, 'reg_reopen_time', array(
              'class'=>'col-lg-3 col-md-6',
            ),
            $form->labelEx($model, 'reg_reopen_time', [
              'label'=>'补报开始时间',
            ]),
            Html::activeTextField($model, 'reg_reopen_time', array(
              'class'=>'datetime-picker',
              'data-date-format'=>'yyyy-mm-dd hh:ii:00',
              'placeholder'=>'请务必早于报名结束时间至少半天',
            )),
            $form->error($model, 'reg_reopen_time', array('class'=>'text-danger'))
          );?>
          <?php endif; ?>
          <?php if ($model->has_qualifying_time): ?>
          <?php echo Html::formGroup(
            $model, 'qualifying_end_time', array(
              'class'=>'col-lg-3 col-md-6',
            ),
            $form->labelEx($model, 'qualifying_end_time'),
            Html::activeTextField($model, 'qualifying_end_time', array(
              'class'=>'datetime-picker',
              'data-date-format'=>'yyyy-mm-dd hh:ii:00',
            )),
            $form->error($model, 'qualifying_end_time', array('class'=>'text-danger'))
          );?>
          <div class="clearfix"></div>
          <?php endif; ?>
          <div class="clearfix"></div>
          <?php
          if ($model->isOld()) {
            echo Html::formGroup(
              $model, 'organizers', array(
                'class'=>'col-lg-12',
              ),
              $form->labelEx($model, 'oldOrganizer', array(
                'label'=>'主办方',
              )),
              Html::activeTextField($model, 'oldOrganizerZh'),
              $form->error($model, 'oldOrganizerZh', array('class'=>'text-danger')),
              Html::activeTextField($model, 'oldOrganizer'),
              $form->error($model, 'oldOrganizer', array('class'=>'text-danger'))
            );
          } elseif ($model->isAccepted() || $this->user->isAdministrator()) {
            echo Html::formGroup(
              $model, 'organizers', array(
                'class'=>'col-lg-12',
              ),
              $form->labelEx($model, 'organizers', array(
                'label'=>'主办方',
              )),
              $form->checkBoxList($model, 'organizers', CHtml::listData($organizers, 'id', 'name_zh'), array(
                'uncheckValue'=>'',
                'container'=>'div',
                'separator'=>'',
                'class'=>'form-control organizer',
                'labelOptions'=>array(
                  'class'=>'checkbox-inline hidden',
                ),
                'template'=>'{beginLabel}{input}{labelTitle}{endLabel}',
              )),
              CHtml::textField('', '', array(
                'class'=>'form-control tokenfield',
                'placeholder'=>'输入名字或拼音',
              )),
              $form->error($model, 'organizers', array('class'=>'text-danger'))
            );
          } ?>
          <?php echo Html::formGroup(
            $model, 'locations', array(
              'class'=>'col-lg-12',
            ),
            $this->widget('MultiLocations', array(
              'model'=>$model,
              'cities'=>$cities,
            ), true),
            $form->error($model, 'locations', array('class'=>'text-danger'))
          );?>
          <?php if ($model->isAccepted() || $this->user->isAdministrator()): ?>
          <div class="clearfix"></div>
          <?php $this->renderPartial('editorTips'); ?>
          <?php echo Html::formGroup(
            $model, 'information_zh', array(
              'class'=>'col-lg-12',
            ),
            $form->labelEx($model, 'information_zh', array(
              'label'=>'比赛信息',
            )),
            $form->textArea($model, 'information_zh', array(
              'class'=>'editor form-control'
            )),
            $form->error($model, 'information_zh', array('class'=>'text-danger'))
          );?>
          <?php endif; ?>
          <div class="clearfix"></div>
          <div class="col-lg-12">
            <button type="submit" class="btn btn-default btn-square"><?php echo Yii::t('common', 'Submit'); ?></button>
          </div>
          <?php $this->endWidget(); ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
$this->widget('Editor');
Yii::app()->clientScript->registerPackage('datetimepicker');
Yii::app()->clientScript->registerPackage('tokenfield');
$allCities = json_encode($cities);
$tokens = json_encode(array_map(function($organizer) {
  return array(
    'value'=>$organizer->user->id . '-' . $organizer->user->name_zh,
    'label'=>$organizer->user->name_zh,
  );
}, $model->organizer));
$datum = json_encode(array_map(function($user) {
  return array(
    'full'=>$user->getCompetitionName() . ' ' . $user->id,
    'value'=>$user->id . '-' . $user->name_zh,
    'label'=>$user->name_zh,
  );
}, $organizers));
$organizerNames = json_encode(CHtml::listData($organizers, 'id', 'name_zh'));
Yii::app()->clientScript->registerScript('competition',
<<<EOT
  $('[data-toggle="tooltip"]').tooltip();
  $('.datetime-picker').on('mousedown touchstart', function() {
    $(this).datetimepicker({
      autoclose: true
    });
  });
  var allCities = {$allCities};
  $(document).on('change', '.province', function() {
    var city = $(this).parents('.location').find('.city'),
      cities = allCities[$(this).val()] || [];
    city.empty();
    $('<option value="">').appendTo(city);
    $.each(cities, function(id, name) {
      $('<option>').val(id).text(name).appendTo(city);
    });
    if (city.find('option').length == 2) {
      city.find('option:last').prop('selected', true);
    }
  }).on('change', '#Competition_type', function() {
    var type = $(this).val();
    if (type === 'WCA') {
      $('#wca_delegates').show();
      $('#cca_delegates').hide().find('input').prop('checked', false);
    } else {
      $('#cca_delegates').show();
      $('#wca_delegates').hide().find('input').prop('checked', false);
    }
  }).on('keydown', '.token-input', function(e) {
    if (e.which == 13) {
      e.preventDefault();
    }
  }).on('changeDate', '#Competition_date', function() {
    var date = $(this).datetimepicker('getDate');
    $('#Competition_end_date').datetimepicker('setStartDate', date);
    date.setDate(date.getDate() - 1);
    date.setHours(23);
    date.setMinutes(59);
    $('#Competition_reg_start, #Competition_qualifying_end_time').datetimepicker('setEndDate', date);
    $('#Competition_reg_end').datetimepicker('setEndDate', date);
  }).on('changeDate', '#Competition_reg_start', function() {
    var date = $(this).datetimepicker('getDate');
    $('#Competition_second_stage_date, #Competition_qualifying_end_time, #Competition_cancellation_end_time').datetimepicker('setStartDate', new Date(+date + 86400000 * 7));
    $('#Competition_third_stage_date').datetimepicker('setStartDate', new Date(+date + 1000));
  }).on('changeDate', '#Competition_reg_end', function() {
    var date = $(this).datetimepicker('getDate');
    $('#Competition_second_stage_date').datetimepicker('setEndDate', new Date(+date - 1000));
    $('#Competition_cancellation_end_time').datetimepicker('setEndDate', new Date(+date - 86400000));
    $('#Competition_reg_reopen_time').datetimepicker('setEndDate', new Date(+date - 43200000));
  }).on('changeDate', '#Competition_second_stage_date', function() {
    var date = $(this).datetimepicker('getDate');
    $('#Competition_third_stage_date').datetimepicker('setStartDate', new Date(+date + 1000));
  }).on('changeDate', '#Competition_third_stage_date', function() {
    var date = $(this).datetimepicker('getDate');
    $('#Competition_second_stage_date').datetimepicker('setEndDate', new Date(+date - 1000));
  }).on('changeDate', '#Competition_cancellation_end_time', function() {
    var date = $(this).datetimepicker('getDate');
    $('#Competition_reg_reopen_time').datetimepicker('setStartDate', new Date(+date + 43200000));
  });
  $('#Competition_date').trigger('changeDate');
  $('#Competition_reg_start').trigger('changeDate');
  $('#Competition_reg_end').trigger('changeDate');
  $('#Competition_cancellation_end_time').trigger('changeDate');
  $('#Competition_type').trigger('change');
  var organizers = {$organizerNames};
  var engine = new Bloodhound({
    local: {$datum},
    datumTokenizer: function(d) {
      return d.full.split('');
    },
    queryTokenizer: function(d) {
      return d.split('');
    }
  });
  engine.initialize();
  $('.tokenfield').tokenfield({
    tokens: {$tokens},
    typeahead: [
      null,
      {
        source: engine.ttAdapter()
      }
    ]
  }).on('tokenfield:createtoken', function(e) {
    var id = e.attrs.value.split('-')[0];
    if (!organizers[id] || organizers[id] != e.attrs.value.split('-')[1]) {
      e.preventDefault();
    }
    //防止重复的
    $.each($(this).tokenfield('getTokens'), function(index, token) {
      if (token.value === e.attrs.value) {
        e.preventDefault();
        return false;
      }
    });
    if (e.attrs.value == e.attrs.label) {
      e.attrs.label = e.attrs.value.split('-')[1];
    }
  }).on('tokenfield:createdtoken', function(e) {
    $('input.organizer[value="' + e.attrs.value.split('-')[0] + '"]').prop('checked', true);
  }).on('tokenfield:removedtoken', function(e) {
    $('input.organizer[value="' + e.attrs.value.split('-')[0] + '"]').prop('checked', false);
  }).on('tokenfield:edittoken', function(e) {
    e.preventDefault();
  });
EOT
);
if (!$model->isAccepted()) {
  $date = strtotime('+14 days') * 1000;
  Yii::app()->clientScript->registerScript('competition-date',
<<<EOT
  $('#Competition_date').datetimepicker('setStartDate', new Date({$date}));
EOT
);
}
