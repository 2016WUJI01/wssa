<div class="col-lg-12">
  <div>
    <p><?php echo Yii::t('statistics', 'Global personal rankings in each official event are listed, based on the {url}.', array(
      '{url}'=>CHtml::link(Yii::t('statistics', 'official WCA rankings'), 'https://www.worldcubeassociation.org/results/events.php?regionId=China', array('target'=>'_blank')),
    )); ?></p>
  </div>
  <?php $form = $this->beginWidget('ActiveForm', array(
    'htmlOptions'=>array(
      'role'=>'form',
      'class'=>'form-inline',
    ),
    'method'=>'get',
    'action'=>array('/results/rankings'),
  )); ?>
    <div class="form-group">
      <label for="region"><?php echo Yii::t('common', 'Region'); ?></label>
      <?php echo CHtml::dropDownList('region', $region, Region::getWCARegions(), array(
        'class'=>'form-control',
      )); ?>
    </div>
    <div class="form-group">
      <label for="event"><?php echo Yii::t('common', 'Event'); ?></label>
      <?php echo CHtml::dropDownList('event', $event, Events::getNormalTranslatedEvents(), array(
        'class'=>'form-control',
      )); ?>
    </div>
    <div class="form-group">
      <label for="gender"><?php echo Yii::t('common', 'Gender'); ?></label>
      <?php echo CHtml::dropDownList('gender', $gender, Persons::getGenders(), array(
        'class'=>'form-control',
      )); ?>
    </div>
    <?php foreach (Results::getRankingTypes() as $_type): ?>
    <?php echo CHtml::tag('button', array(
      'type'=>'submit',
      'name'=>'type',
      'value'=>$_type,
      'class'=>'btn btn-' . ($type == $_type ? 'warning' : 'theme'),
    ), Yii::t('common', ucfirst($_type))); ?>
    <?php endforeach; ?>
  <?php $this->endWidget(); ?>
  <div class="event-title">
    <?php echo Events::getFullEventNameWithIcon($event); ?>
    &nbsp;&nbsp;
    <?php echo Yii::t('Region', $region); ?>
    &nbsp;&nbsp;
    <?php echo Yii::t('common', ucfirst($gender)); ?>
    &nbsp;&nbsp;
    <?php echo Yii::t('common', ucfirst($type)); ?>
  </div>
  <?php
  $columns = array(
    array(
      'headerHtmlOptions'=>array(
        'class'=>'battle-checkbox',
      ),
      'header'=>Yii::t('common', 'Battle'),
      'value'=>'Persons::getBattleCheckBox($data["personName"], $data["personId"])',
      'type'=>'raw',
    ),
    array(
      'class'=>'RankColumn',
      'header'=>Yii::t('statistics', 'Rank'),
      'value'=>'$displayRank',
    ),
    array(
      'header'=>Yii::t('statistics', 'Person'),
      'value'=>'Persons::getLinkByNameNId($data["personName"], $data["personId"])',
      'type'=>'raw',
    ),
    array(
      'header'=>Yii::t('common', 'Region'),
      'value'=>'Region::getIconName($data["countryName"], $data["iso2"])',
      'type'=>'raw',
      'htmlOptions'=>array('class'=>'region'),
    ),
    array(
      'header'=>Yii::t('common', 'Result'),
      'value'=>'Results::formatTime($data["best"], $data["eventId"])',
      'type'=>'raw',
    ),
    array(
      'header'=>Yii::t('common', 'Competition'),
      'value'=>'CHtml::link(ActiveRecord::getModelAttributeValue($data, "name"), $data["url"])',
      'type'=>'raw',
    ),
    array(
      'header'=>Yii::t('Competition', 'Date'),
      'value'=>'date("Y-m-d", strtotime(sprintf("%s-%s-%s", $data["year"], $data["month"], $data["day"])))',
      'type'=>'raw',
    ),
  );
  if ($type === 'average') {
    $columns[] = array(
      'header'=>Yii::t('common', 'Detail'),
      'value'=>'Results::getDisplayDetail($data)',
      'type'=>'raw',
    );
  }
  $this->widget('RankGridView', array(
    'dataProvider'=>new NonSortArrayDataProvider($rankings['rows'], array(
      'pagination'=>array(
        'pageSize'=>100,
        'pageVar'=>'page',
      ),
      'sliceData'=>false,
      'totalItemCount'=>$rankings['count'],
    )),
    'template'=>'{items}{pager}',
    'enableSorting'=>false,
    'front'=>true,
    'rankKey'=>'best',
    'rank'=>$rankings['rank'],
    'count'=>($page - 1) * 100,
    'columns'=>$columns,
  )); ?>
</div>