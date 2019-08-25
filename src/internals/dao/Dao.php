<?php
namespace encryptorcode\dao;

use encryptorcode\dao\Model as Model;
use encryptorcode\exception\DAOException as DAOException;

class Dao{
    
    private static $pdo;
    private static function pdo(){
        if(!isset(self::$pdo)){
            $host = DB_HOST;
            $username = DB_USERNAME;
            $password = DB_PASSWORD;
            $dbname = DB_NAME;
            self::$pdo = new \PDO("mysql:host=".$host.";dbname=".$dbname, $username, $password);
            self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }
        return self::$pdo;
    }

    private $className;
    private $tableName;
    
    public function __construct($className){
        $this->className = $className;
        $this->tableName = substr($className,strrpos($className,"\\")+1);
    }

    public function getAll($query, ...$params){
        if(isset($params) && count($params) == 1 && gettype($params[0]) == "array"){
            $params = $params[0];
        }

        $stmt = self::pdo()->prepare($query);
        $stmt->setFetchMode(\PDO::FETCH_CLASS,$this->className);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getOne($query, ...$params){
        if(isset($params) && count($params) == 1 && gettype($params[0]) == "array"){
            $params = $params[0];
        }

        $stmt = self::pdo()->prepare($query);
        $stmt->setFetchMode(\PDO::FETCH_CLASS,$this->className);
        $stmt->execute($params);
        if($stmt->rowCount() == 1){
            return $stmt->fetch();
        } else {
            return null;
        }
    }

    public function create(Model $entity){
        $id = $entity->getId();
        if(isset($id)){
            throw new DAOException("Please unset id field of the entity before trying to create.");
        }

        $dataMap = $this->getData($entity);
        $columnNames = array_keys($dataMap);
        $columnValues = array_values($dataMap);

        $query = "INSERT INTO $this->tableName (".implode(",",$columnNames).") values (".substr(str_repeat(",?",count($columnValues)),1).")";

        $stmt = self::pdo()->prepare($query);
        $stmt->execute($columnValues);
        $entity->setId(self::pdo()->lastInsertId());
        return $entity;
    }

    public function update(Model $entity){
        $id = $entity->getId();
        if(!isset($id)){
            throw new DAOException("You need to set entity id for updating");
        }

        $dataMap = $this->getData($entity);
        $columnNames = array_keys($dataMap);
        $columnValues = array_values($dataMap);
        
        $query = "UPDATE $this->tableName set ".implode(" = ?,",$columnNames)." = ? WHERE ID = ?";
        array_push($columnValues,$id);

        $stmt = self::pdo()->prepare($query);
        $stmt->execute($columnValues);
        return $stmt->rowCount();
    }

    public function delete(Model $entity){
        $id = $entity->getId();
        if(!isset($id)){
            throw new DAOException("You need to set entity id for updating");
        }

        $query = "DELETE FROM $this->tableName WHERE ID = ?";
        $stmt = self::pdo()->prepare($query);
        $stmt->execute(array($id));
        return $stmt->rowCount();
    }

    private function getData(Model $entity): ?array{
        if(!isset($entity)){
            return null;
        }

        $reflectionClass = new \ReflectionClass($entity);
        $reflectionFields = $reflectionClass->getProperties();
        
        if(!isset($reflectionFields)){
            return null;
        }

        $variableMap = array();
        foreach ($reflectionFields as $reflectionField) {
            $fieldName = $reflectionField->getName();
            if($fieldName[0] != '_'){
                $reflectionField->setAccessible(true);
                $value = $reflectionField->getValue($entity);
                if(gettype($value) == "object" && is_subclass_of($value,"encryptorcode\\dao\\Model")){
                    $value = $value->getId();
                } else if(gettype($value) == "object") {
                    $value = json_encode($value);
                }
                $variableMap[$fieldName] = $value;
            }
        }

        return $variableMap;
    }
}