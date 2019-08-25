<?php
namespace encryptorcode\controllers\v1;

use encryptorcode\router\Router as Router;
use encryptorcode\response\JsonResponse as JsonResponse;
use encryptorcode\services\WalletService as WalletService;
use encryptorcode\services\TransactionService as TransactionService;
use encryptorcode\router\ApiRequest as ApiRequest;

class WalletsController{

    private $walletService;
    private $transactionService;

    public function __construct(){
        $portalId = ApiRequest::header("X-EC-PORTAL");
        $this->walletService = new WalletService($portalId);
        $this->transactionService = new TransactionService($portalId);
    }

    public function process(){
        Router::authenticateRoute("GET",'',function(){
            return new JsonResponse($this->walletService->getAllWallets());
        });
        Router::authenticateRoute("GET",'/:id',function(){
            return new JsonResponse($this->walletService->getWallet(ApiRequest::pathVariable('id')));
        });
        Router::authenticateRoute("POST",'',function(){
            return new JsonResponse($this->walletService->createWallet(ApiRequest::body()));
        });
        Router::authenticateRoute(array("PUT","PATCH"),'/:id',function(){
            return $this->walletService->updateWallet(ApiRequest::pathVariable('id'), ApiRequest::body());
        });
        Router::authenticateRoute("DELETE",'/:id',function(){
            return $this->walletService->deleteWallet(ApiRequest::pathVariable('id'));
        });
        Router::authenticateRoute("GET",'/:id/transactions',function(){
            return new JsonResponse(
                $this->transactionService->getTransactions(
                    ApiRequest::pathVariable('id'),
                    ApiRequest::param('from'),
                    ApiRequest::param('limit')
                )
            );
        });
        Router::authenticateRoute("GET",'/:id/transactions/:transactionId',function(){
            return new JsonResponse(
                $this->transactionService->getTransaction(
                    ApiRequest::pathVariable('id'),
                    ApiRequest::pathVariable('transactionId')
                )
            );
        });
        Router::authenticateRoute("POST",'/:id/transactions',function(){
            return new JsonResponse(
                $this->transactionService->createTransaction(
                    ApiRequest::pathVariable('id'),
                    ApiRequest::body()
                )
            );
        });
        Router::authenticateRoute(array("PUT","PATCH"),'/:id/transactions/:transactionId',function(){
            return $this->transactionService->updateTransaction(
                ApiRequest::pathVariable('id'),
                ApiRequest::pathVariable('transactionId'),
                ApiRequest::body()
            );
        });
        Router::authenticateRoute("DELETE",'/:id/transactions/:transactionId',function(){
            return $this->transactionService->deleteTransaction(
                ApiRequest::pathVariable('id'),
                ApiRequest::pathVariable('transactionId')
            );
        });
    }
}