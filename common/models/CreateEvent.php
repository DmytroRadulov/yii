<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "create_event".
 *
 * @property int $id
 * @property int $type
 * @property int $model_key
 * @property string $text
 * @property string $created_at
 */
class CreateEvent extends ActiveRecord
{

    const TYPE_COMPANY	 = 1;
    const TYPE_SHIPS	 = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'create_event';
    }

    /**
     * {@inheritdoc}
     */


    public function rules()
    {
        return [
            [['type', 'model_key'], 'required'],
            [['type', 'model_key'], 'integer'],
            [['text'], 'string'],
            [['created_at'], 'safe'],
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => false,
                'value' => new Expression('UTC_TIMESTAMP()'),

            ]
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'model_key' => 'Model Key',
            'text' => 'Text',
            'created_at' => 'Created At',
        ];
    }
}
