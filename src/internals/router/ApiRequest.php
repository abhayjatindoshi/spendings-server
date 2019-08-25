<?php
namespace encryptorcode\router;

use encryptorcode\server\request\Request as Request;

class ApiRequest extends Request{
    private $version;
    private $module;
    private $pathComponents;
    private $body;
    private $pathVariables;

    private function __construct(){
        $urlData = parse_url(parent::path());
        $pathArray = explode("/",trim($urlData["path"]));
        
        // removing the first element as it will always be an empty element
        array_shift($pathArray);

        if(count($pathArray) < 3){
            return;
        }

        if(empty($pathArray[2])){
            return;
        }

        // removing `api` from the array
        array_shift($pathArray);

        $this->version = array_shift($pathArray);
        $this->module = array_shift($pathArray);
        $this->pathComponents = $pathArray;

        $contentType = parent::header("CONTENT-TYPE");
        if(isset($contentType)){
            switch($contentType){
                case "application/json":
                    $this->body = json_decode(file_get_contents("php://input"),true);
                    break;
            }
        }
    }

    private static $request;
    private static function request() : ApiRequest{
        if(!isset(self::$request)){
            self::$request = new ApiRequest();
        }
        return self::$request;
    }

    public static function version() : string{
        return self::request()->version;
    }

    public static function module() : ?string{
        return self::request()->module;
    }

    public static function pathComponents() : array{
        return self::request()->pathComponents;
    }

    public static function setPathVariables($pathVariables){
        self::request()->pathVariables = $pathVariables;
    }

    public static function pathVariable(string $name) : ?string{
        $pathVariables = self::request()->pathVariables;
        if(isset($pathVariables[$name])){
            return $pathVariables[$name];
        } else {
            return null;
        }
    }
    
    public static function body(){
        return self::request()->body;
    }
       
}
