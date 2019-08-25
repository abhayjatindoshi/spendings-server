<?php
require "../../init.php";

use encryptorcode\authentication\AuthenticationManager as AuthenticationManager;
use encryptorcode\exception\ExceptionResponse as ExceptionResponse;
try{
    AuthenticationManager::getService()->oauthCallback();
} catch(\Exception $e){
    throw new ExceptionResponse("You are not allowed to login","INVALID_USER",400);
}
?>