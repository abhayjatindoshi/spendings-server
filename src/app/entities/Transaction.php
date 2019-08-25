<?php
namespace encryptorcode\entities;

use encryptorcode\dao\Model as Model;
use encryptorcode\services\UsersCache as UsersCache;

class Transaction extends Model{
    public $id;
    public $walletId;
    public $type;
    public $category;
    public $description;
    public $amount;
    public $createdBy;
    public $modifiedBy;
    public $createdTime;
    public $modifiedTime;

    public function __construct(){
        UsersCache::loadUsers($this->createdBy,$this->modifiedBy);
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