<?php

namespace common\models;

use backend\components\Utils;
use DateTime;
use DateTimeZone;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "mailing".
 *
 * @property int $id
 * @property string $id_template
 * @property string $first_send
 * @property string $next_send
 * @property string $last_send
 * @property int $send_period
 * @property array $json_field
 * @property string $created_at
 * @property string $status
 */
class Mailing extends ActiveRecord {

	public $firstDateSend;
	public $lastDateSend;
	public $emails;
	public $ships		 = [];
	public $companies	 = [];
	public $company_country;
	public $company_nob;

	const SEND_PERIOD_EVERY_7_DAYS	 = 1;
	const SEND_PERIOD_EVERY_14_DAYS	 = 2;
	const SEND_PERIOD_MONTHLY		 = 3;
	const SEND_PERIOD_QUARTERLY		 = 4;
	const SEND_PERIOD_2_MONTHS		 = 5;
	const SEND_PERIOD_4_MONTHS		 = 6;
	const SEND_PERIOD_5_MONTHS		 = 7;
	const SEND_PERIOD_ONE_TIME		 = 8;
	const SEND_PERIOD_1_YEAR		 = 9;

    const STATUS_RUN = 0;
	const STATUS_STOP = 1;

	const SCENARIO_CONSOLE = 'console';

	/**
	 * {@inheritdoc}
	 */
	public static function tableName() {
		return 'mailing';
	}

