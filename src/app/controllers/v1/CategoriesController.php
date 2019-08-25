<?php
namespace encryptorcode\controllers\v1;

use encryptorcode\authentication\server\AuthRequest as AuthRequest;
use encryptorcode\router\Router as Router;
use encryptorcode\response\JsonResponse as JsonResponse;
use encryptorcode\dao\DbAccess as DbAccess;
use encryptorcode\router\ApiRequest as ApiRequest;

class CategoriesController{
    public function process(){
        Router::authenticateRoute('GET','',function(){
            $portalId = ApiRequest::header("X-EC-PORTAL");
            $categoryList = DbAccess::conn()->selectAll('SELECT DISTINCT Transaction.category from Transaction join Wallet on Transaction.walletId = Wallet.id where Wallet.portalId = '.$portalId);
            $categories = array();
            foreach ($categoryList as $category) {
                $categories[] = $category["category"];
            }
            return new JsonResponse($categories);
        });
    }
}