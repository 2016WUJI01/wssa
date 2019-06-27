<?php
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;

/**
 * This is the model class for table 'pay'.
 *
 * The followings are the available columns in table 'pay':
 * @property string $id
 * @property string $user_id
 * @property string $channel
 * @property integer $type
 * @property string $type_id
 * @property string $sub_type_id
 * @property string $order_no
 * @property string $order_name
 * @property string $amount
 * @property integer $device_type
 * @property string $pay_channel
 * @property string $pay_account
 * @property string $trade_no
 * @property integer $status
 * @property string $create_time
 * @property string $update_time
 */
class Pay extends ActiveRecord {
	const CHANNEL_NOWPAY = 'nowPay';
	const CHANNEL_ALIPAY = 'alipay';

	const TYPE_REGISTRATION = 0;

	const STATUS_UNPAID = 0;
	const STATUS_PAID = 1;
	const STATUS_FAILED = 2;
	const STATUS_WAIT_SEND = 3;
	const STATUS_WAIT_CONFIRM = 4;
	const STATUS_WAIT_PAY = 5;

	const DEVICE_TYPE_PC = '02';
	const DEVICE_TYPE_MOBILE = '06';

	//支付宝
	const ALIPAY_TRADE_STATUS_FINISHED = 'TRADE_FINISHED';
	const ALIPAY_TRADE_STATUS_CLOSED = 'TRADE_CLOSED';
	const ALIPAY_TRADE_SUCCESS = 'TRADE_SUCCESS';
	const ALIPAY_SUCCESS = 'T';

	const CHARSET = 'UTF-8';
	const SIGN_TYPE = 'MD5';
	const SIGN_TYPE_RSA2 = 'RSA2';

	private static $_criteria;

	public static function notifyReturn($channel, $success) {
		switch ($channel) {
			case self::CHANNEL_NOWPAY:
				return $success ? 'success=Y' : 'success=N';
			default:
				return $success ? 'success' : 'fail';
		}
	}

	public static function getPayByOrderId($orderId) {
		return self::model()->findByAttributes(array(
			'order_no'=>$orderId,
		));
	}

	public static function getAllStatus() {
		return array(
			self::STATUS_UNPAID=>Yii::t('common', 'Unpaid'),
			self::STATUS_PAID=>Yii::t('common', 'Paid'),
			self::STATUS_WAIT_SEND=>'待发货',
			self::STATUS_WAIT_CONFIRM=>'待收货',
			self::STATUS_WAIT_PAY=>'待付款',
		);
	}

	public static function getChannels() {
		return array(
			self::CHANNEL_ALIPAY=>'支付宝',
		);
	}

	public static function getTypes() {
		return array(
			self::TYPE_REGISTRATION=>Yii::t('common', 'Registration'),
		);
	}

	public function refund($amount) {
		if ($this->refund_time > 0 || $amount <= 0) {
			return false;
		}
		if ($amount >= $this->paid_amount) {
			$amount = $this->paid_amount;
		}
		$app = Yii::app();
		$alipay = $app->params->payments['alipay'];
		$baseUrl = $app->request->getBaseUrl(true);
		$language = $app->language;
		$app->language = 'zh_cn';
		$commonParams = [
			'app_id'=>trim($alipay['app_id']),
			'method'=>'alipay.trade.refund',
			'timestamp'=>date('Y-m-d H:i:s'),
			'version'=>'1.0',
			'charset'=>self::CHARSET,
		];
		$bizParams = array(
			'out_trade_no'=>$this->order_no,
			'refund_amount'=>number_format($amount / 100, 2, '.', ''),
			// 'refund_reason'=>'退赛-' . $this->order_name,
			//暂时没有多次退款的case，统一使用-1
			'out_request_no'=>$this->order_no . '-1',
		);
		$params = $this->generateAlipaySign($commonParams, $bizParams, $alipay['private_key_path']);
		Yii::log(json_encode([
			'amount'=>$amount,
			'params'=>$params,
		]), 'pay', 'refund.params');
		$response = $this->request($alipay['gateway'], $params);
		if ($response === false) {
			return false;
		}
		$sign = $response['sign'];
		$params = $response['alipay_trade_refund_response'];
		if ($this->validateAlipaySign(json_encode($params), $sign, $alipay['alipay_public_key_path'], [
				'sign',
				'code',
				'msg',
			])) {
			$this->refund_amount = $params['refund_fee'] * 100;
			$this->refund_time = strtotime($params['gmt_refund_pay']);
			$this->save();
			return true;
		}
		return false;
	}

