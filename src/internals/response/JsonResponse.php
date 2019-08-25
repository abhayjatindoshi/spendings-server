<?php
namespace encryptorcode\response;

use encryptorcode\server\response\Response as Response;

class JsonResponse extends Response{

    public function __construct($data, int $status = 200){
        if(isset($data)){
            $data = json_encode($data);
        }

        parent::__construct($data,$status);
    }

    public function respond() : void{
        header('Content-Type: application/json');
        parent::respond();
    }    
}