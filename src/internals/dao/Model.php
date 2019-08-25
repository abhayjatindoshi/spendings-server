<?php
namespace encryptorcode\dao;

abstract class Model implements \JsonSerializable{
    
    abstract public function getId();
    abstract public function setId($id);

    public function jsonSerialize(){
        $vars = get_object_vars($this);
        return $vars;
    }
}