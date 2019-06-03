<?php
class UserController extends AdminController {

	public function accessRules() {
		return array(
			array(
				'allow',
				'roles'=>array(
					'role'=>User::ROLE_ADMINISTRATOR,
				),
			),
			array(
				'allow',
				'roles'=>array(
					'role'=>User::ROLE_ORGANIZER,
				),
				'actions'=>array('statistics'),
			),
			array(
				'deny',
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex() {
		$model = new User('search');
		$model->unsetAttributes();
		$model->status = array(
			User::STATUS_NORMAL,
			User::STATUS_BANNED,
		);
		$model->attributes = $this->aRequest('User');
		$this->render('index', array(
			'model'=>$model,
		));
	}

	public function actionRepeat() {
		$model = new User('search');
		$model->unsetAttributes();
		$model->attributes = $this->aRequest('User');
		$this->render('repeat', array(
			'model'=>$model,
		));
	}

	public function actionMerge() {
		if (isset($_POST['users'])) {
			$user1 = User::model()->findByPk($_POST['users'][0]);
			$user2 = User::model()->findByPk($_POST['users'][1]);
			if ($user1 === null || $user2 === null) {
				$this->ajaxError(404, 'Unnknown user ID');
			}
			if ($user1->getCompetitionName() != $user2->getCompetitionName()) {
				$this->ajaxError(403, 'Users\' names must be the same');
			}
			if ($user1->birthday != $user2->birthday) {
				$this->ajaxError(403, 'Users\' birthdays must be the same');
			}
			if ($user1->gender != $user2->gender) {
				$this->ajaxError(403, 'Users\' genders must be the same');
			}
			if ($user1->id == $user2->id) {
				$this->ajaxError(403, 'Users\' IDs mustn\'t be the same');
			}
			$params = [
				[
					'user_id'=>$user1->id,
				],
				[
					'condition'=>'user_id=' . $user2->id,
				],
			];
			foreach (['Registration', 'LiveResult', 'Pay'] as $modelName) {
				call_user_func_array([$modelName::model(), 'updateAll'], $params);
			}
			$this->ajaxOk(null);
		}
		$this->render('merge');
	}

	public function actionSearch() {
		$query = $this->sRequest('query');
		$criteria = new CDbCriteria();
		if (ctype_digit($query)) {
			$criteria->addSearchCondition('id', $query, false, 'OR', '=');
		} else {
			$criteria->addSearchCondition('name', $query, true, 'OR');
			$criteria->addSearchCondition('name_zh', $query, true, 'OR');
		}
		$criteria->addSearchCondition('email', $query, true, 'OR');
		$criteria->addCondition('status!=2');
		$criteria->order = 'id';
		$criteria->limit = 20;
		$users = User::model()->findAll($criteria);
		echo CJSON::encode(array_map(function($user) {
			return [
				'id'=>$user->id,
				'name'=>$user->name,
				'name_zh'=>$user->name_zh,
				'display_name'=>$user->getCompetitionName(),
				'birthday'=>$user->birthday,
				'display_birthday'=>date('Y-m-d', $user->birthday),
				'gender'=>$user->getGenderText(),
			];
		}, $users));
	}

	public function actionRegistration() {
		$model = new Registration();
		$model->unsetAttributes();
		$model->attributes = $this->aRequest('Registration');
		$this->renderPartial('registration', array(
			'model'=>$model,
		));
	}

	public function actionLoginHistory() {
		$model = new LoginHistory();
		$model->unsetAttributes();
		$model->attributes = $this->aRequest('LoginHistory');
		$this->renderPartial('loginHistory', array(
			'model'=>$model,
		));
	}

	public function actionCode() {
		$model = new InvitationCode('search');
		$model->unsetAttributes();
		$model->attributes = $this->aRequest('InvitationCode');
		$this->render('code', array(
			'model'=>$model,
		));
	}

	public function actionAddCode() {
		$model = new InvitationCode();
		$model->unsetAttributes();
		if (isset($_POST['InvitationCode'])) {
			$model->attributes = $_POST['InvitationCode'];
			if ($model->createCode()) {
				Yii::app()->user->setFlash('success', '新增注册码成功');
				$this->redirect(array('/board/user/code'));
			}
		}
		$this->render('addCode', array(
			'model'=>$model,
		));
	}

	public function actionExportCode() {
		$model = new InvitationCode();
		$model->unsetAttributes();
		if (isset($_POST['InvitationCode'])) {
			$model->attributes = $_POST['InvitationCode'];
			$model->exportCode();
		}
		$this->render('exportCode', array(
			'model'=>$model,
		));
	}

	public function actionDisableCode() {
		$id = $this->iGet('id');
		$model = InvitationCode::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		$model->status = InvitationCode::STATUS_USED;
		$model->save();
		Yii::app()->user->setFlash('success', '停用注册码成功');
		$this->redirect(Yii::app()->request->urlReferrer);
	}

	public function actionEnableCode() {
		$id = $this->iGet('id');
		$model = InvitationCode::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		$model->status = InvitationCode::STATUS_NORMAL;
		$model->save();
		Yii::app()->user->setFlash('success', '启用注册码成功');
		$this->redirect(Yii::app()->request->urlReferrer);
	}

	public function actionStatistics() {
		$totalUser = User::model()->countByAttributes(array(
			'status'=>User::STATUS_NORMAL,
		));
		$advancedUser = User::model()->countByAttributes(array(
			'status'=>User::STATUS_NORMAL,
			'role'=>array(
				User::ROLE_ORGANIZER,
				User::ROLE_ADMINISTRATOR,
			),
		));
		$uncheckedUser = User::model()->countByAttributes(array(
			'status'=>User::STATUS_NORMAL,
			'role'=>array(
				User::ROLE_UNCHECKED,
			),
		));
		$userPerDay = round($totalUser / ceil((time() - strtotime('2014-06-06')) / 86400), 2);
		$totalRegistration = Registration::model()->with('user')->count('user.status=' . User::STATUS_NORMAL);
		$acceptedRegistration = Registration::model()->with('user')->countByAttributes(array(
			'status'=>Registration::STATUS_ACCEPTED,
		), 'user.status=' . User::STATUS_NORMAL);
		$dailyUser = User::getDailyUser();
		$dailyRegistration = Registration::getDailyRegistration();
		$dailyData = $this->mergeDailyData($dailyUser, $dailyRegistration);
		$hourlyUser = User::getHourlyUser();
		$hourlyRegistration = Registration::getHourlyRegistration();
		$hourlyData = $this->mergeHourlyData($hourlyUser, $hourlyRegistration);
		$userRegion = User::getUserRegion();
		$userGender = User::getUserGender();
		$userAge = User::getUserAge();
		$userWca = User::getUserWca();
		$this->render('statistics', array(
			'totalUser'=>$totalUser,
			'advancedUser'=>$advancedUser,
			'uncheckedUser'=>$uncheckedUser,
			'userPerDay'=>$userPerDay,
			'totalRegistration'=>$totalRegistration,
			'acceptedRegistration'=>$acceptedRegistration,
			'dailyData'=>$dailyData,
			'hourlyData'=>$hourlyData,
			'userRegion'=>$userRegion,
			'userGender'=>$userGender,
			'userAge'=>$userAge,
			'userWca'=>$userWca,
		));
	}

	private function mergeDailyData() {
		$data = func_get_args();
		$keys = array();
		$startDay = $now = time();
		foreach ($data as $key=>$value) {
			if ($value === array()) {
				unset($data[$key]);
				continue;
			}
			if (($temp = strtotime($value[0]['day'])) < $startDay) {
				$startDay = $temp;
			}
			$data[$key] = $this->transformData($value, 'day');
		}
		for ($day = $startDay; $day <= $now; $day += 86400) {
			$dailyData[date('Y-m-d', $day)] = array();
		}
		foreach ($data as $value) {
			$template = current($value);
			$keys = array_keys($template);
			$template[$keys[1]] = 0;
			foreach ($dailyData as $day=>$temp) {
				if (isset($value[$day])) {
					$dailyData[$day] = array_merge($dailyData[$day], $value[$day]);
				} else {
					$template['day'] = $day;
					$dailyData[$day] = array_merge($dailyData[$day], $template);
				}
			}
		}
		return array_values($dailyData);

	}

	private function mergeHourlyData() {
		$data = func_get_args();
		$keys = array();
		foreach ($data as $key=>$value) {
			if ($value === array()) {
				unset($data[$key]);
				continue;
			}
			$data[$key] = $this->transformData($value, 'hour');
		}
		$hourlyData = array_fill(0, 24, array());
		foreach ($data as $value) {
			$template = current($value);
			$keys = array_keys($template);
			$template[$keys[1]] = 0;
			foreach ($hourlyData as $hour=>$temp) {
				if (isset($value[$hour])) {
					$hourlyData[$hour] = array_merge($hourlyData[$hour], $value[$hour]);
				} else {
					$template['hour'] = $hour;
					$hourlyData[$hour] = array_merge($hourlyData[$hour], $template);
				}
			}
		}
		return array_values($hourlyData);
	}

	private function transformData($data, $key) {
		$temp = array();
		foreach ($data as $value) {
			$temp[$value[$key]] = $value;
		}
		return $temp;
	}

	public function actionEdit() {
		$id = $this->iGet('id');
		$model = User::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		if (isset($_POST['User'])) {
			$model->attributes = $_POST['User'];
			$model->handleDate();
			if ($model->save()) {
				$model->updateIdentity();
				Yii::app()->user->setFlash('success', '编辑用户成功');
				$this->redirect($this->getReferrer());
			}
		}
		$model->formatDate();
		$roles = User::getRoles();
		$identities = UserIdentity::getAllIdentities();
		$genders = User::getGenders();
		$cities = Region::getAllCities();
		$this->render('edit', array(
			'model'=>$model,
			'roles'=>$roles,
			'identities'=>$identities,
			'genders'=>$genders,
			'cities'=>$cities,
		));
	}

	public function actionDisable() {
		$id = $this->iGet('id');
		$model = User::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		$model->status = User::STATUS_BANNED;
		$model->save();
		Yii::app()->user->setFlash('success', '拉黑用户成功');
		$this->redirect(Yii::app()->request->urlReferrer);
	}

	public function actionEnable() {
		$id = $this->iGet('id');
		$model = User::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		$model->status = User::STATUS_NORMAL;
		$model->save();
		Yii::app()->user->setFlash('success', '洗白用户成功');
		$this->redirect(Yii::app()->request->urlReferrer);
	}

	public function actionDelete() {
		$id = $this->iGet('id');
		$model = User::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		$model->status = User::STATUS_DELETED;
		$model->save();
		Yii::app()->user->setFlash('success', '删除用户成功');
		$this->redirect(Yii::app()->request->urlReferrer);
	}
}
