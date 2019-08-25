<?php
namespace encryptorcode\controllers\v1;

use encryptorcode\authentication\server\AuthRequest as AuthRequest;
use encryptorcode\router\Router as Router;
use encryptorcode\response\JsonResponse as JsonResponse;

class MeController{
    
    public function process(){
        Router::authenticateRoute("GET",'',function(){
            return new JsonResponse(AuthRequest::user());
        });
    }
}
?>