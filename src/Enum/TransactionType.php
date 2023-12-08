<?php

namespace Superern\Wallet\Enum;

enum TransactionType: string
{
    /**
     * DEPOSIT:
     * add balance in user's wallet
     */
    case DEPOSIT = 'deposit';

    /**
     * WITHDRAW:
     * request payout to user's deposit account
     */
    case WITHDRAW = 'withdraw';

    public static function toValues(): array
    {
        return ['deposit', 'withdraw'];
    }
}
