<?php
namespace encryptorcode\services;

use encryptorcode\dao\Dao as Dao;
use encryptorcode\authentication\server\AuthRequest as AuthRequest;
use encryptorcode\response\DefaultResponse as DefaultResponse;
use encryptorcode\entities\Transaction as Transaction;
use encryptorcode\enums\TransactionType as TransactionType;
use encryptorcode\exception\ExceptionResponse as ExceptionResponse;

class TransactionService{

    private $transactionDao;
    private $walletDao;
    private $portalId;
    private $wallet;

    public function __construct($portalId){
        $this->transactionDao = new Dao("encryptorcode\\entities\\Transaction");
        $this->walletDao = new Dao("encryptorcode\\entities\\Wallet");
        $this->portalId = $portalId;
    }

    public function getTransactions($walletId, $fromTransactionId, $limit){
        $wallet = $this->getWallet($walletId);

        $query = "SELECT * FROM Transaction WHERE walletId = ?";
        $params = array($walletId);
        if(isset($fromTransactionId)){
            $query .= " AND id < ?";
            $params[] = $fromTransactionId;
        }
        $query .= " ORDER BY transactionTime DESC";
        if(!isset($limit)){
            $limit = '100';
        }
        $query .= " LIMIT $limit";
        
        return $this->transactionDao->getAll($query,$params);
    }

    public function getTransaction($walletId, $id){
        $wallet = $this->getWallet($walletId);
        $transaction = $this->transactionDao->getOne("SELECT * FROM Transaction WHERE walletId = ? AND id = ?", $walletId, $id);
        return $transaction;
    }

    public function createTransaction($walletId, $transactionData){
        
        if(!isset($transactionData["type"])){
            throw new ExceptionResponse("Transaction type is not specified",'INVALID_DATA',400);
        }
        
        if(!isset($transactionData["category"])){
            throw new ExceptionResponse("Category is not specified",'INVALID_DATA',400);
        }
        
        if(!isset($transactionData["amount"])){
            throw new ExceptionResponse("Amount is not specified",'INVALID_DATA',400);
        }

        if(!array_key_exists($transactionData["type"],TransactionType::getConstList())){
            throw new ExceptionResponse("Invalid transaction type specified",'INVALID_DATA',400);
        }

        if($transactionData["amount"] <= 0){
            throw new ExceptionResponse("Amount should be greater than zero",'INVALID_DATA',400);
        }

        $wallet = $this->getWallet($walletId);
        $currentUser = AuthRequest::user();

        switch ($transactionData["type"]) {
            case TransactionType::INCOME:
                $wallet->balance += $transactionData["amount"];
                break;
            
            case TransactionType::EXPENSE:
                $wallet->balance -= $transactionData["amount"];
                break;
        }
        $wallet->modifiedBy = $currentUser;
        $wallet->modifiedTime = time();
        $this->walletDao->update($wallet);

        $transaction = new Transaction();
        $transaction->id = null;
        $transaction->walletId = $wallet->id;
        $transaction->type = $transactionData["type"];
        $transaction->category = strtoupper(str_replace(' ','_',$transactionData["category"]));
        
        if(isset($transactionData["description"])){
            $transaction->description = $transactionData["description"];
        }

        if(isset($transactionData["transactionTime"])){
            $transaction->transactionTime = $transactionData["transactionTime"];
        } else {
            $transaction->transactionTime = time();
        }
        
        $transaction->amount = $transactionData["amount"];
        $transaction->createdBy = $currentUser;
        $transaction->modifiedBy = $currentUser;
        $transaction->createdTime = time();
        $transaction->modifiedTime= time();

        $transaction = $this->transactionDao->create($transaction);
        return $transaction;
    }

    public function updateTransaction($wallerId, $id, $transactionData){
        $wallet = $this->getWallet($wallerId);
        
        $transaction = $this->getTransaction($wallerId, $id);
        if(!isset($transaction)){
            return DefaultResponse::invalidId();
        }

        $currentUser = AuthRequest::user();
        if($transaction->createdBy->id != $currentUser->id){
            DefaultResponse::updateForbidden("Transaction");
        }

        if(isset($transactionData["category"])){
            $transaction->category = strtoupper(str_replace(' ','_',$transactionData["category"]));
        }

        if(isset($transactionData["description"])){
            $transaction->description = $transactionData["description"];
        }

        if(isset($transactionData["transactionTime"])){
            $transaction->transactionTime = $transactionData["transactionTime"];
        }

        if(isset($transactionData["amount"])){
            if($transactionData["amount"] <= 0){
                throw new ExceptionResponse("Amount should be greater than zero",'INVALID_DATA',400);
            }

            $difference = $transactionData["amount"] - $transaction->amount;
            $transaction->amount = $transactionData["amount"];

            switch ($transaction->type) {
                case TransactionType::INCOME:
                    $wallet->balance += $difference;
                    break;
                case TransactionType::EXPENSE:
                    $wallet->balance -= $difference;
                    break;
            }
            $wallet->modifiedBy = $currentUser;
            $wallet->modifiedTime = time();
            $this->walletDao->update($wallet);   
        }


        $transaction->modifiedBy = $currentUser;
        $transaction->modifiedTime = time();
        $this->transactionDao->update($transaction);
        return DefaultResponse::updateSuccess();
    }

    public function deleteTransaction($walletId, $id){
        $wallet = $this->getWallet($walletId);
        
        $transaction = $this->getTransaction($walletId, $id);
        if(!isset($transaction)){
            return DefaultResponse::invalidId();
        }

        switch($transaction->type){
            case TransactionType::INCOME:
                $wallet->balance -= $transaction->amount;
                break;
            case TransactionType::EXPENSE:
                $wallet->balance += $transaction->amount;
                break;
        }
        $currentUser = AuthRequest::user();

        $wallet->modifiedBy = $currentUser;
        $wallet->modifiedTime = time();
        $this->walletDao->update($wallet);
        $this->transactionDao->delete($transaction);
        return DefaultResponse::deleteSuccess();
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
