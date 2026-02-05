<?php

namespace app\commands;

use app\models\LoanRequest;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

class LoanController extends Controller
{
    /**
     * команда: php yii loan/fake 10
     */
    public function actionFake($count = 10)
    {
        $success = 0;
        for ($i = 1; $i <= $count ; $i++) {
          $request = new LoanRequest();
          $request->user_id = $i;
          $request->status = LoanRequest::STATUS_NEW;
          $request->amount = 100;
          $request->term = 3;
          if ($request->save()) {
              $success++;
          }
        }
        echo "$success Done" . PHP_EOL;
        return ExitCode::OK;
    }

    /**
     * команда: php yii loan/truncate
     */
    public function actionTruncate()
    {
        Yii::$app->db->createCommand()->truncateTable('loan_request')->execute();
        echo "loan_request truncated" . PHP_EOL;
        return ExitCode::OK;
    }
}