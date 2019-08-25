<?php
namespace encryptorcode\entities;

use encryptorcode\authentication\user\AuthUser as AuthUser;
use encryptorcode\dao\Model as Model;

class User extends Model implements AuthUser{
    public $id;
    public $name;
    public $fullName;
    public $email;
    public $strategyVsIdMap;
    public $profileImage;

    public function __construct(){
        if(isset($this->strategyVsIdMap)){
            $this->strategyVsIdMap = json_decode($this->strategyVsIdMap,true);
        }
    }

    public function getId(){
        return $this->id;
    }

    public function setId($id){
        $this->id = $id;
    }    

    function getName() : string{
        return $this->name;
    }
    function getFullName() : string{
        return $this->fullName;
    }
    function getEmail() : string{
        return $this->email;
    }
    function getStrategyVsIdMap() : array{
        return $this->strategyVsIdMap;
    }
    function getProfileImage() : string{
        return $this->profileImage;
    }

    function setName(string $name) : void{
        $this->name = $name;
    }
    function setFullName(string $fullName) : void{
        $this->fullName = $fullName;
    }
    function setEmail(string $email) : void{
        $this->email = $email;
    }
    function setStrategyVsIdMap(array $strategyVsIdMap) : void{
        $this->strategyVsIdMap = $strategyVsIdMap;
    }
    function setProfileImage(string $profileImage) : void{
        $this->profileImage = $profileImage;
    }
}