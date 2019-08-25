<?php
namespace encryptorcode\exception;

use encryptorcode\response\JsonResponse as JsonResponse;

class ExceptionResponse extends \Exception{
    
    private $error_code;

    public function __construct(
        $message, $error_code, $code, $previous = null
    ){
        parent::__construct($message, $code, $previous);
        $this->error_code = $error_code;
    }

    public function respond(){
        error_log($this);
        $error = array();
        $error["code"] = $this->error_code;
        $error["message"] = $this->message;
        $error["status"] = 'FAILED';
        $response = new JsonResponse($error,$this->code);
        $response->respond();
    }
}