<?php
namespace encryptorcode\router;

use encryptorcode\server\request\Request as Request;
use encryptorcode\server\response\Response as Response;
use encryptorcode\authentication\AuthenticationManager as AuthenticationManager;
use encryptorcode\exception\ExceptionResponse as ExceptionResponse;

class Router{

    public static function authenticateRoute($methods, string $path, $handler){
        return self::handleRouting($methods,$path,$handler,true);
    }

    public static function route($methods, string $path, $handler){
        return self::handleRouting($methods,$path,$handler,false);
    }

    private static function handleRouting($methods, string $path, $handler, bool $authenticated){
        if(gettype($methods) === "string"){
            $methods = array($methods);
        }

        if(!in_array(Request::method(),$methods)){
            return false;
        }

        $routerPathComponents = $path;

        if(gettype($routerPathComponents) != "array"){
            $routerPathComponents = explode('/',$path);
        }

        if(count($routerPathComponents) < 1){
            throw new Exception("path specified for the route is invalid: $path");
        }

        if($routerPathComponents[0] == ''){
            array_shift($routerPathComponents);
        }

        $requestPathComponents = ApiRequest::pathComponents();
        $requestPathCount = count($requestPathComponents);
        $routerPathCount = count($routerPathComponents);

        if($routerPathCount != $requestPathCount){
            return false;
        }

        for ($i=0; $i < $routerPathCount; $i++) { 
            $requestComponent = $requestPathComponents[$i];
            $routerComponent = $routerPathComponents[$i];
            
            // If it is a variable we find and set it to the request object itself
            if(substr($routerComponent,0,1) == ":"){
                if(!isset($requestPathComponents)){
                    $requestPathComponents = array();
                }
                $requestPathComponents[substr($routerComponent,1)] = $requestComponent;
            
            // if it is not a variable it should match the string exactly
            } else if($requestComponent != $routerComponent){
                return false;
            }
        }
        
        ApiRequest::setPathVariables($requestPathComponents);

        if($authenticated == true){
            $user = AuthenticationManager::getService()->getCurrentUser();
            if(!isset($user)){
                throw new ExceptionResponse("You need to login first.","USER_NOT_LOGGED_IN",403);
            }
        }

        $response = $handler();

        if(!isset($response)){
            throw new \Exception("No reponse from the controller");
        }

        if(!$response instanceof Response){
            throw new \Exception("Invalid response object returned from controller");
        }

        $response->respond();
    }
}