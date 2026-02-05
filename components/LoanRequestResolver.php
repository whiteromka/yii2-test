<?php

namespace app\components;

use app\models\LoanRequest;
use Throwable;
use Yii;

class LoanRequestResolver
{
    public function run(int $loanRequestId, int $delay)
    {
        $model = $this->markInProcess($loanRequestId);
        if (!$model) {
            return;
        }

        $this->processWithDelay($model, $delay);
    }

    /**
     * Берёт заявку и помечает её как IN_PROCESS
     */
    private function markInProcess(int $loanRequestId): ?LoanRequest
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $loanRequest = Yii::$app->db->createCommand("
                SELECT *
                FROM loan_request
                WHERE id = :id AND status = :status
                FOR UPDATE SKIP LOCKED
            ", [
                ':id' => $loanRequestId,
                ':status' => LoanRequest::STATUS_NEW,
            ])->queryOne();

            if (!$loanRequest) {
                $transaction->rollBack();
                return null;
            }

            $model = new LoanRequest();
            $model->setIsNewRecord(false);
            $model->setAttributes($loanRequest, false);
            $model->status = LoanRequest::STATUS_IN_PROCESS;
            $model->save(false);

            $transaction->commit();
            return $model;
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Выполняет sleep и затем решает статус заявки
     */
    private function processWithDelay(LoanRequest $model, int $delay): void
    {
        sleep($delay);
        $approved = rand(1, 10) === 1;

        $this->finalizeDecision($model, $approved);
    }

    /**
     * Ставит статус APPROVED/DECLINED с проверкой, что у пользователя не более одной одобренной заявки
     */
    private function finalizeDecision(LoanRequest $model, bool $approved): void
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($approved) {
                $exists = Yii::$app->db->createCommand("
                    SELECT id
                    FROM loan_request
                    WHERE user_id = :user_id
                      AND status = :status
                    LIMIT 1
                    FOR UPDATE
                ", [
                    ':user_id' => $model->user_id,
                    ':status' => LoanRequest::STATUS_APPROVED
                ])->queryOne();

                if ($exists) {
                    $approved = false;
                }
            }

            $model->status = $approved ? LoanRequest::STATUS_APPROVED : LoanRequest::STATUS_DECLINED;
            $model->processed_at = date('Y-m-d H:i:s');
            $model->save(false);

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}
