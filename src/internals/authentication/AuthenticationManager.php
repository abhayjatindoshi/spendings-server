<?php
namespace encryptorcode\authentication;

use encryptorcode\authentication\service\AuthenticationHelper as AuthenticationHelper;
use encryptorcode\authentication\service\StrategyLoader as StrategyLoader;
use encryptorcode\authentication\service\AuthenticationService as AuthenticationService;
use encryptorcode\authentication\user\AuthUserService as AuthUserService;
use encryptorcode\authentication\session\AuthSessionStorage as AuthSessionStorage;
use encryptorcode\services\UserService as UserService;
use encryptorcode\services\SessionService as SessionService;
use encryptorcode\authentication\oauth\OauthUser as OauthUser;
use encryptorcode\authentication\user\AuthUser as AuthUser;

class AuthenticationManager extends AuthenticationHelper {

    private static $authenticationService;

    public static function getService() : AuthenticationService{
        if(!isset(AuthenticationManager::$authenticationService)){
            $authenticationManager = new AuthenticationManager();
            AuthenticationManager::$authenticationService = new AuthenticationService($authenticationManager);
        }
        return AuthenticationManager::$authenticationService;
    }

    public function getStrategyLoader() : StrategyLoader{
        return new LoginStrategyLoader();
    }
    public function getUserService() : AuthUserService{
        return new UserService();
    }
    public function getSessionStorage() : AuthSessionStorage{
        return new SessionService();
    }
    public function isUserAllowedSignUp(OauthUser $user) : bool{
        return false;
    }
    public function isUserAllowedLogin(AuthUser $user) : bool{
        return true;
    }
}