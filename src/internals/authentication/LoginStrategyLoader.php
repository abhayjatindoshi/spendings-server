<?php
namespace encryptorcode\authentication;

use encryptorcode\authentication\service\StrategyLoader as StrategyLoader;
use encryptorcode\authentication\oauth\OauthStrategy as OauthStrategy;

class LoginStrategyLoader implements StrategyLoader{
    function get(string $strategy) : OauthStrategy{
        $className = "encryptorcode\\authentication\\".ucfirst($strategy)."AuthenticationStrategy";
        return new $className();
    }
}