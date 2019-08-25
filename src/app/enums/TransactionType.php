<?php
namespace encryptorcode\enums;

use encryptorcode\dao\Enum;

class TransactionType extends Enum{
    const INCOME = "INCOME";
    const EXPENSE = "EXPENSE";
}