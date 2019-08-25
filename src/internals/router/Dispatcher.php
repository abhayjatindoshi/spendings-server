<?php
namespace encryptorcode\router;

use encryptorcode\server\request\Request as Request;
use encryptorcode\exception\ExceptionResponse as ExceptionResponse;

class Dispatcher{
    public static function dispatch($controllerPackageName = ""){
        $module = ApiRequest::module();
        if(isset($module)){
            $controllerName = $controllerPackageName.ucfirst($module)."Controller";
            if(class_exists($controllerName)){
                $controller = new $controllerName();
                $controller->process();
            }
        }

        throw new ExceptionResponse('Invalid API request made. Please check the url','INVALID_API',400);
    }
}

