<?php
namespace encryptorcode\entities;

use encryptorcode\authentication\session\AuthSession as AuthSession;
use encryptorcode\authentication\user\AuthUser as AuthUser;
use encryptorcode\authentication\oauth\OauthToken as OauthToken;
use encryptorcode\dao\Model as Model;

class Session extends Model implements AuthSession{
    public $id;
    public $identifier;
    public $oauthStrategy;
    public $user;
    public $token;

    public function getId(){
        return $this->id;
    }

    public function setId($id){
        $this->id = $id;
    }

    function getIdentifier() : string{
        return $this->identifier;
    }
    function getOauthStrategy() : string{
        return $this->oauthStrategy;
    }
    function getUser() : AuthUser{
        return $this->user;
    }
    function getToken() : OauthToken{
        return $this->token;
    }
    
    function setToken($token) : void{
        $this->token = $token;
    }
}