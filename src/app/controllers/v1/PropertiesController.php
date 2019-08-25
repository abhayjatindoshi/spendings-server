<?php
namespace encryptorcode\controllers\v1;

use encryptorcode\authentication\server\AuthRequest as AuthRequest;
use encryptorcode\router\Router as Router;
use encryptorcode\response\JsonResponse as JsonResponse;
use encryptorcode\dao\DbAccess as DbAccess;
use encryptorcode\router\ApiRequest as ApiRequest;
use encryptorcode\services\WalletService as WalletService;
use encryptorcode\services\PortalService as PortalService;

class PropertiesController{
    
    public function process(){
        Router::authenticateRoute("GET",'',function(){
            
            $user = AuthRequest::user();

            $portalService = new PortalService();
            $portals = $portalService->getAllPortals();
            $portal = $portals[0];
            $portalId = $portal->id;
            
            $categoryList = DbAccess::conn()->selectAll("SELECT DISTINCT Transaction.category from Transaction join Wallet on Transaction.walletId = Wallet.id where Wallet.portalId = ?",$portalId);
            $categories = array();
            foreach ($categoryList as $category) {
                $categories[] = $category["category"];
            }

            $walletService = new WalletService($portalId);
            $wallets = $walletService->getAllWallets();
            
            $properties = array();
            $properties["portal"] = $portal;
            $properties["categories"] = $categories;
            $properties["user"] = $user;
            $properties["wallets"] = $wallets;
            return new JsonResponse($properties);
        });
    }
}
?>