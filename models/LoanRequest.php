<?php

namespace app\models;

use app\models\validators\NoApprovedLoanValidator;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "loan_request".
 *
 * @property int $id
 * @property int $user_id
 * @property int $amount
 * @property int $term
 * @property string $status
 * @property string|null $processed_at Когда обработали заявку
 * @property string $created_at
 * @property string $updated_at
 */
class LoanRequest extends ActiveRecord
{
    public const STATUS_NEW = 'new';
    public const STATUS_IN_PROCESS = 'in_process';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_DECLINED = 'declined';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'loan_request';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['processed_at'], 'default', 'value' => null],
            [['status'], 'default', 'value' => self::STATUS_NEW],
            [['user_id', 'amount', 'term'], 'required'],
            [['user_id', 'amount', 'term'], 'default', 'value' => null],
            [['user_id', 'amount', 'term'], 'integer'],
            [['user_id'], NoApprovedLoanValidator::class],
            [['processed_at', 'created_at', 'updated_at'], 'safe'],
            [['status'], 'string', 'max' => 255]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'amount' => 'Amount',
            'term' => 'Term',
            'status' => 'Status',
            'processed_at' => 'Processed At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
