<?php
namespace encryptorcode\dao;

use encryptorcode\exception\DAOException as DAOException;

class DbAccess{
    
    private static $instance;

    public static function conn(){
        if (!isset(self::$instance)) {
            self::$instance = new DbAccess();
        }
        return self::$instance;
    }

    private $host = DB_HOST;
    private $username = DB_USERNAME;
    private $password = DB_PASSWORD;
    private $dbname = DB_NAME;
    private $conn;

    private function __construct(){
        $this->conn = new \PDO("mysql:host=".$this->host.";dbname=".$this->dbname, $this->username, $this->password);
        $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function selectAll($query, ...$params){
        if(isset($params) && count($params) == 1 && gettype($params[0]) == "array"){
            $params = $params[0];
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC); 
        return $stmt->fetchAll();
    }

    public function selectOne($query, ...$params){
        if(isset($params) && count($params) == 1 && gettype($params[0]) == "array"){
            $params = $params[0];
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC); 
        if($stmt->rowCount() == 1){
            return $stmt->fetch();    
        } else {
            return null;
        }
        
    }

    public function insert($query, ...$params){
        if(isset($params) && count($params) == 1 && gettype($params[0]) == "array"){
            $params = $params[0];
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return array("id" => $this->conn->lastInsertId());
    }

    public function insertAll($query, $params){
        $ids = array();
        $stmt = $this->conn->prepare($query);
        $params_count = count($params);
        for ($i=0; $i < $params_count; $i++) { 
            $stmt->execute($params);
            $ids[] = $this->conn->lastInsertId();
        }
        return $ids;
    }

    public function query($query, ...$params){
        if(isset($params) && count($params) == 1 && gettype($params[0]) == "array"){
            $params = $params[0];
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
}

?>