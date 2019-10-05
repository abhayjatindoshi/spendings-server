<?php
namespace encryptorcode\services;

use encryptorcode\dao\Dao as Dao;
use encryptorcode\authentication\server\AuthRequest as AuthRequest;
use encryptorcode\entities\Wallet as Wallet;
use encryptorcode\exception\ExceptionResponse as ExceptionResponse;
use encryptorcode\response\DefaultResponse as DefaultResponse;
use encryptorcode\dao\DbAccess as DbAccess;

class ChartService{

    private $dao;
    private $portalId;
    private $wallet;

    public function __construct($portalId){
        $this->dao = DbAccess::conn();
        $this->portalId = $portalId;
    }

    public function categoriesVsExpenses($walletId, $type, $month = null){
        if(!isset($type) || $type != ""){
            $type = 'EXPENSE';
        }

        $wallet = $this->getWallet($walletId);
        $query = "SELECT category, sum(amount) total_amount from Transaction where walletId = ? and type ='$type'";
        $params = array();
        $params[] = $walletId;
        if(isset($month)){
            $first = strtotime(date("Y-m-d", strtotime("first day of $month")));
            $last = strtotime(date("Y-m-d", strtotime("last day of $month")));
            $query .= " and createdTime > ? and createdTime < ?";
            $params[] = $first;
            $params[] = $last;
        }
        $query .= " group by category;";
        $data = $this->dao->selectAll($query,$params);
        return $data;
    }

    private function getWallet($walletId){
        if(!isset($this->wallet)){
            $walletService = new WalletService($this->portalId);
            $wallet = $walletService->getWallet($walletId);
            if(!isset($wallet)){
                DefaultResponse::invalidPortal();
            }
            $this->wallet = $wallet;
        }
        return $this->wallet;
    }
}