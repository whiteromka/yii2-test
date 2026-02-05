<?php

namespace app\controllers;

use app\services\JobLoanRequestService;
use app\services\LoanRequestService;
use yii\filters\ContentNegotiator;
use yii\rest\Controller;
use yii\web\Response;
use app\models\LoanRequest;
use Yii;

class LoanRequestController extends Controller
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::class,
                'formats' => ['application/json' => Response::FORMAT_JSON],
            ],
        ];
    }

    /**
     * POST /requests Подать новую заявку на займ
     */
    public function actionRequests()
    {
        $loanRequest = new LoanRequest();
        if ($loanRequest->load(Yii::$app->request->getBodyParams(), '') && $loanRequest->save()) {
            Yii::$app->response->statusCode = 201;
            return [
                'result' => true,
                'id' => $loanRequest->id,
            ];
        }

        Yii::$app->response->statusCode = 400;
        return [
            'result' => false,
            'errors' => $loanRequest->errors
        ];
    }

    /**
     * GET /processor Запустить обработку заявок через очереди, job-ы
     */
    public function actionProcessor()
    {
        $delay = (int)Yii::$app->request->get('delay', 1);
        $loanRequestService = Yii::$container->get(JobLoanRequestService::class);
        $loanRequestService->startResolve($delay);

        return ['result' => true];
    }
}
