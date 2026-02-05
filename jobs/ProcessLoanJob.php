<?php

namespace app\jobs;

use app\components\LoanRequestResolver;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class ProcessLoanJob extends BaseObject implements JobInterface
{
    public int $loanRequestId;
    public int $delay;

    public function execute($queue): void
    {
        (new LoanRequestResolver())->run($this->loanRequestId, $this->delay);
    }
}
