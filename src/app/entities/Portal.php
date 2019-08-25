<?php
namespace encryptorcode\entities;

use encryptorcode\dao\Model as Model;
use encryptorcode\services\UsersCache as UsersCache;

class Portal extends Model{
    public $id;
    public $name;
    public $ownedBy;
    public $createdBy;
    public $modifiedBy;
    public $createdTime;
    public $modifiedTime;

    public function __construct(){
        UsersCache::loadUsers($this->ownedBy,$this->createdBy,$this->modifiedBy);
        if(isset($this->ownedBy)){
            $this->ownedBy = UsersCache::getUser($this->ownedBy);
        }
        if(isset($this->createdBy)){
            $this->createdBy = UsersCache::getUser($this->createdBy);
        }
        if(isset($this->modifiedBy)){
            $this->modifiedBy = UsersCache::getUser($this->modifiedBy);
        }
    }

    public function getId(){
        return $this->id;
    }

    public function setId($id){
        $this->id = $id;
    }
}