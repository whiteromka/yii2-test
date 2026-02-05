<?php

namespace app\components;

use app\models\LoanRequest;
use Throwable;
use Yii;
use yii\db\IntegrityException;

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
                $model->status = LoanRequest::STATUS_APPROVED;
            } else {
                $model->status = LoanRequest::STATUS_DECLINED;
            }
            $model->processed_at = date('Y-m-d H:i:s');
            $model->save();

            $transaction->commit();
        } catch (IntegrityException $e) {
            // если два job-а параллельно решили approved для разных заявок одного поль-ля
            $transaction->rollBack();

            $model->updateAttributes([
                'status' => LoanRequest::STATUS_DECLINED,
                'processed_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
