<?php
namespace encryptorcode\services;

use encryptorcode\authentication\user\AuthUserService as AuthUserService;
use encryptorcode\authentication\user\AuthUser as AuthUser;
use encryptorcode\entities\User as User;
use encryptorcode\dao\Dao as Dao;

class UserService implements AuthUserService{

    private $userDao;

    public function __construct(){
        $this->userDao = new Dao("encryptorcode\\entities\\User");
    }

    function getUser($id) : ?AuthUser{
        UsersCache::loadUsers($id);
        return UsersCache::getUser($id);
    }

    public function getUserByEmail(string $email) : ?AuthUser{
        UsersCache::loadUsers($email);
        return UsersCache::getUser($email);
    }
    public function createUser(string $email, string $name, string $fullName, array $strategyVsIdMap, string $profileImage) : void{
        $user = new User();
        $user->email = $email;
        $user->name = $name;
        $user->fullName = $fullName;
        $user->strategyVsIdMap = json_encode($strategyVsIdMap);
        $user->profileImage = $profileImage;
        $this->userDao->create($user);
    }
    public function updateUser(AuthUser $authUser) : void{
        $user = $this->getUserByEmail($authUser->getEmail());
        if(!isset($user)){
            throw new ExceptionResponse("Invalid user email given for update","INVALID_EMAIL",400);
        }

        $user->strategyVsIdMap = json_encode($authUser->getStrategyVsIdMap());
        $this->userDao->update($user);
    }
}