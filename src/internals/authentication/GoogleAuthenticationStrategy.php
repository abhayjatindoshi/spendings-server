<?php
namespace encryptorcode\authentication;

use encryptorcode\authentication\implementation\OauthStrategyHelper as OauthStrategyHelper;
use encryptorcode\authentication\implementation\OauthStrategyDetails as OauthStrategyDetails;
use encryptorcode\authentication\implementation\DefaultOauthStrategy as DefaultOauthStrategy;
use encryptorcode\authentication\oauth\OauthToken as OauthToken;
use encryptorcode\authentication\oauth\OauthUser as OauthUser;

class GoogleStrategyHelper extends OauthStrategyHelper{
	public function getDetails() : OauthStrategyDetails{
		$details = new OauthStrategyDetails(
			"https://accounts.google.com/o/oauth2/v2/auth",
			GOOGLE_CLIENT_ID,
			GOOGLE_CLIENT_SECRET,
			SERVER_DOMAIN."/auth/callback.php",
			"https://www.googleapis.com/oauth2/v4/token",
			"https://accounts.google.com/o/oauth2/revoke",
			"https://www.googleapis.com/oauth2/v1/userinfo?alt=json",
			"https://www.googleapis.com/auth/userinfo.email%20https://www.googleapis.com/auth/userinfo.profile"
		);
		return $details;
	}

	public function readUser(string $userJson) : OauthUser{
		$user = json_decode($userJson,true);
		return new OauthUser($user["id"],$user["email"],$user["name"],$user["given_name"],$user["picture"]);
	}

	public function readToken(string $tokenJson) : OauthToken{
		$token = json_decode($tokenJson,true);
		$expiryTime = time() + $token["expires_in"];
		if(isset($token["refresh_token"])){
			return new OauthToken($token["access_token"],$token["refresh_token"],$expiryTime);
		} else {
			return new OauthToken($token["access_token"],'',$expiryTime);
		}
	}
}

class GoogleAuthenticationStrategy extends DefaultOauthStrategy{
	public function __construct(){
		$helper = new GoogleStrategyHelper();
		parent::__construct($helper);
	}
}
?>