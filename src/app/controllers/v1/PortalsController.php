<?php
namespace encryptorcode\controllers\v1;

use encryptorcode\router\Router as Router;
use encryptorcode\response\JsonResponse as JsonResponse;
use encryptorcode\services\PortalService as PortalService;
use encryptorcode\router\ApiRequest as ApiRequest;

class PortalsController{
    
    private $portalService;

    public function __construct(){
        $this->portalService = new PortalService();
    }

    public function process(){
        Router::authenticateRoute("GET",'',function(){
            return new JsonResponse($this->portalService->getAllPortals());
        });
        Router::authenticateRoute("GET",'/:id',function(){
            return new JsonResponse(
                $this->portalService->getPortal(
                    ApiRequest::pathVariable('id')
                )
            );
        });
        Router::authenticateRoute("POST",'',function(){
            return new JsonResponse(
                $this->portalService->createPortal(
                    ApiRequest::body()
                )
            );
        });
        Router::authenticateRoute(array("PUT","PATCH"),'/:id',function(){
            return $this->portalService->updatePortal(
                ApiRequest::pathVariable('id'),
                ApiRequest::body()
            );
        });
        Router::authenticateRoute("DELETE",'/:id',function(){
            return $this->portalService->deletePortal(
                ApiRequest::pathVariable('id')
            );
        });
        Router::authenticateRoute("GET","/:id/users",function(){
            return new JsonResponse(
                $this->portalService->getUsersOfPortal(
                    ApiRequest::pathVariable("id")
                )
            );
        });
        Router::authenticateRoute("POST","/:id/users",function(){
            return new JsonResponse(
                $this->portalService->addUserToPortal(
                    ApiRequest::pathVariable("id"),
                    ApiRequest::body()
                )
            );
        });
        Router::authenticateRoute("DELETE","/:id/users",function(){
            return $this->portalService->removeUserFromPortal(
                    ApiRequest::pathVariable("id"),
                    ApiRequest::body()
            );
        });
    }
}