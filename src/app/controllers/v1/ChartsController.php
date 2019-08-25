<?php
namespace encryptorcode\controllers\v1;

use encryptorcode\authentication\server\AuthRequest as AuthRequest;
use encryptorcode\router\Router as Router;
use encryptorcode\response\JsonResponse as JsonResponse;
use encryptorcode\router\ApiRequest as ApiRequest;
use encryptorcode\services\ChartService as ChartService;

class ChartsController{

    private $chartService;

    public function __construct(){
        $portalId = ApiRequest::header('X-EC-PORTAL');
        $this->chartService = new ChartService($portalId);
    }

    public function process(){
        Router::authenticateRoute('GET','/wallet/:id/categoriesVsExpenses',function(){
            return new JsonResponse(
                $this->chartService->categoriesVsExpenses(
                    ApiRequest::pathVariable('id'),
                    ApiRequest::param('type'),
                    ApiRequest::param('month')
                )
            );
        });
    }
}