<?php

class SchedulesForm extends Widget {
	public $model;
	public $name = 'events';
	public $htmlOptions = [];
	public $tableOptions = [];
	public function run() {
		$htmlOptions = $this->htmlOptions;
		$tableOptions = $this->tableOptions;
		$model = $this->model;
		$name = $this->name;
		if (!isset($htmlOptions['class'])) {
			$htmlOptions['class'] = 'table-responsive';
		} else {
			$htmlOptions['class'] .= ' table-responsive';
		}
		$htmlOptions['id'] = 'schedules';
		if (!isset($tableOptions['class'])) {
			$tableOptions['class'] = 'table table-condensed';
		} else {
			$tableOptions['class'] .= ' table table-condensed';
		}
		echo CHtml::openTag('div', $htmlOptions);
		echo CHtml::openTag('table', $tableOptions);
		echo CHtml::openTag('thead');
		echo CHtml::openTag('tr');
		echo CHtml::tag('th', [], '第几天');
		echo CHtml::tag('th', [], '开始时间');
		echo CHtml::tag('th', [], '结束时间');
		echo CHtml::tag('th', [], '项目');
		echo CHtml::tag('th', [], '轮次');
		echo CHtml::tag('th', [], '人数');
		echo CHtml::closeTag('tr');
		echo CHtml::closeTag('thead');

		$schedules = $model->$name;
		$schedules[] = Schedule::model()->attributes;
		$events = Events::getScheduleEvents();
		foreach ($events as $key=>$value) {
			$events[$key] = Yii::t('event', $value);
		}
		$rounds = Competition::getRounds();
		$stages = Schedule::getStages();
		echo CHtml::openTag('tbody');
		// CVarDumper::dump($schedules, 10, 1);exit;
		foreach ($schedules as $key=>$value) {
			extract($value);
			echo CHtml::openTag('tr');
			echo CHtml::tag('td', [], CHtml::activeNumberField($model, "{$name}[day][$key]", [
				'value'=>$day ?: 1,
				'min'=>1,
				'max'=>4,
			]));
			echo CHtml::tag('td', [], CHtml::activeTextField($model, "{$name}[start_time][$key]", [
				'value'=>$start_time ? date('H:i', $start_time) : '',
				'class'=>'datetime-picker',
				'data-date-format'=>'hh:ii',
				'data-max-view'=>'1',
				'data-start-view'=>'1',
			]));
			echo CHtml::tag('td', [], CHtml::activeTextField($model, "{$name}[end_time][$key]", [
				'value'=>$end_time ? date('H:i', $end_time) : '',
				'class'=>'datetime-picker',
				'data-date-format'=>'hh:ii',
				'data-max-view'=>'1',
				'data-start-view'=>'1',
			]));
			echo CHtml::tag('td', [], CHtml::dropDownList(CHtml::activeName($model, "{$name}[event][$key]"), $event, $events, [
				'prompt'=>'',
				'class'=>'schedule-event',
			]));
			echo CHtml::tag('td', [], CHtml::dropDownList(CHtml::activeName($model, "{$name}[round][$key]"), $round, $rounds, [
				'prompt'=>'',
				'class'=>'schedule-round',
			]));
			echo CHtml::tag('td', [], CHtml::activeNumberField($model, "{$name}[number][$key]", [
				'value'=>$number,
			]));
			if ($model->hasErrors("{$name}.{$key}")) {
				echo CHtml::tag('tr', [
					'class'=>'danger',
				], CHtml::tag('td', [
					'colspan'=>11,
				], $model->getError("{$name}.{$key}")));
			}
			echo CHtml::closeTag('tr');
		}
		echo CHtml::closeTag('tbody');

		echo CHtml::closeTag('table');
		echo CHtml::closeTag('div');
		$onlyScheculeEvents = json_encode(Events::getOnlyScheduleEvents());
		Yii::app()->clientScript->registerScript('SchedulesForm',
<<<EOT
  var onlyScheculeEvents = {$onlyScheculeEvents};
  var combinedRoundTypes = ['c', 'd', 'e', 'g'];
  var length = $('#schedules table tbody tr').length;
  $(document).on('focus', '#schedules table tbody tr:last-child', function() {
    var last = $(this).clone().insertAfter(this);
    last.find('input, select').each(function() {
      var name = this.name;
      $(this).attr('name', name.replace(/\[\d*\]/, '[' + length + ']'));
    });
    length++;
    last.find('.datetime-picker').datetimepicker({
      autoclose: true
    });
  }).on('change', '.schedule-event', function(e) {
    var that = $(this);
    var event = that.val();
    if (onlyScheculeEvents[event] !== undefined) {
      that.parent().nextAll().find('select, input').prop('disabled', true);
    } else {
      that.parent().nextAll().find('select, input').prop('disabled', false);
    }
  }).on('change', '.schedule-round', function(e) {
    var that = $(this);
    var round = that.val();
    var format = that.parent().next().find('option');
    var cutoff = that.parent().next().next().find('input');
    format.prop('disabled', false);
    if (combinedRoundTypes.indexOf(round) > -1) {
      cutoff.prop('disabled', false);
      format.filter(':not([value="2/a"]):not([value="1/m"])').prop('disabled', true);
    } else {
      cutoff.prop('disabled', true);
      format.filter('[value="2/a"], [value="1/m"]').prop('disabled', true);
    }
  });
  $('.schedule-event, .schedule-round').change();
EOT
		);
	}
}