	public function behaviors() {
		return [
			[
				'class'				 => TimestampBehavior::className(),
				'createdAtAttribute' => 'created_at',
				'updatedAtAttribute' => false,
				'value'				 => new Expression('UTC_TIMESTAMP()'),
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules() {
		return [
			[['id_template', 'firstDateSend', 'send_period'], 'required'],
			[['lastDateSend'], 'required', 'when' => function($model) { return $model->send_period != self::SEND_PERIOD_ONE_TIME;}],
			[['id_template', 'send_period'], 'integer'],
			[['firstDateSend', 'lastDateSend'], 'date', 'format' => Yii::$app->formatter->dateFormat],
			[['json_field', 'first_send','last_send', 'next_send', 'created_at'], 'safe'],
			[['next_send'], 'safe', 'on' => [self::SCENARIO_CONSOLE]],
			[['emails', 'ships', 'companies', 'company_country', 'company_nob'], 'safe'],
		];
	}


	public function afterFind() {
		parent::afterFind();
		if (!empty($this->json_field)) {
			$json = json_decode($this->json_field, true);
			$this->emails		 = $json['emails'];
			$this->ships		 = $json['filter_ships'];
			$this->companies	 = $json['filter_companies'];
			$this->company_country  = isset($json['filter_company_country']) ? $json['filter_company_country'] : '';
			$this->company_nob		= isset($json['filter_company_nob']) ? $json['filter_company_nob'] : '';
		}

		$this->firstDateSend = Utils::getDate($this->first_send, 'Y-m-d', Yii::$app->formatter->dateFormat);
		$this->lastDateSend	 = Utils::getDate($this->last_send, 'Y-m-d', Yii::$app->formatter->dateFormat);
	}

	public function beforeSave($insert) {
		if ($this->getScenario() != self::SCENARIO_CONSOLE){
			$this->first_send	 = Utils::getDate($this->firstDateSend, Yii::$app->formatter->dateFormat);
			$this->last_send	 = Utils::getDate($this->lastDateSend, Yii::$app->formatter->dateFormat);
			if ($this->send_period == self::SEND_PERIOD_ONE_TIME) {
				$this->next_send = $this->first_send;
				$this->last_send = $this->first_send;
			}
			else {
				$now				 = (new DateTime("now", new DateTimeZone('UTC')))->setTime(0,0);
				$dateFirst			 = DateTime::createFromFormat('Y-m-d', $this->first_send, new DateTimeZone('UTC'))->setTime(0,0);

				if ($this->isNewRecord || ($this->isAttributeChanged('first_send') && $dateFirst >= $now)) {
					$this->next_send = $this->first_send;
				}
				elseif (($this->send_period != $this->getOldAttribute('send_period') && !$this->isAttributeChanged('first_send')) || $this->isAttributeChanged('last_send') || ($this->isAttributeChanged('first_send') && $dateFirst < $now)) {
					$this->setNextDateSend();

				}
			}

		}


		$this->json_field	 = json_encode([
			'emails'			 => $this->emails,
			'filter_ships'		 => $this->ships,
			'filter_companies'	 => $this->companies,
			'filter_company_country' => $this->company_country,
			'filter_company_nob'	 => $this->company_nob,
		]);

		return parent::beforeSave($insert);
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels() {
		return [
			'id' => 'ID',
			'id_template' => 'Template',
			'firstDateSend' => 'First Date Send',
			'lastDateSend' => 'Last Date Send',
			'json_field' => 'Json Field',
			'emails' => 'Emails',
			'ships' => 'Ships Emails',
			'companies' => 'Companies Emails',
			'company_country' => 'Companies Country',
			'company_nob' => 'Companies Nature Of Business',
			'send_period' => 'Send Period',
		];
	}

	public function getLettersArray(): array {
		$array = [];
		$letters = range('A', 'Z');
		foreach ($letters as $letter) {
			$array[$letter] = $letter;
		}
		return $array;
	}

	public function getSendPeriodList(): array {
		return [
			self::SEND_PERIOD_ONE_TIME		 => 'One time',
			self::SEND_PERIOD_EVERY_7_DAYS	 => '7 days',
			self::SEND_PERIOD_EVERY_14_DAYS	 => '14 days',
			self::SEND_PERIOD_MONTHLY		 => 'Monthly',
			self::SEND_PERIOD_QUARTERLY		 => 'Quarterly',
			self::SEND_PERIOD_2_MONTHS		 => 'Two months',
			self::SEND_PERIOD_4_MONTHS		 => 'Four months',
			self::SEND_PERIOD_5_MONTHS		 => 'Five Months',
			self::SEND_PERIOD_1_YEAR		 => 'Annual',
		];
	}

	public function getSendPeriod(): string{
		return $this->getSendPeriodList()[$this->send_period];
	}

	public function setNextDateSend(){
        $dateEnd             = DateTime::createFromFormat('Y-m-d', $this->last_send, new DateTimeZone('UTC'))->setTime(0,0);
        $dateFirst			 = DateTime::createFromFormat('Y-m-d', $this->first_send, new DateTimeZone('UTC'))->setTime(0,0);
        $dateNext            = $this->calcNexSendPeriod($dateFirst);
        $this->next_send     = ($dateNext <= $dateEnd && $this->send_period != self::SEND_PERIOD_ONE_TIME) ? $dateNext->format('Y-m-d') : null;
	}

	public function calcNexSend($date) {
		switch ($this->send_period) {
			case self::SEND_PERIOD_EVERY_7_DAYS :
				$date->modify('+ 7 days');
				break;
			case self::SEND_PERIOD_EVERY_14_DAYS :
				$date->modify('+ 14 days');
				break;
			case self::SEND_PERIOD_2_MONTHS :
				$date->modify('+ 2 month');
				break;
			case self::SEND_PERIOD_QUARTERLY :
				$date->modify('+ 3 month');
				break;
			case self::SEND_PERIOD_4_MONTHS :
				$date->modify('+ 4 month');
				break;
			case self::SEND_PERIOD_5_MONTHS :
				$date->modify('+ 5 month');
				break;
			case self::SEND_PERIOD_1_YEAR :
				$date->modify('+ 12 month');
				break;
			case self::SEND_PERIOD_MONTHLY :
			default	:
				$date->modify('+ 1 month');
				break;
		}
		return $date;
	}

	public function calcNexSendPeriod($dateFirst) {
		$now = (new DateTime("now", new DateTimeZone('UTC')))->setTime(0,0);
		$dateNext = $this->calcNexSend($dateFirst);
		if ($dateNext < $now || ($dateNext == $now && $this->getScenario() == self::SCENARIO_CONSOLE)) {
			$dateFirst = $this->calcNexSendPeriod($dateNext);
		}
		return $dateFirst;
	}

	public function getTemplateList(): array {
		return ArrayHelper::map(Settings::find()->where(['is_deleted'=>false])->orderBy(['name' => 'ASC'])->asArray()->all(), 'id', 'label');
	}

	public function getTemplate() {
		return $this->hasOne(Settings::className(), ['id' => 'id_template']);
	}


}
