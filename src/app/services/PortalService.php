<?php
namespace encryptorcode\services;

use encryptorcode\dao\Dao as Dao;
use encryptorcode\dao\DbAccess as DbAccess;
use encryptorcode\authentication\user\AuthUser as AuthUser;
use encryptorcode\authentication\server\AuthRequest as AuthRequest;
use encryptorcode\entities\Portal as Portal;
use encryptorcode\response\DefaultResponse as DefaultResponse;
use encryptorcode\exception\ExceptionResponse as ExceptionResponse;
use encryptorcode\enums\UserRole as UserRole;
use encryptorcode\entities\PortalUser as PortalUser;

class PortalService{

    private $portalDao;
    private $portalUserDao;

    public function __construct(){
        $this->portalDao = new Dao("encryptorcode\\entities\\Portal");
        $this->portalUserDao = DbAccess::conn();
    }

    public function getAllPortals(){
        $currentUser = AuthRequest::user();
        $portals = $this->portalDao->getAll("SELECT * FROM Portal WHERE Portal.ownedBy = ? OR Portal.id in (SELECT portalId from PortalUser where userId = ?)",$currentUser->id,$currentUser->id);
        return $portals;
    }

    public function getPortal($id){
        $currentUser = AuthRequest::user();
        $portal = $portals = $this->portalDao->getOne("SELECT * FROM Portal WHERE (Portal.ownedBy = ? OR Portal.id in (SELECT portalId from PortalUser where userId = ?)) AND Portal.id = ?",$currentUser->id,$currentUser->id,$id);
        return $portal;
    }

    public function createPortal($portalData){
        $currentUser = AuthRequest::user();

        $portal = new Portal();
        $portal->id = null;
        $portal->name = $portalData["name"];
        $portal->ownedBy = $currentUser;
        $portal->createdBy = $currentUser;
        $portal->modifiedBy = $currentUser;
        $portal->createdTime = time();
        $portal->modifiedTime = time();

        $createdPortal = $this->portalDao->create($portal);

        $this->portalUserDao->insert("INSERT INTO PortalUser (portalId,userId,userRole) values (?,?,?)",$createdPortal->id,$currentUser->id,UserRole::ADMIN);

        return $createdPortal;
    }

    public function updatePortal($id,$portalData){
        $portal = $this->getPortal($id);
        if(!isset($portal)){
            return DefaultResponse::invalidId();
        }

        $currentUser = AuthRequest::user();
        if($portal->ownedBy->id != $currentUser->id){
            DefaultResponse::updateForbidden("Portal");
        }

        $portal->name = $portalData["name"];
        $portal->modifiedBy = $currentUser;
        $portal->modifiedTime = time();

        $this->portalDao->update($portal);
        return DefaultResponse::updateSuccess();
    }

    public function deletePortal($id){
        $portal = $this->getPortal($id);
        if(!isset($portal)){
            return DefaultResponse::invalidId();
        }

        $currentUser = AuthRequest::user();
        if($portal->ownedBy->id != $currentUser->id){
            DefaultResponse::deleteForbidden("Portal");
        }

        $this->portalDao->delete($portal);
        return DefaultResponse::deleteSuccess();
    }

    public function getUsersOfPortal($portalId){
        $portal = $this->getPortal($portalId);
        if(!isset($portal)){
            return DefaultResponse::invalidId();
        }

        $usersList = $this->portalUserDao->selectAll('SELECT userId,userRole FROM PortalUser WHERE portalId = ?',$portalId);
        $usersRole = array();
        $userIds = array();
        foreach ($usersList as $user) {
            $userIds[] = $user["userId"];
            $usersRole[$user["userId"]] = $user["userRole"];
        }

        UsersCache::loadUserArray($userIds);
        $usersList = array();
        foreach ($userIds as $userId) {
            $user = UsersCache::getUser($userId);
            $user->role = $usersRole[$userId];
            $usersList[] = $user;
        }
        
        return $usersList;
    }

    public function addUserToPortal($portalId,$userData){
        $email = $userData["email"];
        $userRole = UserRole::USER;
        $currentUser = AuthRequest::user();
        
        if(isset($userData["role"])){
            if(!array_key_exists($userData["role"],UserRole::getConstList())){
                throw new ExceptionResponse("Invalid user role specified",'INVALID_USER_ROLE',400);
            }
            $userRole = $userData["role"];
        }
        
        $portal = $this->getPortal($portalId);
        if(!isset($portal)){
            return DefaultResponse::invalidId();
        }

        $portalUser = $this->getPortalUser($portalId,$currentUser->id);
        if($portalUser->userRole != UserRole::ADMIN){
            throw new ExceptionResponse("Only the admin is allowed to add users to the portal","UNAUTHORISED_ACCESS",403);
        }

        $userService = new UserService();
        $user = $userService->getUserByEmail($email);

        if(!isset($user)){
            $name = substr($email,0,strpos($email,'@'));
            $userService->createUser($email,$name,$name,array(),"");
            $user = $userService->getUserByEmail($email);
        } else {
            $portalUserRows = $this->portalUserDao->selectAll("SELECT * FROM PortalUser WHERE userId = ? && portalId = ?",$user->id,$portalId);
            if(count($portalUserRows) != 0){
                throw new ExceptionResponse("User is already added to portal","USER_ALREADY_ADDED",400);
            }
        }
        
        $this->portalUserDao->insert("INSERT INTO PortalUser (portalId,userId,userRole) values (?,?,?)",$portalId,$user->id,$userRole);
        
        $portal->modifiedBy = $currentUser;
        $portal->modifiedTime = time();
        $this->portalDao->update($portal);

        return $user;
    }

    public function removeUserFromPortal($portalId,$userData){
        $userId = $userData["id"];
        $currentUser = AuthRequest::user();

        $portal = $this->getPortal($portalId);
        if(!isset($portal)){
            return DefaultResponse::invalidId();
        }
        
        $portalUserRows = $this->portalUserDao->selectAll("SELECT * FROM PortalUser WHERE userId = ? && portalId = ?",$userId,$portalId);
        if(count($portalUserRows) == 0){
            return DefaultResponse::invalidId();           
        }

        $portalUser = $this->getPortalUser($portalId,$currentUser->id);
        if($portalUser->userRole != UserRole::ADMIN){
            throw new ExceptionResponse("Only the admin is allowed to remove users from the portal","UNAUTHORISED_ACCESS",403);
        }

        $this->portalUserDao->query("DELETE FROM PortalUser where userId = ? && portalId = ?",$userId,$portalId);

        $portal->modifiedBy = $currentUser;
        $portal->modifiedTime = time();
        $this->portalDao->update($portal);

        return DefaultResponse::deleteSuccess();
    }

    public function getPortalUser($portalId,$userId){
        $portalUserRows = $this->portalUserDao->selectAll("SELECT * FROM PortalUser WHERE userId = ? && portalId = ?",$userId,$portalId);
        if(count($portalUserRows) == 1){
            $portalUserRow = $portalUserRows[0];
            $portalUser = new PortalUser();
            $portalUser->portalId = $portalUserRow["portalId"];
            $portalUser->userId = $portalUserRow["userId"];
            $portalUser->userRole = $portalUserRow["userRole"];
            return $portalUser;
        } else {
            return null;
        }
    }
}