	public function request($gateway, $params) {
		$client = new Client();
		try {
			$response = $client->post($gateway, [
				'form_params'=>$params,
			]);
			if ($response->getStatusCode() != 200) {
				return false;
			}
			$body = $response->getBody();
			Yii::log($body, 'pay', 'response');
			$body = json_decode($body, true);
			if (!isset($body['alipay_trade_refund_response'])) {
				return false;
			}
			return $body;
		} catch (Exception $e) {
			$log = [
				'code'=>$e->getCode(),
				'message'=>$e->getMessage(),
			];
			if (method_exists($e, 'getRequest')) {
				$log['request'] = Psr7\str($e->getRequest());
				if ($e->hasResponse()) {
					$log['response'] = Psr7\str($e->getResponse());
				}
			}
			Yii::log(json_encode($log), 'pay', 'request');
			return false;
		}

	}

	public function validateNotify($channel, $params) {
		switch ($channel) {
			default:
				return $this->validateAlipayNotify($params);
		}
	}

	public function validateAlipayNotify($params) {
		$app = Yii::app();
		$alipay = $app->params->payments['alipay'];
		$tradeStatus = isset($params['trade_status']) ? $params['trade_status'] : '';
		$buyerEmail = isset($params['buyer_email']) ? $params['buyer_email'] : '';
		$tradeNo = isset($params['trade_no']) ? $params['trade_no'] : '';
		$paidAmount = isset($params['total_amount']) ? $params['total_amount'] * 100 : 0;
		$result = $this->validateAlipaySign($params, $params['sign'], $alipay['alipay_public_key_path']);
		if ($result) {
			$this->trade_no = $tradeNo;
			$this->pay_account = $buyerEmail;
			$this->channel = 'alipay';
			$status = self::STATUS_UNPAID;
			switch ($tradeStatus) {
				case self::ALIPAY_TRADE_SUCCESS:
				case self::ALIPAY_TRADE_STATUS_FINISHED:
					$status = self::STATUS_PAID;
					break;
				default:
					return $result;
			}
			$this->updateStatus($status, $paidAmount);
		}
		return $result;
	}

