<?php
namespace encryptorcode\services;

use encryptorcode\entities\User;
use encryptorcode\dao\Dao as Dao;

class UsersCache{

    private static $userDao;
    private static $idVsUsers;
    private static $emailVsUsers;

    public static function loadUsers(?string ...$ids) : void {
        self::loadUserArray($ids);
    }

    public static function loadUserArray(?array $ids) : void {
        $userIds = array();
        $userEmails = array();

        foreach ($ids as $id) {
            if(isset($id)){
                if(preg_match('/^[0-9]+$/',$id)){
                    if(!isset(self::$idVsUsers) || !array_key_exists($id,self::$idVsUsers)){
                        $userIds[] = $id;
                    }
                } else {
                    if(!isset(self::$emailVsUsers) || !array_key_exists($id,self::$emailVsUsers)){
                        $userEmails[] = $id;
                    }
                }
            }
        }

        self::fetchAll($userIds, $userEmails);
    }

    public static function getUser(string $id) : ?User {
        if(preg_match('/^[0-9]+$/',$id)){
            if(isset(self::$idVsUsers[$id])){
                return self::$idVsUsers[$id];
            }
        } else {
            if(isset(self::$emailVsUsers[$id])){
                return self::$emailVsUsers[$id];
            }
        }
        return null;
    }

    private static function addUser(User $user) : void {
        if(!isset(self::$idVsUsers)){
            self::$idVsUsers = array();
        }
        if(!isset(self::$emailVsUsers)){
            self::$emailVsUsers = array();
        }

        self::$idVsUsers[$user->id] = $user;
        self::$emailVsUsers[$user->email] = $user;
    }

    private static function fetchAll($ids, $emails) : void {
        if((!isset($ids) || count($ids) == 0) && (!isset($emails) || count($emails) == 0)){
            return;
        }

        $query = "SELECT * FROM User WHERE";
        $values = array();

        $wasIdsSet = false;
        
        if(isset($ids) && count($ids) > 0){
            $query .= " ID in (".substr(str_repeat(",?",count($ids)),1).")";
            $values = array_merge($values,$ids);
            $wasIdsSet = true;
        }

        if(isset($emails) && count($emails) > 0){
            if($wasIdsSet == true){
                $query .= " OR ";
            }
            $query .= " EMAIL in (".substr(str_repeat(",?",count($emails)),1).")";
            $values = array_merge($values,$emails);
        }

        if(!isset(self::$userDao)){
            self::$userDao = new Dao("encryptorcode\\entities\\User");
        }

        $users = self::$userDao->getAll($query,$values);
        if(!isset($users) || count($users) == 0){
            return;
        }

        foreach ($users as $user) {
            self::addUser($user);
        }
    }
}