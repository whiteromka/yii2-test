<?php

namespace app\models\validators;

use yii\validators\Validator;
use app\models\LoanRequest;

class NoApprovedLoanValidator extends Validator
{
    /**
     * Проверяет, что у пользователя нет одобренных заявок
     *
     * @param $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute): void
    {
        $exists = LoanRequest::find()
            ->where([
                'user_id' => $model->$attribute,
                'status' => LoanRequest::STATUS_APPROVED
            ])
            ->exists();

        if ($exists) {
            $this->addError($model, $attribute, 'Пользователь уже имеет одобренную заявку.');
        }
    }
}