	public function validateAlipaySign($params, $sign, $publicKeyPath, $excludeAttributes = ['sign', 'sign_type']) {
		if (is_array($params)) {
			$temp = $params;
			foreach ($excludeAttributes as $attribute) {
				unset($temp[$attribute]);
			}
			ksort($temp);
			$str = array();
			foreach ($temp as $key=>$value) {
				$str[] = "$key=$value";
			}
			$data = implode('&', $str);
		} else {
			$data = $params;
		}
		$publicKeyContent = file_get_contents($publicKeyPath);
		$res = openssl_get_publickey($publicKeyContent);
		$result = openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_SHA256);
		openssl_free_key($res);
		return !!$result;
	}

	public function updateStatus($status = self::STATUS_PAID, $paidAmount = 0) {
		if (!$this->isPaid()) {
			if ($this->paid_time == 0) {
				$this->paid_time = time();
				$this->paid_amount = $paidAmount;
			}
			$this->status = $status;
		}
		$this->update_time = time();
		$this->save(false);
		if ($this->status == self::STATUS_WAIT_PAY || $this->status == self::STATUS_UNPAID) {
			return;
		}
		switch ($this->type) {
			case self::TYPE_REGISTRATION:
				$registration = $this->registration;
				if ($registration !== null && !$registration->isAccepted()) {
					$registration->paid = Registration::PAID;
					$registration->total_fee = $registration->getTotalFee(true);
					$registration->guest_paid = $registration->has_entourage;
					$registration->accept();
				}
				break;
		}
	}

	public function generateParams($isMobile) {
		return $this->generateAlipayParams($isMobile);
	}

	public function generateAlipayParams($isMobile) {
		$app = Yii::app();
		$alipay = $app->params->payments['alipay'];
		$baseUrl = $app->request->getBaseUrl(true);
		$language = $app->language;
		$app->language = 'zh_cn';
		$commonParams = [
			'app_id'=>trim($alipay['app_id']),
			'method'=>$isMobile ? 'alipay.trade.wap.pay' : 'alipay.trade.page.pay',
			'timestamp'=>date('Y-m-d H:i:s'),
			'version'=>'1.0',
			'charset'=>self::CHARSET,
			'return_url'=>$baseUrl . $app->createUrl('/pay/frontNotify', array('channel'=>self::CHANNEL_ALIPAY)),
			'notify_url'=>$baseUrl . $app->createUrl('/pay/notify', array('channel'=>self::CHANNEL_ALIPAY)),
		];
		$bizParams = array(
			'out_trade_no'=>$this->order_no,
			'product_code'=>'FAST_INSTANT_TRADE_PAY',
			'total_amount'=>number_format($this->amount / 100, 2, '.', ''),
			'subject'=>$this->order_name,
		);
		$params = $this->generateAlipaySign($commonParams, $bizParams, $alipay['private_key_path']);
		return array(
			'action'=>$alipay['gateway'],
			'method'=>'post',
			'params'=>$params,
		);
	}

	public function generateAlipaySign($commonParams, $bizParams, $privateKeyPath, $excludeAttributes = array()) {
		ksort($bizParams);
		$commonParams['biz_content'] = json_encode($bizParams, JSON_UNESCAPED_UNICODE);
		$temp = array_filter($commonParams);
		foreach ($excludeAttributes as $attribute) {
			unset($temp[$attribute]);
		}
		$temp['sign_type'] = self::SIGN_TYPE_RSA2;
		ksort($temp);
		$str = array();
		foreach ($temp as $key=>$value) {
			$str[] = "$key=$value";
		}
		$data = implode('&', $str);
		$privateKey = file_get_contents($privateKeyPath);
		$res = openssl_get_privatekey($privateKey);
		openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256);
		openssl_free_key($res);
		$sign = base64_encode($sign);
		$temp['sign'] = $sign;
		return $temp;
	}

	public function getUrl() {
		$baseUrl = Yii::app()->request->getBaseUrl(true);
		switch ($this->type) {
			case self::TYPE_REGISTRATION:
				return $baseUrl . CHtml::normalizeUrl($this->competition->getUrl());
			default:
				return $baseUrl;
		}
	}

	public function isPaid() {
		return $this->status == self::STATUS_PAID;
	}


	public function amountMismatch() {
		return $this->isPaid() && $this->amount != $this->paid_amount;
	}

	public function getStatusText() {
		$status = self::getAllStatus();
		return isset($status[$this->status]) ? $status[$this->status] : $this->status;
	}

	public function getTypeText() {
		$types = self::getTypes();
		return isset($types[$this->type]) ? $types[$this->type] : $this->type;
	}

	public function getColumns() {
		$criteria = clone self::$_criteria;
		$criteria->select = 'SUM(amount) AS amount';
		$amount = $this->find($criteria)->amount;
		$criteria = clone self::$_criteria;
		$criteria->select = 'SUM(paid_amount) AS paid_amount';
		$paidAmount = $this->find($criteria)->paid_amount;
		$criteria = clone self::$_criteria;
		$criteria->select = 'SUM(refund_amount) AS refund_amount';
		$refundAmount = $this->find($criteria)->refund_amount;
		$criteria->select = 'SUM(ROUND((CASE
			WHEN status=0 OR status=5 THEN 0
			WHEN channel="nowPay" AND device_type="02" THEN paid_amount*0.02
			WHEN channel="nowPay" THEN paid_amount*0.06
			ELSE (paid_amount)*0.012 END) / 100, 2)) AS paid_amount';
		$fee = $this->find($criteria)->paid_amount;
		$columns = array(
			array(
				'name'=>'id',
				'filter'=>false,
			),
			array(
				'name'=>'user_id',
				'value'=>'$data->user->getCompetitionName()',
				'filter'=>false,
			),
			array(
				'header'=>'订单金额',
				'value'=>'number_format($data->amount / 100, 2)',
				'footer'=>number_format($amount / 100, 2),
			),
			array(
				'header'=>'支付金额',
				'value'=>'number_format($data->paid_amount / 100, 2)',
				'footer'=>number_format($paidAmount / 100, 2),
			),
			array(
				'header'=>'退款金额',
				'value'=>'number_format($data->refund_amount / 100, 2)',
				'footer'=>number_format($refundAmount / 100, 2),
			),
			array(
				'name'=>'fee',
				'footer'=>$fee,
				'filter'=>false,
				'header'=>'手续费',
			),
			array(
				'name'=>'create_time',
				'type'=>'raw',
				'value'=>'date("Y-m-d H:i:s", $data->create_time)',
				'filter'=>false,
			),
			array(
				'name'=>'paid_time',
				'type'=>'raw',
				'value'=>'$data->paid_time > 0 ? date("Y-m-d H:i:s", $data->paid_time) : "-"',
				'filter'=>false,
			),
			array(
				'name'=>'status',
				'type'=>'raw',
				'value'=>'$data->getStatusText()',
				'filter'=>Pay::getAllStatus(),
			),
			array(
				'name'=>'type',
				'value'=>'$data->getTypeText()',
				'filter'=>Pay::getTypes(),
			),
			array(
				'name'=>'channel',
				'value'=>'$data->channel',
				'filter'=>Pay::getChannels(),
			),
		);
		if ($this->type !== '') {
			switch ($this->type) {
				case self::TYPE_REGISTRATION:
					array_splice($columns, 1, 0, array(
						array(
							'name'=>'type_id',
							'header'=>Yii::t('common', 'Competition'),
							'value'=>'$data->competition->name_zh',
							'filter'=>Competition::getRegistrationCompetitions(),
						),
					));
					break;
			}
		}
		return $columns;
	}

	public function getFee() {
		if ($this->status != self::STATUS_UNPAID && $this->status != self::STATUS_WAIT_PAY) {
			switch ($this->channel) {
				case self::CHANNEL_NOWPAY:
					if ($this->device_type == self::DEVICE_TYPE_PC) {
						return number_format(max($this->paid_amount * 0.0002, 0.08), 2, '.', '');
					} else {
						return number_format(max($this->paid_amount * 0.0006, 0.08), 2, '.', '');
					}
				default:
					return number_format(($this->paid_amount) * 0.00012, 2, '.', '');
			}
		} else {
			return '0.00';
		}
	}

	public function getBillFee() {
		if ($this->status != self::STATUS_UNPAID && $this->status != self::STATUS_WAIT_PAY) {
			switch ($this->channel) {
				case self::CHANNEL_NOWPAY:
					if ($this->device_type == self::DEVICE_TYPE_PC) {
						return number_format(max($this->paid_amount * 0.0002, 0.08), 2, '.', '');
					} else {
						return number_format(max($this->paid_amount * 0.0006, 0.08), 2, '.', '');
					}
				default:
					return number_format(($this->paid_amount) * 0.00006, 2, '.', '');
			}
		} else {
			return '0.00';
		}
	}

	public function getTotal($status = self::STATUS_PAID, $channel = false, $attribute = 'paid_amount', $compareTime = true) {
		$criteria = new CDbCriteria;
		if ($channel) {
			$criteria->compare('channel', $this->channel);
		}
		$criteria->compare('type', $this->type);
		$criteria->compare('type_id', $this->type_id);
		$criteria->compare('status', $status);
		if ($compareTime) {
			$this->compareTime($criteria);
		}
		$criteria->select = "SUM({$attribute}) AS {$attribute}";
		return number_format($this->find($criteria)->$attribute / 100, 2, '.', '');
	}

	public function getTotalFee() {
		$criteria = new CDbCriteria;
		$criteria->compare('type', $this->type);
		$criteria->compare('type_id', $this->type_id);
		$criteria->select = 'SUM(ROUND((CASE
			WHEN status=0 OR status=5 THEN 0
			WHEN channel="nowPay" AND device_type="02" THEN paid_amount*0.02
			WHEN channel="nowPay" THEN paid_amount*0.06
			ELSE (paid_amount)*0.012 END) / 100, 2)) AS paid_amount';
		return $this->find($criteria)->paid_amount;
	}

	public function getBillTotalFee() {
		$criteria = new CDbCriteria;
		$criteria->compare('channel', $this->channel);
		$this->compareTime($criteria);
		$criteria->select = 'SUM(ROUND((CASE
			WHEN status=0 OR status=5 THEN 0
			WHEN channel="nowPay" AND device_type="02" THEN paid_amount*0.02
			WHEN channel="nowPay" THEN paid_amount*0.06
			ELSE (paid_amount)*0.006 END) / 100, 2)) AS paid_amount';
		return $this->find($criteria)->paid_amount;
	}

	protected function beforeValidate() {
		if ($this->isNewRecord) {
			$this->create_time = $this->update_time = time();
			if (!$this->order_no) {
				$this->order_no = sprintf('%s-%d-%06d-%05d', date('YmdHis', $this->create_time), $this->type, $this->sub_type_id, mt_rand(10000, 99999));
			}
		}
		return parent::beforeValidate();
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'pay';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, order_no, order_name', 'required'),
			array('type, device_type, status', 'numerical', 'integerOnly'=>true),
			array('user_id, type_id, sub_type_id, amount', 'length', 'max'=>10),
			array('channel', 'length', 'max'=>10),
			array('order_no', 'length', 'max'=>32),
			array('order_name', 'length', 'max'=>50),
			array('pay_channel', 'length', 'max'=>4),
			array('pay_account, trade_no', 'length', 'max'=>64),
			array('create_time, update_time, paid_time', 'length', 'max'=>11),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, user_id, channel, type, type_id, sub_type_id, order_no, order_name, amount, device_type, pay_channel, pay_account, trade_no, status, create_time, update_time', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'user'=>array(self::BELONGS_TO, 'User', 'user_id'),
			'competition'=>array(self::BELONGS_TO, 'Competition', 'type_id'),
			'registration'=>array(self::BELONGS_TO, 'Registration', 'sub_type_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id'=>Yii::t('Pay', 'ID'),
			'user_id'=>Yii::t('Pay', 'User'),
			'channel'=>Yii::t('Pay', 'Channel'),
			'type'=>Yii::t('Pay', 'Type'),
			'type_id'=>Yii::t('Pay', 'Type'),
			'sub_type_id'=>Yii::t('Pay', 'Sub Type'),
			'order_no'=>Yii::t('Pay', 'Order No'),
			'order_name'=>Yii::t('Pay', 'Order Name'),
			'amount'=>Yii::t('Pay', 'Amount'),
			'device_type'=>Yii::t('Pay', 'Device Type'),
			'pay_channel'=>Yii::t('Pay', 'Pay Channel'),
			'pay_account'=>Yii::t('Pay', 'Pay Account'),
			'trade_no'=>Yii::t('Pay', 'Trade No'),
			'status'=>Yii::t('Pay', 'Status'),
			'create_time'=>Yii::t('Pay', 'Create Time'),
			'update_time'=>Yii::t('Pay', 'Update Time'),
			'paid_time'=>Yii::t('Pay', 'Paid Time'),
			'paid_time[0]'=>'开始时间',
			'paid_time[1]'=>'结束时间',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search() {
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria = new CDbCriteria;

		$criteria->compare('id', $this->id);
		$criteria->compare('user_id', $this->user_id);
		$criteria->compare('channel', $this->channel);
		$criteria->compare('type', $this->type);
		$criteria->compare('type_id', $this->type_id);
		$criteria->compare('sub_type_id', $this->sub_type_id);
		$criteria->compare('order_no', $this->order_no, true);
		$criteria->compare('order_name', $this->order_name, true);
		$criteria->compare('amount', $this->amount, true);
		$criteria->compare('device_type', $this->device_type);
		$criteria->compare('pay_channel', $this->pay_channel, true);
		$criteria->compare('pay_account', $this->pay_account, true);
		$criteria->compare('trade_no', $this->trade_no, true);
		$criteria->compare('status', $this->status);
		$criteria->compare('create_time', $this->create_time, true);
		$criteria->compare('update_time', $this->update_time, true);

		self::$_criteria = $criteria;

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'sort'=>array(
				'defaultOrder'=>'id DESC',
			),
			'pagination'=>array(
				'pageSize'=>100,
			),
		));
	}

	public function searchBill($pagination = ['pageSize'=>100]) {

		$criteria = new CDbCriteria;

		$criteria->compare('id', $this->id);
		$criteria->compare('user_id', $this->user_id);
		$criteria->compare('channel', $this->channel);
		$criteria->compare('type', $this->type);
		$criteria->compare('type_id', $this->type_id);
		$criteria->compare('sub_type_id', $this->sub_type_id);
		$criteria->compare('order_no', $this->order_no, true);
		$criteria->compare('order_name', $this->order_name, true);
		$criteria->compare('amount', $this->amount, true);
		$criteria->compare('device_type', $this->device_type);
		$criteria->compare('pay_channel', $this->pay_channel, true);
		$criteria->compare('pay_account', $this->pay_account, true);
		$criteria->compare('trade_no', $this->trade_no, true);
		$criteria->compare('status', $this->status);
		$this->compareTime($criteria);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'sort'=>array(
				'defaultOrder'=>'id DESC',
			),
			'pagination'=>$pagination,
		));
	}

	private function compareTime($criteria) {
		foreach (['create_time', 'update_time', 'paid_time'] as $attribute) {
			$time = $this->$attribute;
			if (!is_array($time)) {
				continue;
			}
			if (isset($time[0]) && ($temp = strtotime($time[0])) !== false) {
				$criteria->compare($attribute, '>=' . $temp);
			}
			if (isset($time[1]) && ($temp = strtotime($time[1])) !== false) {
				$criteria->compare($attribute, '<' . $temp);
			}
		}
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Pay the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
