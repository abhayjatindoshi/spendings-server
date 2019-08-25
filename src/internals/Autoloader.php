<?php
spl_autoload_register(function($className){
    $fileName = ROOT."/src/".str_replace("\\","/",$className).".php";

    if(file_exists(str_replace("encryptorcode","app",$fileName))){
        include_once str_replace("encryptorcode","app",$fileName);
    } else if(file_exists(str_replace("encryptorcode","internals",$fileName))){
        include_once str_replace("encryptorcode","internals",$fileName);
    } else {
        error_log("Failed: $className");
    }
});

use encryptorcode\exception\ExceptionResponse as ExceptionResponse;

set_exception_handler(function ($exception){
    if(!$exception instanceof ExceptionResponse){
        $exception = new ExceptionResponse(
            "Oops, Looks like server couldn't handle it",
            'INTERNAL_ERROR',
            500,
            $exception
        );
    }
    $exception->respond();
});