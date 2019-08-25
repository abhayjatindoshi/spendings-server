<?php
namespace encryptorcode\services;

use encryptorcode\dao\Dao as Dao;
use encryptorcode\authentication\server\AuthRequest as AuthRequest;
use encryptorcode\entities\Wallet as Wallet;
use encryptorcode\exception\ExceptionResponse as ExceptionResponse;
use encryptorcode\response\DefaultResponse as DefaultResponse;

class WalletService{

    private $walletDao;
    private $portalId;
    private $portalUser;

    public function __construct($portalId){
        $this->walletDao = new Dao("encryptorcode\\entities\\Wallet");
        $this->portalId = $portalId;
    }

    public function getAllWallets(){
        $portalUser = $this->getPortalUser();
        $wallets = $this->walletDao->getAll("SELECT * FROM Wallet where portalId = ? AND ownedBy = ?",$portalUser->portalId, $portalUser->userId);
        return $wallets;
    }

    public function getWallet($id){
        $portalUser = $this->getPortalUser();
        $wallet = $this->walletDao->getOne("SELECT * FROM Wallet where portalId = ? AND ownedBy = ? AND id = ?",$portalUser->portalId, $portalUser->userId, $id);
        return $wallet;
    }

    public function createWallet($walletData){
        $currentUser = AuthRequest::user();
        $portalUser = $this->getPortalUser();

        $wallet = new Wallet();
        $wallet->id = null;
        $wallet->name = $walletData["name"];
        $wallet->balance = 0;
        $wallet->portalId = $portalUser->portalId;
        $wallet->ownedBy = $currentUser;
        $wallet->createdBy = $currentUser;
        $wallet->modifiedBy = $currentUser;
        $wallet->createdTime = time();
        $wallet->modifiedTime = time();

        $createdWallet = $this->walletDao->create($wallet);
        return $createdWallet;
    }

    public function updateWallet($id,$walletData){
        $wallet = $this->getWallet($id);
        if(!isset($wallet)){
            return DefaultResponse::invalidId();
        }

        $currentUser = AuthRequest::user();
        if($wallet->ownedBy->id != $currentUser->id){
            DefaultResponse::updateForbidden("Wallet");
        }

        $wallet->name = $walletData["name"];
        $wallet->modifiedBy = $currentUser;
        $wallet->modifiedTime = time();

        $this->walletDao->update($wallet);
        return DefaultResponse::updateSuccess();
    }

    public function deleteWallet($id){
        $wallet = $this->getWallet($id);
        if(!isset($wallet)){
            return DefaultResponse::invalidId();
        }

        $currentUser = AuthRequest::user();
        if($wallet->ownedBy->id != $currentUser->id){
            DefaultResponse::deleteForbidden("Wallet");
        }

        $this->walletDao->delete($wallet);
        return DefaultResponse::deleteSuccess();

    }

    private function getPortalUser(){
        if(!isset($this->portalUser)){
            $userId = AuthRequest::user()->id;
            $portalService = new PortalService();
            $portalUser = $portalService->getPortalUser($this->portalId,$userId);
            if(!isset($portalUser)){
                DefaultResponse::invalidPortal();
            }
            $this->portalUser = $portalUser;
        }
        return $this->portalUser;
    }
}