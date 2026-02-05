<?php

namespace app\services;

use app\jobs\ProcessLoanJob;
use app\models\LoanRequest;
use Yii;
use yii\db\Exception;
use yii\queue\Queue;

class JobLoanRequestService
{
    /**
     * Обрабатывать loan_request-ы через job-ы и очереди
     *
     * @param int $delay
     * @return int
     * @throws Exception
     */
    public function startResolve(int $delay): int
    {
        $count = 0;
        $rows = Yii::$app->db->createCommand("
            SELECT id
            FROM loan_request
            WHERE status = :status",
            [':status' => LoanRequest::STATUS_NEW]
        )->queryAll();

        foreach ($rows as $row) {
            /** @var Queue $queue */
            $queue = Yii::$app->queue;
            $queue->push(new ProcessLoanJob([
                'loanRequestId' => $row['id'],
                'delay' => $delay,
            ]));
            $count++;
        }

        return $count;
    }
}