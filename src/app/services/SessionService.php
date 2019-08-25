<?php
namespace encryptorcode\services;

use encryptorcode\authentication\session\AuthSessionStorage as AuthSessionStorage;
use encryptorcode\authentication\session\AuthSession as AuthSession;
use encryptorcode\authentication\user\AuthUser as AuthUser;
use encryptorcode\authentication\oauth\OauthToken as OauthToken;
use encryptorcode\entities\Session as Session;
use encryptorcode\dao\Dao as Dao;

class SessionService implements AuthSessionStorage{
    
    private $sessionDao;

    public function __construct(){
        $this->sessionDao  = new Dao("encryptorcode\\entities\\Session");
    }

    function getSession(string $identifier) : ?AuthSession{
        $session = $this->sessionDao->getOne("SELECT * FROM Session WHERE IDENTIFIER = ?",$identifier);
        if(!isset($session)){
            return null;
        }
        $userService = new UserService();
        $user = $userService->getUser($session->user);
        $token = json_decode($session->token,true);
        $oauthToken = new OauthToken(
            $token["accessToken"],
            $token["refreshToken"],
            $token["expiryTime"]
        );
        $session->user = $user;
        $session->token = $oauthToken;
        return $session;
    }
    function createSession(string $identifier, string $strategyName, OauthToken $token, AuthUser $user) : void{
        $session = new Session();
        $session->identifier = $identifier;
        $session->oauthStrategy = $strategyName;
        $session->user = $user;
        $session->token = $token;
        $this->sessionDao->create($session);
    }
    function updateSessionToken(string $identifier, OauthToken $token) : void{
        $session = $this->getSession($identifier);
        if(!isset($session)){
            throw new ExceptionResponse("Invalid session identifier given for update","INVALID_SESSION",400);
        }
        $session->token = $token;
        $this->sessionDao->update($session);
    }
    function updateSessionAccessed(string $identifier) : void{
        error_log("Session last accessed at ".date("d-m-Y h:m:s"));
    }
    function deleteSession(string $identifier) : void{
        $session = $this->getSession($identifier);
        if(isset($session)){
            $this->sessionDao->delete($session);
        }
    }
}